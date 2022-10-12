import mariadb
import xml.etree.ElementTree as ET
import sys
import os
import re
import logging

def reparse_quotes(cursor, limit, start=0):

    # get all posts within 'limit' starting with 'start' that match a particular pattern where a url to another post is embedded
    query = ("select post_id, post_text, ExtractValue(post_text, '//QUOTE[@url]/s') as ref_quotes from phpbb_posts where post_text rlike '\\\\[quote=\"In \\\\[url=https?:\\/\\/(www\\.|forum\\.)?mafiascum\\.net(\\/forum)?\\/viewtopic\\.php\\\\?p=(\\\\d+)#.*?](.*?)\\\\[\\/url\\](.*?)\"]' and post_id > %s order by post_id limit %s")

    logging.debug(query)
    cursor.execute(query, (start, limit))

    all_ref_post_ids = set()
    all_postdata = dict()
    ref_quotes_to_post_ids = dict()

    # for each post, parse out other post_ids mentioned and map quoted segments to reference post_ids
    for (post_id, post_text, ref_quotes) in cursor:
        ref_quotes_arr = re.split(r'(?<=\]) (?=\[)', ref_quotes)

        try:
            for ref_quote in ref_quotes_arr: 
                ref_post_id = re.match(r'\[quote=".+?\[url=https?:\/\/(www\.|forum\.)?mafiascum\.net(\/forum)?\/viewtopic\.php\?p=(\d+)#.*?\](.*?)\[\/url\](.*?)"]', ref_quote).group(3)
                all_ref_post_ids.add(ref_post_id)
                ref_quotes_to_post_ids[ref_quote] = ref_post_id
        except:
            logging.warning(f'Could not parse post content from quote: {ref_post_id}, {ref_quote}. Skipping.')
                
        all_postdata[post_id] = post_text

    if not all_postdata:
        logging.info("Found no posts. Finishing.")
        return None

    # obtain information about posts referenced by posts under modification
    cursor.executemany("INSERT INTO t_ref_posts (post_id) VALUES (%s)", [(x, ) for x in all_ref_post_ids])
    cursor.execute("select p.post_id, p.poster_id, p.post_time, u.username from phpbb_posts p join t_ref_posts r on p.post_id = r.post_id join phpbb_users u on p.poster_id = u.user_id")

    all_mapped_posts = dict()
    # build a map of post_id -> info about that post to replace into quotes
    for (post_id, poster_id, post_time, username) in cursor:
        all_mapped_posts[post_id] = (poster_id, post_time, username)

    to_update = list()

    # replace xml properties where needed
    for (post_id, post_text) in all_postdata.items():
        root = ET.fromstring(post_text)
        elems = root.findall('.//QUOTE[@url]')
        for quote_elem in elems:
            quote_s_elem = quote_elem.find('./s')
            ref_quote = quote_s_elem.text
            ref_post_id = ref_quotes_to_post_ids.get(ref_quote, None)
            if ref_post_id:
                try:
                    if int(ref_post_id) in all_mapped_posts:
                        (poster_id, post_time, username) = all_mapped_posts[int(ref_post_id)]
                        quote_elem.attrib.pop('url')
                        quote_elem.set('author', str(username))
                        quote_elem.set('post_id', str(ref_post_id))
                        quote_elem.set('time', str(post_time))
                        quote_elem.set('user_id', str(poster_id))
                        quote_s_elem.text = f'[quote={username} post_id={ref_post_id} time={post_time} user_id={poster_id}]'
                    else:
                        logging.info(f'post {ref_post_id} was deleted, so we cannot link to it.')
                except:
                    logging.error(f'Something went wrong: {post_id}, {post_text}, {ref_post_id}')
                    raise
        # hack to deal with the xml parser changing all brs to have a space before self close, which bbcode will totally ignore!
        to_update.append((post_id, re.sub(r'<br />', '<br/>', ET.tostring(root, 'unicode'))))

    logging.debug(to_update)

    # upsert changes
    cursor.executemany("INSERT INTO phpbb_posts (post_id, post_text) VALUES (%s,%s) ON DUPLICATE KEY UPDATE post_text = %s",
        [(post_id, post_text, post_text) for (post_id, post_text) in to_update])

    # find the max row we dealt with
    highest_post_id = max([x for x in all_postdata.keys()])
    logging.info(f"Processed up to {highest_post_id}")
    return highest_post_id

def main():
    cnx = mariadb.connect(
        user=os.getenv('PHPBB_DATABASE_USER'), 
        password=os.getenv('PHPBB_DATABASE_PASSWORD'), 
        host=os.getenv('PHPBB_DATABASE_HOST'), 
        database=os.getenv('PHPBB_DATABASE_NAME'), 
        autocommit=True
    )
    logging.basicConfig(stream=sys.stdout, level=os.getenv('LOG_LEVEL', logging.INFO), format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    logging.info('Connection established. Starting at 0.')
    cursor = cnx.cursor()
    cursor.execute("CREATE TEMPORARY TABLE IF NOT EXISTS t_ref_posts (post_id integer primary key)")

    highest_post_id = reparse_quotes(cursor, 1000)
    while (highest_post_id):
        cursor.execute("TRUNCATE TABLE t_ref_posts")
        highest_post_id = reparse_quotes(cursor, 1000, highest_post_id)

if __name__ == '__main__':
    main()


