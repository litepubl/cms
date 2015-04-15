wget http://litepublisher.googlecode.com/files/litepublisher.zip
unzip litepublisher.zip
chmod 0777 backup
chmod 0777 cache
chmod 0777 data
chmod 0777 files
chmod 0777 lib
chmod 0777 plugins
chmod 0777 themes

chmod 0666 lib/*.php
chmod 0777 lib/include
chmod 0777 lib/install
chmod 0777 lib/languages

chmod 0666 lib/.htaccess
chmod 0666 lib/index.htm
chmod 0666 lib/include/*
chmod 0666 lib/install/*
chmod 0666 lib/languages/*

chmod -R -f 0777 plugins/*