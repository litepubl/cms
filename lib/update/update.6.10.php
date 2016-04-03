<?php
function update610() {
$js = tjsmerger::i();
$js->lock();
$js->deletefile('default', '/js/litepubl/bootstrap/popover.post.min.js');
$js->unlock();

$cl = litepublisher::$classes;
unset($cl->items['tfilestorage']);
unset($cl->items['tstorage']);
$cl->items['litepubl'] = array('kernel.php', '', 'litepubl.php');
$cl->items['paths'] = array('kernel.php', '', 'paths.php');
$cl->items['storage'] = array('kernel.php', '', 'storage.php');
$cl->items['incstorage'] = array('kernel.php', '', 'storage.inc.php');
$cl->items['jsonstorage'] = array('storage.json.php', '', 'storage.json.php');
$cl->items['memcachestorage'] = array('kernel.php', '', 'storage.memcache.php');
$cl->items['datastorage'] = array('kernel.php', '', 'storage.class.php');
$cl->save();

$site = litepublisher::$site;

$body = " Здравствуйте!
Это письмо отправлено скриптом обновления вашего сайта $site->url/ Пожалуйста, обновите файл index.php в корне вашего сайта. Новую версию файла можно скачать по ссылке:
https://raw.githubusercontent.com/litepubl/cms/master/index.php

После этого работа вашего сайта возобновиться
Спасибо за понимание, разработчик Владимир Юшко
http://litepublisher.ru/
";

tmailer::sendtoadmin("Обновление сайта $site->url", $body);

echo tcontentfilter::i()->filter($body);
}