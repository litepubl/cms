Система управления сайтом"LitePublisher" это простой и легкий движок блога. Все подробности на официальном сайте проекта:
http://litepublisher.ru/

Минимальные требование к системе: php версии не ниже 5.4 Опционально модуль апача rewrite, mysql или pdo драйвер.

Установка. Если у вас есть шелл доступ к серверу, то рекомендую устанавливать из шел следующим образом:

wget https://github.com/litepubl/cms/archive/vx.xx.tar.gz
tar -xf vx.xx.tar.gz

где вместо x.xx поставить номер актуальной версии. Ссылку на актуальную версию вы можете скопировать со страницы 
https://github.com/litepubl/cms/releases/latest

Иначе  вам следует скопировать файлы и папки движка в корень домена, установить права для записи (0777) на папки files, storage, storage/backup, storage/cache, storage/data. Во всех этих папках установить права 0666 на файлы index.htm (в них производится тестовая запись). 

После копирования файлов на сервер следует открыть адрес домена в браузере и заполнить форму создания. Если конфигурация устраивает, то будет запущен инсталятор litepublisher, где вы заполните простую форму с названием и E-Mail администратора. В самом начале стоит форма выбора языка. Есть дава способа задать параметры доступа к базе MySQL: в файле index.php в корне домена или в форме инсталятора. Что вам удобнее - решать вам. Если доступ к базе будет прописан в index.php, то инсталятор не будет запрашивать эти данные.

 Если по каким то причинам инсталятор не смог определить язык установки, то смените язык, нажав кнопку "Change language".

Установка в подпапку домена ничем не отличается, за исключением того, что необходимо исправить файл .htaccess изменив строку 
RewriteRule . /index.php [L]

на 
RewriteRule . /subdir/index.php [L]

где subdir имя папки, куда устанавливается блог.

Если инсталятор открылся в неверной кодировке, то возможно проблему решит редактирование файла .htaccess - в первой строке удалите символ#, получится первая строка:
CharsetDisable On

Для повышения безопасности отредактируйте строку, изменив на свое значение:
  public static $secret = '8r7j7hbt8iik//pt7hUy5/e/7FQvVBoh7/Zt8sCg8+ibVBUt7rQ';

Для управления сайтом зайдите в простую панель управления по специальному адресу:
http://yourdomain/admin/

Если вы потеряли пароль, то вы можете восстановить пароль, перейдя по ссылке
http://yourdomain/admin/passwordrecover/

где надо ввести ваш E-Mail администратора, на который будет выслан новый пароль к блогу, то есть старый пароль будет заменен новым случайно сгенерированным.

Для тех, кто хочет сделать редирект с адресами www можно добавить в .htaccess следующие строки:
RewriteCond %{HTTP_HOST} ^www.site.ru$ [NC]
RewriteRule ^(.*)$ http://site.ru/$1 [R=301,L]

Использованные продукты и их лицензии в CMS Litepublisher

Bootstrap. Code copyright 2011-2015 Twitter, Inc. Code released under the MIT license (https://github.com/twbs/bootstrap/blob/master/LICENSE).

Bootswatch. Copyright 2014 Thomas Park. Code released under the MIT License.

jQuery. Copyright 2015 jQuery Foundation and other contributors. Code released under the MIT License.

jQuery UI. Copyright jQuery Foundation and other contributors, https://jquery.org/  Code released under the MIT License.

Font Awesome by @davegandy - http://fontawesome.io - @fontawesome License - http://fontawesome.io/license (Font: SIL OFL 1.1, CSS: MIT License)

Lobster font by Pablo Impallari released under the SIL Open Font License  (http://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL)

NautilusPompilius font by PUNK YOU BRANDS Nikita Kanarev released under the SIL Open Font License  (http://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=OFL)

MediaElement.js Copyright 2010-2015, John Dyer (http://j.hn) License: MIT

SWFUpload is (c) 2006-2007 Lars Huring, Olov Nilzйn and Mammon Media and is released under the MIT License

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
