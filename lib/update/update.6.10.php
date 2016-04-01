<?php
function update610() {
$js = tjsmerger::i();
$js->lock();
$js->deletefile('default', '/js/litepubl/bootstrap/popover.post.min.js');
$js->unlock();

$cl = litepublisher::$classes;
unset($cl->items['tfilestorage']);
unset($cl->items['tstorage']);
$cl->items['litepubl\litepubl'] = array('kernel.php', '', 'litepubl.php');
$cl->items['litepubl\paths'] = array('kernel.php', '', 'paths.php');
$cl->items['litepubl\storage'] = array('kernel.php', '', 'storage.php');
$cl->items['litepubl\incstorage'] = array('kernel.php', '', 'storage.inc.php');
$cl->items['litepubl\jsonstorage'] = array('storage.json.php', '', 'storage.json.php');
$cl->items['litepubl\memcachestorage'] = array('kernel.php', '', 'storage.memcache.php');
$cl->items['litepubl\datastorage'] = array('kernel.php', '', 'storage.class.php');
$cl->save();

if (version_compare(PHP_VERSION, '5.4', '<')) {
$site = litepublisher::$site;
tmailer::sendtoadmin("Обновление сайта $site.url", " Здравствуйте!
Это письмо отправлено скриптом обновления вашего сайта $site.url/ Пожалуйста, переведите работу вашего сайта на PHP версии 5.4 как минимум. Желательно перейти на PHP 7 версии. Долгие годы мы поддерживали работу движка на старых версиях PHP. С каждым днем это становится все более сложным. Отрасль растет и развивается и мы должны не отставать. Поддержка старых версий PHP не позволяет развивать движок сайта.

Спасибо за понимание, разработчик Владимир Юшко
http://litepublisher.ru/ает
");
}
}