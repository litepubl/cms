  id int(10) unsigned NOT NULL,
  created datetime NOT NULL default '2010-01-01 10:01:01',
  modified datetime NOT NULL default '2010-01-01 10:01:01',
  ip varchar(15) NOT NULL default '',
  rawcontent longtext NOT NULL,
  hash char(22) NOT NULL,

  PRIMARY KEY  (id),
  KEY hash (hash)