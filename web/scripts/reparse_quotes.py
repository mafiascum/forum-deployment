from cgitb import text
import mariadb
import xml.etree.ElementTree as ET
import sys
import os
import re
import logging
from functools import partial

def extract_post_number_from_link_text(link_text):
    match=re.match(r'post #?(\d+)', link_text)
    if(match is None):
        return None
    return int(match.group(1))

def build_quote_tag_text(username, ref_post_id, post_time, poster_id, post_num):
    quote_elem_text = f'[quote={username} post_id={ref_post_id} time={post_time} user_id={poster_id}'
    if(post_num is not None):
        quote_elem_text += f' post_num={post_num}'
    quote_elem_text += ']'
    return quote_elem_text

def reparse_quotes(table, id_column, text_column, necessary_insert_cols, cursor, limit, start=0):

    # get all posts within 'limit' starting with 'start' that match a particular pattern where a url to another post is embedded
    query = f"select {id_column}, {text_column}, ExtractValue({text_column}, '//QUOTE[@url]/s') as ref_quotes from {table} where {text_column} rlike '\\\\[quote=\"In \\\\[url=https?:\\/\\/(www\\.|forum\\.)?mafiascum\\.net(\\/forum)?\\/viewtopic\\.php\\\\?p=(\\\\d+)#.*?](.*?)\\\\[\\/url\\](.*?)\"]' and {id_column} > %s order by {id_column} limit %s"

    logging.debug(f"select query: {query}")
    cursor.execute(query, (start, limit))

    all_ref_post_ids = set()
    all_postdata = dict()
    ref_quotes_to_post_details = dict()

    # for each post, parse out other post_ids mentioned and map quoted segments to reference post_ids
    for (post_id, post_text, ref_quotes) in cursor:
        ref_quotes_arr = re.split(r'(?<=\]) (?=\[)', ref_quotes)

        try:
            for ref_quote in ref_quotes_arr:
                pattern_match = re.match(r'\[quote=".+?\[url=https?:\/\/(www\.|forum\.)?mafiascum\.net(\/forum)?\/viewtopic\.php\?p=(\d+)#.*?\](.*?)\[\/url\](.*?)"]', ref_quote)
                ref_post_id = pattern_match.group(3)
                link_text = pattern_match.group(4)
                post_num = extract_post_number_from_link_text(link_text)
                all_ref_post_ids.add(ref_post_id)
                ref_quotes_to_post_details[ref_quote] = {'post_id': ref_post_id, 'post_num': post_num}
        except:
            logging.warning(f'Could not parse post content from quote: {table}, {post_id}, {ref_quote}. Skipping.')
        all_postdata[post_id] = post_text

    if not all_postdata:
        logging.info(f"Found no posts for {table}. Finishing.")
        return None

    # obtain information about posts referenced by posts/PMs under modification
    cursor.executemany("INSERT INTO t_ref_posts (post_id) VALUES (%s)", [(x, ) for x in all_ref_post_ids])
    cursor.execute(f"select p.post_id, p.poster_id, p.post_time, u.username from phpbb_posts p join t_ref_posts r on p.post_id = r.post_id join phpbb_users u on p.poster_id = u.user_id")

    all_mapped_posts = dict()
    # build a map of post_id -> info about that post to replace into quotes
    for (post_id, poster_id, post_time, username) in cursor:
        all_mapped_posts[post_id] = (poster_id, post_time, post_num, username)

    to_update = list()

    # replace xml properties where needed
    for (post_id, post_text) in all_postdata.items():
        root = ET.fromstring(post_text)
        elems = root.findall('.//QUOTE[@url]')
        for quote_elem in elems:
            quote_s_elem = quote_elem.find('./s')
            ref_quote = quote_s_elem.text
            ref_post_details = ref_quotes_to_post_details.get(ref_quote, None)
            if ref_post_details:
                ref_post_id = ref_post_details['post_id']
                ref_post_num = ref_post_details['post_num']
                try:
                    if int(ref_post_id) in all_mapped_posts:
                        (poster_id, post_time, post_num, username) = all_mapped_posts[int(ref_post_id)]
                        quote_elem.attrib.pop('url')
                        quote_elem.set('author', str(username))
                        quote_elem.set('post_id', str(ref_post_id))
                        quote_elem.set('time', str(post_time))
                        quote_elem.set('user_id', str(poster_id))
                        if(ref_post_num is not None):
                            quote_elem.set('post_num', str(ref_post_num))
                        quote_s_elem.text = build_quote_tag_text(username, ref_post_id, post_time, poster_id, ref_post_num)
                    else:
                        logging.info(f'post {ref_post_id} was deleted, so we cannot link to it.')
                except:
                    logging.error(f'Something went wrong: {table}, {post_id}, {post_text}, {ref_post_id}')
                    raise
        # hack to deal with the xml parser changing all brs to have a space before self close, which bbcode will totally ignore!
        to_update.append((post_id, re.sub(r'<br />', '<br/>', ET.tostring(root, 'unicode'))))

    logging.debug(f"replaced XML: {to_update}")

    # insert is being used as a vehicle for doing bulk updates; we never actually insert
    # but data integrity constraints will still fire if it thinks you're trying to insert a null val into a not null column
    extra_coldefs = ''.join([f", {x}" for x in necessary_insert_cols])
    extra_vals = ''.join([", 'blah'" for _x in necessary_insert_cols])

    # upsert changes
    try:
        query = f"INSERT INTO {table} ({id_column}, {text_column}{extra_coldefs}) VALUES (%s,%s{extra_vals}) ON DUPLICATE KEY UPDATE {text_column} = %s"
        logging.debug(f"update query: {query}")
        cursor.executemany(query, [(post_id, post_text, post_text) for (post_id, post_text) in to_update])
    except:
        logging.error(f'Problem doing write replacement: {table}, {post_id}, {post_text}')
        raise

    # find the max row we dealt with
    highest_post_id = max([x for x in all_postdata.keys()])
    logging.info(f"Processed up to {highest_post_id} for {table}")
    return highest_post_id

def reparse_entity(table, id_column, text_column, necessary_insert_cols, cursor):
    logging.info(f'Starting {table} at 0.')
    cursor.execute("TRUNCATE TABLE t_ref_posts")
    reparse = partial(reparse_quotes, table, id_column, text_column, necessary_insert_cols)
    highest_post_id = reparse(cursor, 1000)
    while (highest_post_id):
        cursor.execute("TRUNCATE TABLE t_ref_posts")
        highest_post_id = reparse(cursor, 1000, highest_post_id)


def main():
    cnx = mariadb.connect(
        user=os.getenv('PHPBB_DATABASE_USER'), 
        password=os.getenv('PHPBB_DATABASE_PASSWORD'), 
        host=os.getenv('PHPBB_DATABASE_HOST'), 
        database=os.getenv('PHPBB_DATABASE_NAME'), 
        autocommit=True
    )
    logging.basicConfig(stream=sys.stdout, level=os.getenv('LOG_LEVEL', logging.INFO), format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
    logging.info('Connection established.')
    cursor = cnx.cursor()
    cursor.execute("CREATE TEMPORARY TABLE IF NOT EXISTS t_ref_posts (post_id integer primary key)")

    reparse_entity("phpbb_privmsgs", "msg_id", "message_text", ["to_address", "bcc_address"], cursor)
    reparse_entity("phpbb_posts", "post_id", "post_text", [], cursor)


if __name__ == '__main__':
    main()
