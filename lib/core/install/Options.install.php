<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\core;

use litepubl\config;
use litepubl\utils\Filer;

/* to prevent recurse call */
function installOptions($email, $language)
{
    $app = litepubl::$app;
    $options = Options::i();
    $options->lock();
    $options->solt = Str::md5Uniq();
    if (config::$db) {
        //set empty dbconfig to options
        $options->data['dbconfig'] = [
            'driver' => 'mysqli',
            'host' => 'localhost',
            'port' => 0,
            'dbname' => '',
            'login' => '',
            'password' => '',
        'crypt' => '',
            'prefix' => config::$db['prefix'],
        ];
    } else {
        $usehost = isset($_REQUEST['usehost']) ? ($_REQUEST['usehost'] == '1') : false;
        $options->data['dbconfig'] = [
            'driver' => 'mysqli',
            'host' => $usehost ? $_REQUEST['dbhost'] : 'localhost',
            'port' => $usehost ? (int)$_REQUEST['dbport'] : 0,
            'dbname' => $_REQUEST['dbname'],
            'login' => $_REQUEST['dblogin'],
            'password' => '',
        'crypt' => '',
            'prefix' => $_REQUEST['dbprefix']
        ];

        $options->setdbpassword($_REQUEST['dbpassword']);
    }

    try {
        $app->db = new DB();
    } catch (\Exception $e) {
        die($e->GetMessage());
    }

    if (config::$debug) {
        $db = $app->db;
        $list = $db->res2array($db->query("show tables from " . $options->dbconfig['dbname']));
        foreach ($list as $row) {
            $table = $row[0];
            if (Str::begin($table, $db->prefix)) {
                $db->exec('DROP TABLE IF EXISTS ' . $table);
            }
        }
    }

    $options->language = $language;
    $options->email = $email;
    $options->dateformat = '';
    $options->password = '';
    $password = Str::md5Uniq();
    $options->changepassword($password);
    $options->authenabled = true;
    $options->cookiehash = '';
    $options->cookieexpired = 0;
    $options->securecookie = false;

    $options->mailer = '';
    $options->data['cache'] = true;
    $options->expiredcache = 3600;
    $options->admincache = false;
    $options->ob_cache = true;
    $options->compress = false;
    $options->filetime_offset = Filer::getFiletimeOffset();
    $options->data['perpage'] = 10;
    $options->commentsdisabled = false;
    $options->comstatus = 'guest';
    $options->pingenabled = true;
    $options->commentpages = true;
    $options->commentsperpage = 100;
    $options->comments_invert_order = false;
    $options->commentspool = false;

    $versions = Str::toArray(file_get_contents(dirname(dirname(__DIR__)) . '/install/versions.txt'));
    $options->version = $versions[0];
    $options->echoexception = true;
    $options->parsepost = true;
    $options->hidefilesonpage = false;
    $options->show_draft_post = false;
    $options->usersenabled = false;
    $options->reguser = false;

    $options->show_file_perm = false;
    $options->xxxcheck = false;
    $options->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
    $options->unlock();
    return $password;
}
