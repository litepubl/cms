  id int unsigned NOT NULL auto_increment,
  status enum('approved','hold','comuser') default 'approved',
  email varchar(64) NOT NULL,
  password char(22) NOT NULL,
  cookie char(22) NOT NULL,
  expired datetime NOT NULL default '2010-01-01 10:01:01',
  trust int unsigned NOT NULL default '0',
  idgroups text NOT NULL,
  name text not null,
  website varchar(255) NOT NULL,

  PRIMARY KEY  (id),
  KEY email (email)