  id int(10) unsigned NOT NULL auto_increment,
  url varchar(255) NOT NULL,
  class varchar(64) NOT NULL,
  arg varchar(32) NOT NULL,
  type enum('normal','get','tree', 'usernormal', 'userget') default 'normal',

  PRIMARY KEY  (id),
  KEY url (url)
