  `id` int(10) unsigned NOT NULL auto_increment,
  `post` int(10) unsigned NOT NULL default '0',
  `posted` datetime NOT NULL default '2010-01-01 10:01:01',
  `status` enum('approved','hold','spam','deleted') default 'hold',
  `url` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `ip` varchar(15) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `post` (`post`),
  KEY `posted` (`posted`),
  KEY `status` (`status`)