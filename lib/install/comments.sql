  id int(10) unsigned NOT NULL auto_increment,
  post int(10) unsigned NOT NULL default '0',
  author int(10) unsigned NOT NULL default '0',
  parent int(10) unsigned NOT NULL default '0',
  posted datetime NOT NULL default '2010-01-01 10:01:01',
  status enum('approved','hold','spam','deleted') default 'approved',
  content text NOT NULL,

  PRIMARY KEY  (id),
  KEY post (post),
  KEY status (status),
  KEY author (author),
  KEY posted (posted)