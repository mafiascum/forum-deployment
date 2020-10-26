DELETE FROM phpbb_config WHERE config_name IN("pmwelcome_user", "pmwelcome_subject", "pmwelcome_version");

INSERT INTO phpbb_config VALUES
("pmwelcome_user", "5932", 0),
("pmwelcome_subject", "Welcome to MafiaScum!", 0),
("pmwelcome_version", "1.0.1", 0);

DELETE FROM phpbb_config_text WHERE config_name IN("pmwelcome_post_text");

INSERT INTO phpbb_config_text VALUES
("pmwelcome_post_text", 'Welcome to MafiaScum, {USERNAME}! We''re glad to have you!\n\nTo sign up for your first newbie game, visit the [b][url=https://forum.mafiascum.net/viewforum.php?f=136]Newbie Queue[/url][/b] and post [b]/in[/b]. You will be added to the queue &amp; receive a private message when your game begins.\n\nIf you''re a more experienced player, you can find more games in the [b][url=https://forum.mafiascum.net/viewforum.php?f=4]Queue[/url][/b] forum.\n\nGood luck &amp; we hope you have fun!');
