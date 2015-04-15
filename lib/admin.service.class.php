<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminservice extends tadminmenu {
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcont() {
    return ttheme::parsevar('menu', $this, ttheme::i()->templates['content.menu']);
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $args = targs::i();
    
    switch ($this->name) {
      case 'service':
      if (!dbversion) {
        return $html->h2->noupdates;
      }
      
      $lang = $this->lang;
      $result .= $html->h3->info;
      $result .= $this->doupdate($_GET);
      $result .= $html->tableprops(array(
      'postscount' => litepublisher::$classes->posts->count,
      'commentscount' =>litepublisher::$classes->commentmanager->count,
      'version' => litepublisher::$site->version
      ));
      $updater = tupdater::i();
      $islatest= $updater->islatest();
      if ($islatest === false) {
        $result .= $html->h4->errorservice;
      } elseif ($islatest <= 0) {
        $result .= $html->h4->islatest;
      } else {
        $form = new adminform($args);
        $form->title = tlocal::i()->requireupdate;
        $form->items = $this->getloginform(). '[submit=autoupdate]';
        $form->submit = 'manualupdate';
        $result .= $form->get();
      }
      break;
      
      
      case 'backup':
      if (empty($_GET['action'])) {
        
        $args->plugins = false;
        $args->theme = false;
        $args->lib = false;
        $args->dbversion = dbversion ? '' : 'disabled="disabled"';
        $args->saveurl = true;
        
        $form = new adminform($args);
        $form->upload = true;
        $form->items =  $html->h4->partialform;
        $form->items .= $this->getloginform();
        $form->items .= '[checkbox=plugins]
        [checkbox=theme]
        [checkbox=lib]
        [submit=downloadpartial]';
        
        $form->items .= $html->p->notefullbackup;
        $form->items .= '[submit=fullbackup]
        [submit=sqlbackup]';
        
        $form->items .= $html->h4->uploadhead;
        $form->items .= '[upload=filename]
        [checkbox=saveurl]';
        
        $form->submit = 'restore';
        $result= $form->get();
        $result .= $this->getbackupfilelist();
      } else {
        $filename = $_GET['id'];
        if (strpbrk ($filename, '/\<>')) return $this->notfound;
        if (!file_exists(litepublisher::$paths->backup . $filename)) return $this->notfound;
        switch ($_GET['action']) {
          case 'download':
          if ($s = @file_get_contents(litepublisher::$paths->backup . $filename)) {
            $this->sendfile($s, $filename);
          } else {
            return $this->notfound;
          }
          break;
          
          case 'delete':
          if ($this->confirmed) {
            @unlink(litepublisher::$paths->backup . $filename);
            return $html->h2->backupdeleted;
          } else {
            $args->adminurl = $this->adminurl;
            $args->id=$_GET['id'];
            $args->action = 'delete';
            $args->confirm = sprintf('%s %s?', $this->lang->confirmdelete, $_GET['id']);
            $result .= $html->confirmform($args);
          }
        }
      }
      break;
      
      case 'run':
      $args->formtitle = $this->lang->runhead;
      $args->content = isset($_POST['content']) ? $_POST['content'] : '';
      $result = $html->adminform('[editor=content]', $args);
      break;
      
      case 'upload':
      $args->url = str_replace('$mysite', rawurlencode(litepublisher::$site->url),tadminhtml::getparam('url', ''));
      $lang = tlocal::admin();
      $form = new  adminform($args);
      $form->title = $lang->uploaditem;
      $form->upload = true;
      $form->items = '[text=url]
      [upload=filename]' .
      $this->getloginform();
      $result = $html->p->uploaditems;
      $result .= $form->get();
      break;
    }
    
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  private function doupdate($req) {
    $html = $this->html;
    $updater = tupdater::i();
    if (isset($req['autoupdate'])) {
      if (!$this->checkbackuper()) return $html->h4->erroraccount;
      if ($updater->autoupdate())       return $html->h4->successupdated;
      return sprintf('<h3>%s</h3>', $updater->result);
    } elseif (isset($req['manualupdate'])) {
      $updater->update();
      return $html->h4->successupdated;
    }
    return '';
  }
  
  public function checkbackuper() {
    $backuper = tbackuper::i();
    if ($backuper->filertype == 'file') return true;
    $host = tadminhtml::getparam('host', '');
    $login = tadminhtml::getparam('login', '');
    $password = tadminhtml::getparam('password', '');
    if (($host == '') || ($login == '') || ($password == '')) return '';
    
    return $backuper->connect($host, $login, $password);
  }
  
  public function getloginform() {
    $backuper = tbackuper::i();
    //$backuper->filertype = 'ftp';
    if ($backuper->filertype == 'file') return '';
    $html = $this->html;
    $args = targs::i();
    $acc = $backuper->filertype == 'ssh2' ? $html->h3->ssh2account : $html->h3->ftpaccount;
    $args->host = tadminhtml::getparam('host', '');
    $args->login = tadminhtml::getparam('login', '');
    $args->password = tadminhtml::getparam('pasword', '');
    return $acc. $html->parsearg('[text=host] [text=login] [password=password]', $args);
  }
  
  public function processform() {
    $html = $this->html;
    
    switch ($this->name) {
      case 'service':
      return $this->doupdate($_POST);
      
      case 'backup':
      if (!$this->checkbackuper()) return $html->h3->erroraccount;
      extract($_POST, EXTR_SKIP);
      $backuper = tbackuper::i();
      if (isset($restore)) {
        if (!is_uploaded_file($_FILES['filename']['tmp_name'])) {
          return sprintf($html->h4red->attack, $_FILES["filename"]["name"]);
        }
        
        if (strpos($_FILES['filename']['name'], '.sql')) {
          $backuper->uploaddump(file_get_contents($_FILES["filename"]["tmp_name"]), $_FILES["filename"]["name"]);
        } else {
          $url = litepublisher::$site->url;
          if (dbversion) $dbconfig = litepublisher::$options->dbconfig;
          $backuper->upload(file_get_contents($_FILES['filename']['tmp_name']), $backuper->getarchtype($_FILES['filename']['name']));
          if (isset($saveurl)) {
            $storage = new tdata();
            $storage->basename = 'storage';
            $storage->load();
            $storage->data['site'] = litepublisher::$site->data;
            if (dbversion) $data->data['options']['dbconfig'] = $dbconfig;
            $storage->save();
          }
        }
        ttheme::clearcache();
        @header('Location: http://' . $_SERVER['HTTP_HOST'] .  $_SERVER['REQUEST_URI']);
        exit();
        
      } elseif (isset($downloadpartial)) {
        $filename = str_replace('.', '-', litepublisher::$domain) . date('-Y-m-d') . $backuper->getfiletype();
        $content = $backuper->getpartial(isset($plugins), isset($theme), isset($lib));
        $this->sendfile($content, $filename);
      } elseif (isset($fullbackup)) {
        $filename = str_replace('.', '-', litepublisher::$domain) . date('-Y-m-d') . $backuper->getfiletype();
        $content = $backuper->getfull();
        $this->sendfile($content, '');
      } elseif (isset($sqlbackup)) {
        $content = $backuper->getdump();
        $filename = litepublisher::$domain . date('-Y-m-d') . '.sql';
        
        switch ($backuper->archtype) {
          case 'tar':
          tbackuper::include_tar();
          $tar = new tar();
          $tar->addstring($content, $filename, 0644);
          $content = $this->tar->savetostring(true);
          $filename .= '.tar.gz';
          unset($tar);
          break;
          
          case 'zip':
          tbackuper::include_zip();
          $zip = new zipfile();
          $zip->addFile($content, $filename);
          $content = $zip->file();
          $filename .= '.zip';
          unset($zip);
          break;
          
          default:
          $content = gzencode($content);
          $filename .= '.gz';
          break;
        }
        
        $this->sendfile($content, $filename);
      }
      break;
      
      case 'run':
      $result = eval($_POST['content']);
      return sprintf('<pre>%s</pre>', $result);
      
      case 'upload':
      $backuper = tbackuper::i();
      if (!$this->checkbackuper()) return $html->h3->erroraccount;
      if (is_uploaded_file($_FILES['filename']['tmp_name']) && !(isset($_FILES['filename']['error']) && ($_FILES['filename']['error'] > 0))) {
        $s = file_get_contents($_FILES['filename']['tmp_name']);
        $archtype = $backuper->getarchtype($_FILES['filename']['name']);
      } else {
        $url = trim($_POST['url']);
        if (empty($url)) return '';
        if (!($s = http::get($url))) return $html->h3->errordownload;
        $archtype = $backuper->getarchtype($url);
      }
      
      if (!$archtype) {
        //         local file header signature     4 bytes  (0x04034b50)
        $archtype = strbegin($s, "\x50\x4b\x03\x04") ? 'zip' : 'tar';
      }
      
      if ($backuper->uploaditem($s, $archtype)) {
        return $html->h3->itemuploaded;
      } else {
        return sprintf('<h3>%s</h3>', $backuper->result);
      }
      break;
    }
    
  }
  
  private function sendfile(&$content, $filename) {
    //@file_put_contents(litepublisher::$domain . ".zip", $content);
    if ($filename == '') $filename = str_replace('.', '-', litepublisher::$domain) . date('-Y-m-d') . '.zip';
    if (ob_get_level()) ob_end_clean ();
    header('HTTP/1.1 200 OK', true, 200);
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Length: ' .strlen($content));
    header('Last-Modified: ' . date('r'));
    
    echo $content;
    exit();
  }
  
  private function getbackupfilelist() {
    $list = tfiler::getfiles(litepublisher::$paths->backup );
    if (!count($list)) return '';
    $items = array();    $html = $this->html;
    foreach($list as $filename) {
      if (strend($filename, '.gz') || strend($filename, '.zip')) {
        $items[]['filename'] = $filename;
      }
    }
    
    if (!count($items)) return '';
    $lang = $this->lang;
    return $this->html->h4->backupheadern .
    $this->html->buildtable($items, array(
    array('right', $lang->download, "<a href=\"$this->adminurl=\$filename&action=download\">\$filename</a>"),
    array('right', $lang->delete, "<a href=\"$this->adminurl=\$filename&action=delete\">$lang->delete</a>")
    ));
  }
  
}//class
?>