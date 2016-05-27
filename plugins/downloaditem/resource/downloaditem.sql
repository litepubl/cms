  `id` int unsigned NOT NULL default '0',
  `type` enum('theme','plugin') default 'theme',
  `downloads` int unsigned NOT NULL default '0',
  `downloadurl` varchar(255) NOT NULL,
  `authorurl` varchar(255) NOT NULL,
  `authorname` text NOT NULL,
  `version` varchar(5) NOT NULL,

  PRIMARY KEY  (`id`,`type`),
  KEY `downloads` (`downloads`)
