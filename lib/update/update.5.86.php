<?php
function _encrypt($s, $key) {
$maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
if (strlen($key) > $maxkey) $key = substr($key, $maxkey);

    $block = mcrypt_get_block_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
    $pad = $block - (strlen($s) % $block);
    $s .= str_repeat(chr($pad), $pad);
    return mcrypt_encrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
  }

function update586() {
$menus = tadminmenus::i();
$id = $menus->url2id('/admin/logout/');
if (!$id) $id = $menus->addfake('/admin/logout/', tlocal::i()->logout);  
$menus->items[$id]['order'] = 9999999;
$menus->save();

tjsonserver::i()->addevent('comments_get_logged', 'tjsoncomments', 'comments_get_logged');

$man = tdbmanager::i();
    $prefix = strtolower(litepublisher::$options->dbconfig['prefix']);
    $tables = $man->gettables();
    foreach ($tables as $table) {
      if (strbegin(strtolower($table), $prefix)) {
$man->query("alter table $table ENGINE = MYISAM");
}
}

if (isset(litepublisher::$options->solt)) return;

  litepublisher::$options->solt = md5uniq();
litepublisher::$options->emptyhash = basemd5(litepublisher::$secret . litepublisher::$options->solt);
litepublisher::$options->securecookie = false;
litepublisher::$options->authenabled = true;

if (function_exists('mcrypt_encrypt')) {
litepublisher::$options->data['dbconfig']['password'] = _encrypt(str_rot13(base64_decode(litepublisher::$options->data['dbconfig']['password'])),
 litepublisher::$options->solt . litepublisher::$secret);
}
  
    $expired = time() + 31536000;
    $cookie = md5uniq();
    //litepublisher::$options->setcookies($cookie, $expired);
    $subdir = litepublisher::$site->subdir . '/';
    setcookie('litepubl_user_id', litepublisher::$options->user, $expired,  $subdir, false);
    setcookie('litepubl_user', $cookie, $expired, $subdir , false);
    setcookie('litepubl_user_flag', 'true', $expired, $subdir, false);
    
$cookie = basemd5((string) $cookie . litepublisher::$options->solt .litepublisher::$secret);
    litepublisher::$options->data['cookiehash'] = $cookie;
      litepublisher::$options->cookieexpired = $expired;
unset(litepublisher::$options->data['cookie'], litepublisher::$options->data['authcookie']);

  $password = md5uniq();
    litepublisher::$options->data['password'] = basemd5($password . litepublisher::$options->solt . litepublisher::$secret);

unset(litepublisher::$classes->items['tauthdigest']);
litepublisher::$classes->items['tableprop'] = array('kernel.admin.php', '', 'htmlresource.class.php');
litepublisher::$classes->save();

tusers::i()->db->update("password = ''", 'id > 0');
$theme = ttheme::i();
$args = new targs();
$args->password = $password;
$subj = $theme->parsearg('[$site.name] Смена пароля', $args);
$body = $theme->parsearg('Внимание! Обновление LitePublisher 5.86 включает в себя новые алгоритмы безопасности и поэтому старые пароли больше не будут работать. Скрипт сгенерировал для вас новый пароль:
$password

Пожалуйста, используйте его или получите другой на странице восстановления пароля:
$site.url/admin/password/

Сохранение старых паролей невозможно потому, что в системе никогда не хранились пароли, а только их хеши. Приносим извенения за доставленные неудобства. Новые алгоритмы защиты значительно усиливают безопасность вашего сайта, также не забывайте регулярно менять пароли для лучшей безопасности.

На сайтах, у которых псетители могли залогиниватся также сброшены все пароли, но им не была сделана рассылка уведомлений о смене паролей. При попытки залогинится таким посетителям будет предложено восстановить пароль. Для залогинивающихся через соцсети будет просто предложено еще раз авторизоваться (ранее они даже и не имели паролей)
', $args);

tmailer::sendtoadmin($subj, $body);
}