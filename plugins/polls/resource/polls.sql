id int unsigned NOT NULL auto_increment,
  idobject int UNSIGNED NOT NULL default 0,
typeobject enum('post', 'image', 'comment') default 'post',
  votes int UNSIGNED NOT NULL default 0,
  rate decimal(3,1) not null default '0.0',
  maxvote tinyint unsigned NOT NULL default '1',
  created datetime NOT NULL default '00-00-00 00:00:00',
  status enum('opened','closed') default 'opened',
template enum('stars', 'like') default 'stars',

  PRIMARY KEY  ( id),
key (idobject, typeobject),
  KEY votes (votes),
  KEY rate (rate)