LitePublisher is content management system. LitePublisher is a simple and lightweight engine. Official website:
http://litepublisher.com/ 

Minimum System Requirements: 
- PHP 5.4
- MySQL or pdo driver

 Optional:
- apache server with rewrite module

Installation. If you have a shell access to the server you can download file:
wget https://github.com/litepubl/cms/archive/vx.xx.tar.gz
tar -xf cms.x.xx.tar.gz

where x.xx is current version. You can find latest release on
https://github.com/litepubl/cms/releases/latest

Otherwise you must upload files into root folder of website. set write permissions (0777) on following folder: 
- files
- storage
- storage / backup
- storage / cache
- storage / data

In these folders you should set permissions 0666 on index.htm  files
After that  open your website in browser. Installer will ask you some questions such as E-Mail, site name and database account.  You can set database account in index.php from root folder or in installation form.

Sometime installer can not determine language. You can change language pressing by "change language button".
Thats all. Click install to begin instalation.

To install into subfolder you should edit .htaccess file. Replace one line:
RewriteRule. / Index.php [L] 

to:
RewriteRule. / Subdir / index.php [L] 

where subdir is your subfolder.

If the installer opens in wrong encoding you can solve problem by editing . Htaccess file. Remove first char # from .htaccess
CharsetDisable On 

To increase security you can edit index.php  file. Change value in line:
  public static $ secret = '8 r7j7hbt8iik / / pt7hUy5/e/7FQvVBoh7/Zt8sCg8 + ibVBUt7rQ '; 

To manage the site go to a simple admin panel:
http://example.com/admin/ 

If you lost password you can restore your password by clicking on the link 
http://example.com/admin/passwordrecover/ 

Included products and licenses


Bootstrap. Code copyright 2011-2015 Twitter, Inc. Code released under the MIT license (https://github.com/twbs/bootstrap/blob/master/LICENSE).

Bootswatch. Copyright 2014 Thomas Park. Code released under the MIT License.

jQuery. Copyright 2015 jQuery Foundation and other contributors. Code released under the MIT License.

jQuery UI. Copyright jQuery Foundation and other contributors, https://jquery.org/  Code released under the MIT License.

Font Awesome by @davegandy - http://fontawesome.io - @fontawesome License - http://fontawesome.io/license (Font: SIL OFL 1.1, CSS: MIT License)

Lobster font by Pablo Impallari released under the SIL Open Font License  (http://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL)

NautilusPompilius font by PUNK YOU BRANDS Nikita Kanarev released under the SIL Open Font License  (http://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL)

MediaElement.js Copyright 2010-2015, John Dyer (http://j.hn) License: MIT

SWFUpload is (c) 2006-2007 Lars Huring, Olov Nilzén and Mammon Media and is released under the MIT License

PhotoSwipe Copyright (c) 2015 Dmitry Semenov (http://photoswipe.com) licensed under MIT license

prettyPhoto Copyright by Stephane Caron (http://www.no-margin-for-errors.com) http://creativecommons.org/licenses/by/2.5/

FileReader.js Copyright 2012 Brian Grinstead - MIT License.

Font Face Observer licensed under the BSD License. Copyright 2014-2015 Bram Stein. All rights reserved.

jQuery Cookie Plugin. Copyright 2006, 2014 Klaus Hartl Released under the MIT license

jQuery JSON Plugin. Brantley Harris wrote this plugin. It is based somewhat on the JSON.org (http://www.json.org/json2.js) MIT License: http://www.opensource.org/licenses/mit-license.php

Respond.js Copyright 2014 Scott Jehl Licensed under MIT

Modernizr Copyright © 2009—2015. Modernizr is available under the MIT license.

HTML5 Shiv (http://paulirish.com/2011/the-history-of-the-html5-shiv/) Licensed under MIT and (or) GPL-2.0

tar Class     Copyright (C) 2002  Josh Barger under the terms of the GNU Lesser General Public License

getID3by James Heinrich under 3 license for choise: GNU GPL, GNU LGPL and Mozilla MPL

Punycode Library Copyright (c) 2011 Takehito Gondo MIT License

PemFTP - A Ftp implementation in pure PHP copyright Alexey Dotsenko LGPL License 

PHP SMTP class Author: Chris Ryan License: LGPL

CKEditor Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved. GNU Lesser General Public License Version 2.1 or later (the "LGPL")
