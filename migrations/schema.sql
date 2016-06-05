CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `bookmarks_url` varchar(255) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `micropub_endpoint` varchar(255) DEFAULT NULL,
  `media_endpoint` varchar(255) DEFAULT NULL,
  `access_token` varchar(1024) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bookmarks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(30) DEFAULT NULL,
  `bookmark_of` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `media_url` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
