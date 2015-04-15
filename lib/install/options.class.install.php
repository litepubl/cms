<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

/* to prevent recurse call */
function installoptions($email, $language) {
  $options = toptions::i();
  $options->lock();
  $options->solt = md5uniq();
  $usehost = isset($_REQUEST['usehost']) ? ($_REQUEST['usehost'] == '1') : false;
  $options->data['dbconfig'] = array(
  'driver' => 'mysqli',
  'host' => $usehost ? $_REQUEST['dbhost'] : 'localhost',
  'port' => $usehost ? (int) $_REQUEST['dbport'] : 0,
  'dbname' => $_REQUEST['dbname'],
  'login' => $_REQUEST['dblogin'],
  'password' => '',
  'prefix' => $_REQUEST['dbprefix']
  );
  
  $options->setdbpassword($_REQUEST['dbpassword']);
  try {
    litepublisher::$db= new tdatabase();
  } catch (Exception $e) {
    die($e->GetMessage());
  }
  
  if (litepublisher::$debug) {
    $db = litepublisher::$db;
    $list = $db->res2array($db->query("show tables from " . $options->dbconfig['dbname']));
    foreach ($list as $row) {
      $table = $row[0];
      if (strbegin($table, $db->prefix)) {
        $db->exec('DROP TABLE IF EXISTS ' . $table);
      }
    }
  }
  
  $options->language = $language;
  $options->email = $email;
  $options->dateformat = '';
  $options->password = '';
  $password = md5uniq();
  $options->changepassword($password);
  $options->authenabled = true;
  $options->cookiehash = '';
  $options->cookieexpired = 0;
  $options->securecookie = false;
  
  $options->mailer = '';
  $options->data['cache'] = true;
  $options->expiredcache= 3600;
  $options->admincache = false;
  $options->ob_cache = true;
  $options->compress = false;
  $options->filetime_offset = tfiler::get_filetime_offset();
  $options->data['perpage'] = 10;
  $options->commentsdisabled = false;
  $options->comstatus = 'guest';
  $options->pingenabled = true;
  $options->commentpages = true;
  $options->commentsperpage = 100;
  $options->comments_invert_order = false;
  $options->commentspull = false;
  
  $versions = strtoarray(file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'versions.txt'));
  $options->version = $versions[0];
  $options->echoexception = true;
  $options->parsepost = true;
  $options->hidefilesonpage = false;
  $options->show_draft_post = false;
  $options->usersenabled = false;
  $options->reguser = false;
  $options->icondisabled = false;
  $options->crontime = time();
  $options->show_file_perm = false;
  $options->xxxcheck = empty($_SERVER['HTTP_REFERER']) && isset($_POST) && (count($_POST) > 0) ? false : true;
  $options->fromemail = 'litepublisher@' . $_SERVER['HTTP_HOST'];
  $options->unlock();
  return $password;
}