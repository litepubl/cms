  id int unsigned NOT NULL auto_increment,
  parent int unsigned NOT NULL default '0',
  idurl int unsigned NOT NULL default '0',
  customorder int unsigned NOT NULL default '0',
  itemscount int unsigned NOT NULL default '0',
  icon int unsigned NOT NULL default '0',
  idschema int unsigned NOT NULL default '1',
  idperm int unsigned NOT NULL default '0',
  includeparents boolean default false,
  includechilds boolean default false,
  title text NOT NULL,

  PRIMARY KEY  (id),
  KEY parent (parent)