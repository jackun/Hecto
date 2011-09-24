CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL,
  `user` varchar(32) NOT NULL,
  `watch` varchar(32) NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `plays` int(11) NOT NULL default 0,
  `erroneous` int(11) NOT NULL default 0,
  `bkey` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `watch` (`watch`),
  FULLTEXT KEY `title` (`title`)
);

CREATE TABLE IF NOT EXISTS `videos_playlist` (
  `playlist` varchar(32) NOT NULL,
  `video_id` int(11) NOT NULL,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  KEY `playlist_video` (`video_id`)
);
