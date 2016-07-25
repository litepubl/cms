<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\update;

use litepubl\core\litepubl;
use litepubl\core\Crypt;
use litepubl\Config;
use litepubl\core\DBManager;

function update702()
{
$options = litepubl::$app->options;
if (!isset($options->data['dbconfig']['crypt'])) {
$options->data['dbconfig']['crypt'] = Crypt::METHOD;
$password = decrypt($options->data['dbconfig']['password'], $options->solt . Config::$secret);
$options->data['dbconfig']['password'] = Crypt::encode($password,$options->solt . Config::$secret);
$options->save();
}

if (isset($options->data['icondisabled'])) {
unset($options->data['icondisabled']);
$options->save();
}

$man = DBManager::i();

foreach (['posts', 'categories', 'tags', 'files'] as $table) {
if ($db->columnExists($table, 'icon')) {
$man->alter($table, 'drop icon');
}
}

if ($db->columnExists('posts', 'rss')) {
$man->alter('posts', 'drop rss');
}

}

function encrypt($s, $key)
    {
        $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        if (strlen($key) > $maxkey) {
            $key = substr($key, $maxkey);
        }

        $block = mcrypt_get_block_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($s) % $block);
        $s.= str_repeat(chr($pad), $pad);
        return mcrypt_encrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
    }

    function decrypt($s, $key)
    {
        $maxkey = mcrypt_get_key_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
        if (strlen($key) > $maxkey) {
            $key = substr($key, $maxkey);
        }

        $s = mcrypt_decrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
        $len = strlen($s);
        $pad = ord($s[$len - 1]);
        return substr($s, 0, $len - $pad);
}