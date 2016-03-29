<?php
function update609() {
if (litepublisher::$classes->exists('catbread')) {
$catbread = catbread::i();
unset($catbread->data['tml']);
$catbread->save();

tlocalmerger::i()->addplugin('catebrad');
tthemeparser::i()->addtags('plugins/catbread/resource/theme.txt', 'plugins/catbread/resource/theme.ini');
}

if (version_compare(PHP_VERSION, '5.4', '<')) {
$site = litepublisher::$site;
tmailer::sendtoadmin("Обновление сайта $site.url", " Здравствуйте!
Это письмо отправлено скриптом обновления вашего сайта $site.url/ Пожалуйста, переведите работу вашего сайта на PHP версии 5.4 как минимум. Желательно перейти на PHP 7 версии. Долгие годы мы поддерживали работу движка на старых версиях PHP. С каждым днем это становится все более сложным. Отрасль растет и развивается и мы должны не отставать. Поддержка старых версий PHP не позволяет развивать движок сайта.

Спасибо за понимание, разработчик Владимир Юшко
http://litepublisher.ru/ает
");
}
}