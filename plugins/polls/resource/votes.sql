idpoll int UNSIGNED NOT NULL default 0,
  iduser int UNSIGNED NOT NULL default 0,
  vote tinyint unsigned NOT NULL default '1',

  PRIMARY KEY(idpoll, iduser)