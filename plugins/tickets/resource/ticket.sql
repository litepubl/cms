  `id` int unsigned NOT NULL default '0',
  `state` enum('fixed','opened','wontfix','invalid','duplicate','reassign') default 'opened',
  `prio` enum('trivial','minor','major','critical','blocker') default 'major',
  `assignto` int unsigned NOT NULL default '0',
  `closed` datetime NOT NULL default '2010-01-01 10:01:01',
  `version` varchar(5) NOT NULL,
  `poll` int unsigned NOT NULL default '0',
  `os` varchar(32) NOT NULL,
  `reproduced` tinyint(1) default '0',
  `code` longtext NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `state` (`state`)
