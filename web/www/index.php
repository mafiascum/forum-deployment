<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Welcome to MafiaScum</title>
		<meta name="DESCRIPTION" content="Play Mafia/Werewolf Online">
		<meta property="og:title" content="Welcome to MafiaScum"/>
		<meta property="og:image" content="./title.png"/>
		<meta property="og:description" content="Play Mafia/Werewolf Online"/>
		<meta charset='UTF-8'>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<link type="image/x-icon" href="/favicon.ico" rel="icon">
		<link type="image/x-icon" href="/favicon.ico" rel="shortcut icon">
		<link rel='stylesheet' href='css.css'>
		<link rel="stylesheet" href="fontawesome/font-awesome.min.css">
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-31225517-1']);
		  _gaq.push(['_setDomainName', 'mafiascum.net']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
	</head>
	<body>
		<div id='bg'>
			<a id='title' href='<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('FORUM_FQDN')) ?>'><img src="title.png" alt="MafiaScum Noose Logo"/></a>
			<div id='content'>
				<p>
					<span class='welcome'>Welcome!</span> You are at the largest website dedicated to the game of Mafia (also known as Werewolf). Join our forum for online play or browse through our wiki to see our many roles and setups, read about strategy and theory, and find plenty of other helpful info about Mafia!
				</p>
				<div id='nav_container'>
					<a class='nav' href='<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('FORUM_FQDN')) ?>' alt='Play here' title='Play here!'>Forum</a>&bull;
					<a class='nav' href='<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('WIKI_FQDN')) ?>' alt='Learn here' title='Learn here!'>Wiki</a>&bull;
					<a class='nav' href='<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('WIKI_FQDN')) ?>/index.php?title=Newbie_Guide' alt='Newbie Guide' title='Newbie Guide'>Info for new players</a>&bull;<br/>
					<a class='nav' href='<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('WWW_FQDN')) ?>/howtojoin.svg' target='_blank' alt='Join your first game' title='Join your first game' target='_blank'>How to join your first game</a>
				<div>
				<div class='social-icons'>
					<a href='https://www.facebook.com/mafiascum.net/' target='_blank' alt='Official Facebook Page'><i class="fa fa-facebook-square"></i></a>
					<a href='https://twitter.com/MafiaScum' target='_blank' alt='Official Twitter Page'><i class="fa fa-twitter-square"></i></a>
				</div>
			</div>
		</div>
	</body>
</html>
