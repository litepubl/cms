 id int unsigned NOT NULL default 0,
    service enum('$names') default 'facebook',
    uid varchar(22) NOT NULL default '',
    
primary key (id),
key (service)