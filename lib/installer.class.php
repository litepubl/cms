<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tinstaller extends tdata {
  public $language;
  public $mode;
  public $lite;
  public $resulttype;
  public $installed;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function DefineMode () {
    $this->mode = 'form';
    $this->language = $this->GetBrowserLang();
    $this->lite = false;
    $this->installed = false;
    
    if (isset($_GET) && (count($_GET) > 0)) {
      $_SERVER['REQUEST_URI']= substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
    }
    
    $sub = rtrim($_SERVER['REQUEST_URI'], '/');
    if ('/admin' == substr($sub, -6)) {
      $sub = substr($sub, 0, strlen($sub) - 5);
      header('Location: http://'. $_SERVER['HTTP_HOST'] . $sub);
      exit();
    }
    
    if (!empty($_GET['lang']))  {
      if ($this->langexists($_GET['lang'])) $this->language = $_GET['lang'];
    }
    
    if (!empty($_GET['mode'])) $this->mode = $_GET['mode'];
    if (!empty($_GET['lite'])) $this->lite = $_GET['lite'] == 1;
    if (!empty($_GET['resulttype'])) $this->resulttype = $_GET['resulttype'];
  }
  
  public function AutoInstall() {
    $this->CanInstall();
    $password = $this->FirstStep();
    
    $this->ProcessForm(
    $_GET['email'],
    $_GET['name'],
    $_GET['description'],
    isset($_GET['checkrewrite'])
    );
    
    $this->CreateDefaultItems($password);
    if ($this->mode == 'remote') {
      $this->OutputResult($password);
    }
  }
  
  public function OutputResult($password) {
    if ($this->mode != 'remote') return;
    litepublisher::$options->savemodified();
    
    $result = array(
    'url' => litepublisher::$site->url,
    'email' => litepublisher::$options->email,
    'password' => $password,
    'name' => litepublisher::$site->name,
    'description' => litepublisher::$site->description
    );
    
    switch ($this->resulttype) {
      case 'json' :
      $s = json_encode($result);
      header('Content-Type: text/javascript; charset=utf-8');
      BREAK;
      
      case 'serialized' :
      $s = serialize($result);
      header('Content-Type: text/plain; charset=utf-8');
      BREAK;
      
      case 'xmlrpc':
      $r = new IXR_Value($result);
      $s = '<?xml version="1.0" encoding="utf-8" ?>
      <methodResponse><params><param><value>' .
      $r->getXml() .
      '</value></param></params></methodResponse>';
      
      header('Content-Type: text/xml; charset=utf-8');
      break;
      
      default:
      die('Unknown remote method');
    }
    
    header('Connection: close');
    header('Last-Modified: ' . date('r'));
    Header( 'Cache-Control: no-cache, must-revalidate');
    Header( 'Pragma: no-cache');
    header('Content-Length: ' . strlen($s));
    echo $s;
    exit();
  }
  
  public function CreateDefaultItems($password) {
    if ($this->mode != 'remote') {
      $this->congratulation($password);
    }
    
    if (!$this->lite) $this->CreateFirstPost();
    
    $this->SendEmail($password);
    return $password;
  }
  
  public function CanInstall() {
    $this->CheckSystem();
    $this->CheckFolders();
  }
  
  public function FirstStep() {
    $this->CheckFolders();
    if (!defined('dbversion')) {
      if (isset($_REQUEST['dbversion'])) {
        define('dbversion', $_REQUEST['dbversion'] == '1');
      } else {
        define('dbversion', true);
      }
    }
    
    require_once(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'classes.install.php');
    parse_classes_ini(isset($_REQUEST['classes']) ? $_REQUEST['classes'] : false);
    return install_engine($_REQUEST['email'], $this->language);
  }
  
  public function install() {
    if (get_magic_quotes_gpc()) {
      if (isset($_POST) && (count($_POST) > 0)) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
      
      if (isset($_GET) && (count($_GET) > 0)) {
        foreach ($_GET as $name => $value) {
          $_GET[$name] = stripslashes($_GET[$name]);
        }
      }
      
    }
    
    $this->DefineMode();
    if ($this->mode != 'form') return $this->AutoInstall();
    
    if (!isset($_POST) || (count($_POST) <= 1)) {
      $this->CanInstall();
      return $this->wizardform();
    }
    
    $password = $this->FirstStep();
    $this->processform(
    $_POST['email'],
    $_POST['name'],
    $_POST['description'],
    isset($_POST['checkrewrite'])
    );
    
    return $this->CreateDefaultItems($password);
  }
  
  public function processform($email, $name, $description, $rewrite) {
    litepublisher::$options->lock();
    litepublisher::$options->email = $email;
    litepublisher::$site->name = $name;
    litepublisher::$site->description = $description;
    litepublisher::$options->fromemail = 'litepublisher@' . $_SERVER['SERVER_NAME'];
    $this->CheckApache($rewrite);
    if (litepublisher::$site->q == '&') litepublisher::$site->data['url'] .= '/index.php?url=';
    litepublisher::$options->unlock();
  }
  
  public function CheckFolders() {
    $this->checkFolder(litepublisher::$paths->data);
    $this->CheckFolder(litepublisher::$paths->cache);
    $this->CheckFolder(litepublisher::$paths->files);
    //$this->CheckFolder(litepublisher::$paths->languages);
    //$this->CheckFolder(litepublisher::$paths->plugins);
    //$this->CheckFolder(litepublisher::$paths->themes);
  }
  
  public function CheckFolder($folder) {
    if(!file_exists($folder)) {
      $up = dirname($folder);
      if(!file_exists($up)) {
        @mkdir($up, 0777);
        @chmod($up, 0777);
      }
      @mkdir($folder, 0777);
    }
    @chmod($folder, 0777);
    if(!file_exists($folder) && !@is_dir($FolderName)) {
      echo "directory $folder is not exists. Please create directory and set permisions to 0777";
      exit();
    }
    $tmp= $folder . 'index.htm';
    if (!@file_put_contents($tmp, ' ')) {
      echo "Error write file to the $folder folder. Please change permisions to 0777";
      exit();
    }
    @chmod($tmp, 0666);
    //@unlink($tmp);
  }
  
  public function  CheckSystem() {
    if (version_compare(PHP_VERSION, '5.1.4', '<')) {
      echo 'LitePublisher requires PHP 5.1.4 or later. You are using PHP ' . PHP_VERSION ;
      exit;
    }
    
    if (!class_exists('domDocument')) {
      echo 'LitePublisher requires "domDocument" class and domxml extension';
      exit;
    }
    
    if (!function_exists('mcrypt_encrypt')) {
      echo 'LitePublisher requires "mcrypt_encrypt" functions';
      exit;
    }
  }
  
  public function CheckApache($rewrite) {
    if ($rewrite || (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))) {
      litepublisher::$site->q = '?';
    } else {
      litepublisher::$site->q = '&';
    }
  }
  
  public function wizardform() {
    $this->loadlang();
    $combobox = $this->getlangcombo();
    $html = tadminhtml::i();
    $html->section = 'installation';
    $lang = tlocal::i('installation');
    if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
      $checkrewrite   = '';
    } else {
      eval('$checkrewrite =  "'. $html->checkrewrite . '\n";');
    }
    $dbprefix = strtolower(str_replace(array('.', '-'), '', litepublisher::$domain)) . '_';
    $title = tlocal::get('installation', 'title');
    $form = file_get_contents(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'installform.tml');
    $form = str_replace('"', '\"', $form);
    eval('$form = "'. $form . '\n";');
    $this->echohtml(  $form);
  }
  
  private function getlangcombo() {
    $langs = array(
    'en' => 'English',
    'ru' => 'Russian'
    //'ua' => 'Ukrain'
    );
    
    $result = '';
    foreach ($langs as $lang => $value) {
      $selected = $lang == $this->language ? 'selected' : '';
      $result .= "<option value='$lang' $selected>$value</option>\n";
    }
    return $result;
  }
  
  public function CreateFirstPost() {
    $html = tadminhtml::i();
    $html->section = 'installation';
    $lang = tlocal::i();
    $theme = ttheme::i();
    
    $post = tpost::i(0);
    $post->title = $lang->posttitle;
    $post->catnames = $lang->postcategories;
    $post->tagnames = $lang->posttags;
    $post->content = $theme->parse($lang->postcontent);
    $posts = tposts::i();
    $posts->add($post);
    
    $icons = ticons::i();
    $cats = tcategories::i();
    $cats->setvalue($post->categories[0], 'icon', $icons->getid('news'));
    
    $cm = tcommentmanager::i();
    $users = tusers::i();
    $cm->idguest =  $users->add(array(
    'email' => '',
    'name' => tlocal::get('default', 'guest'),
    'status' => 'hold',
    'idgroups' => 'commentator'
    ));
    
    $cm->save();
    $users->setvalue($cm->idguest, 'status', 'approved');
    
    tcomments::i()->add($post->id, $cm->idguest,$lang->postcomment, 'approved', '127.0.0.1');
    
    $plugins = tplugins::i();
    $plugins->lock();
    $plugins->add('oldestposts');
    //$plugins->add('adminlinks');
    //$plugins->add('nicedit');
    $plugins->unlock();
  }
  
  public function SendEmail($password) {
    define('mailpassword', $password);
    register_shutdown_function(__class__ . '::sendmail');
  }
  
  public static function sendmail() {
    $lang = tlocal::$self->ini['installation'];
    $body = sprintf($lang['body'], litepublisher::$site->url, litepublisher::$options->email, mailpassword);
    
    tmailer::sendmail('', litepublisher::$options->fromemail,
    '', litepublisher::$options->email, $lang['subject'], $body);
  }
  
  public function congratulation($password) {
    global  $lang;
    $tml = file_get_contents(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.congratulation.tml');
    $theme = ttheme::getinstance('default');
    $html = tadminhtml::i();
    $html->section = 'installation';
    $lang = tlocal::i('installation');
    $args = targs::i();
    $args->title = litepublisher::$site->name;
    $args->url = litepublisher::$site->url . '/';
    $args->password = $password;
    $args->likeurl = litepublisher::$options->language == 'ru' ? 'litepublisher.ru' : 'litepublisher.com';
    $content = $html->parsearg($tml, $args);
    $this->echohtml($content);
  }
  
  public function uninstall() {
    tfiler::delete(litepublisher::$paths->data, true);
    tfiler::delete(litepublisher::$paths->cache, true);
    tfiler::delete(litepublisher::$pathsfiles, true);
  }
  
  private function loadlang() {
    //litepublisher::$options = $this;
    //require_once(litepublisher::$paths->lib . 'filer.class.php');
    require_once(litepublisher::$paths->lib . 'local.class.php');
    require_once(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'local.class.install.php');
    require_once(litepublisher::$paths->lib . 'htmlresource.class.php');
    tlocalPreinstall($this->language);
  }
  
  private function GetBrowserLang() {
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $result = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
      $result = substr($result, 0, 2);
      if ($this->langexists($result))  return $result;
    }
    return 'en';
  }
  
  public function langexists($language) {
    return @file_exists(litepublisher::$paths->languages . $language . DIRECTORY_SEPARATOR . 'default.ini');
  }
  
  public function echohtml($html) {
    @header('Content-Type: text/html; charset=utf-8');
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    echo $html;
    if (ob_get_level()) ob_end_flush ();
  }
  
}//class