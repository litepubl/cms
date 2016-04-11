  `id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL default '00-00-00 00:00:00',
  `modified` datetime NOT NULL default '00-00-00 00:00:00',
  `rawcontent` longtext NOT NULL,
  `hash` varchar(22) default NULL,

  PRIMARY KEY  (`id`)