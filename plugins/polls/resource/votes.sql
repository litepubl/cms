id int UNSIGNED NOT NULL default 0,
  item tinyint unsigned NOT NULL default '0',
  votes int UNSIGNED NOT NULL default 0,

  PRIMARY KEY(id, item)