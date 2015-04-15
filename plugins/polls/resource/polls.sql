id int unsigned NOT NULL auto_increment,
  id_tml int UNSIGNED NOT NULL default 0,
  total int UNSIGNED NOT NULL default 0,
  rate tinyint unsigned NOT NULL default '0',
  status enum('opened','closed') default 'opened',

  PRIMARY KEY  ( id),
  KEY total(total),
  KEY rate (rate)