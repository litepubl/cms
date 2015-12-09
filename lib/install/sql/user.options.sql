  id int unsigned NOT NULL default '0',
  subscribe enum('enabled','disabled') default 'enabled',
  authorpost_subscribe enum('enabled','disabled') default 'enabled',

  KEY id (id)