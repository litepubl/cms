  id int(10) unsigned NOT NULL auto_increment,
  type enum('normal','get','usernormal', 'userget', 'begin', 'end', 'regexp') default 'normal',
  url varchar(255) NOT NULL,
  class varchar(64) NOT NULL,
  arg varchar(32) NOT NULL,

  PRIMARY KEY  (id),
  KEY url (url)
