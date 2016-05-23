  `id` int(10) unsigned NOT NULL auto_increment,
  `url` varchar(255) NOT NULL,
  `foafurl` varchar(255) NOT NULL,
  `nick` text NOT NULL,
  `added` datetime NOT NULL default '2010-01-01 10:01:01',
  `errors` int(10) unsigned NOT NULL default '0',
  `status` enum('approved','hold','invated','rejected','spam','error') default 'hold',

  PRIMARY KEY  (`id`),
  KEY `url` (`url`),
  KEY `status` (`status`)