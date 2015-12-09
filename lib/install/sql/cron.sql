  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('single','hour','day','week') default 'single',
  `date` datetime NOT NULL default '2010-01-01 10:01:01',
  `class` varchar(64) NOT NULL,
  `func` varchar(64) NOT NULL,
  `arg` varchar(255) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `date` (`date`)