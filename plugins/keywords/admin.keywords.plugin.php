<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminkeywords extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $datadir = litepublisher::$paths->data . 'keywords' . DIRECTORY_SEPARATOR  ;
    $selfdir = dirname(__file__) . DIRECTORY_SEPARATOR ;
    $tml = parse_ini_file($selfdir . 'keywords.templates.ini', false);
    $about = tplugins::getabout(tplugins::getname(__file__));
    $html = $this->html;
    $lang = $this->lang;
    $args = targs::i();
    if (isset($_GET['filename'])) {
      $filename = $_GET['filename'];
      if (!@file_exists($datadir . $filename)) return $html->h3->notfound;
      $args->filename = $filename;
      $args->content =file_get_contents($datadir . $filename);
      $args->formtitle = $about['edithead'];
      return $html->adminform('[editor=content]', $args);
    }
    
    $page = isset($_GET['page'])  ? (int) $_GET['page'] : 1;
    $result = '';
    if ($page == 1) {
      $widget = tkeywordswidget::i();
      $widgets = twidgets::i();
      $idwidget = $widgets->find($widget);
      $args->count = $widget->count;
      $args->trace = $widget->trace;
      $args->notify = $widget->notify;
      $args->optionsform = 1;
      $args->title =       $widget->gettitle($idwidget);
      $args->blackwords = tadminhtml::specchars(implode("\n", tkeywordsplugin ::i()->blackwords));
      $lang = tplugins::getlangabout(__file__);
      $args->formtitle = $about['name'];
      $result .= $html->adminform(
      '[text=title]
      [text=count]
      [checkbox=trace]
      [checkbox=notify]
      [editor=blackwords]
      [hidden=optionsform]',
      $args);
    }
    
    $from = 100 * ($page - 1);
    $filelist = tfiler::getfiles($datadir);
    sort($filelist);
    $count = ceil(count($filelist)/ 100);
    $links = $this->getlinkpages($page, $count);
    $result .= $links;
    $filelist = array_slice($filelist, $from, 100, true);
    $list = '';
    $args->url = litepublisher::$site->url. '/admin/plugins/' . litepublisher::$site->q . 'plugin=' . basename(dirname(__file__));
    foreach ($filelist as $filename) {
      if (!preg_match('/^\d+?\.\d+?\.php$/', $filename)) continue;
      $args->filename = $filename;
      $args->content = file_get_contents($datadir . $filename);
      $list .= $html->parsearg($tml['item'], $args);
    }
    
    $args->list = $list;
    $result .= $html->parsearg($tml['form'], $args);
    $result .= $links;
    return $result;
  }
  
  private function getlinkpages($page, $count) {
    $url = litepublisher::$site->url. '/admin/plugins/' . litepublisher::$site->q . 'plugin=' . basename(dirname(__file__));
    $result = "<a href='$url'>1</a>\n";
    for ($i = 2; $i <= $count; $i++) {
      $result .= "<a href='$url&page=$i'>$i</a>|\n";
    }
    return sprintf("<p>\n%s</p>\n", $result);
  }
  
  public function processform() {
    $datadir = litepublisher::$paths->data . 'keywords' . DIRECTORY_SEPARATOR  ;
    if (isset($_POST['optionsform'])) {
      extract($_POST, EXTR_SKIP);
      $plugin = tkeywordsplugin::i();
      $widget = tkeywordswidget::i();
      $widgets = twidgets::i();
      $idwidget = $widgets->find($widget);
      $widget->lock();
      $widget->settitle($idwidget, $title);
      $widget->count = (int) $count;
      $widget->notify = isset($notify);
      $trace = isset($trace);
      if ($widget->trace != $trace) {
        if ($trace) {
          litepublisher::$urlmap->afterrequest = $plugin->parseref;
        } else {
          litepublisher::$urlmap->delete_event_class('afterrequest', get_class($plugin));
        }
      }
      
      $widget->trace = $trace;
      $widget->unlock();
      
      $plugin->blackwords = array();
      $words = strtoarray($blackwords);
      if (litepublisher::$options->language != 'en') {
        tlocal::usefile('translit');
        foreach ($words as $word) {
          $word = strtr($word, tlocal::$self->ini['translit']);
          $word = trim($word);
          if (empty($word)) continue;
          $plugin->blackwords[] = strtolower($word);
        }
      }
      $plugin->save();
      return;
    }
    
    if (isset($_GET['filename'])) {
      $filename = str_replace('_', '.', $_GET['filename']);
      $content = trim($_POST['content']);
      if ($content == '') {
        @unlink($datadir . $filename);
      } else {
        file_put_contents($datadir . $filename, $content);
      }
      return;
    }
    
    foreach ($_POST as $filename => $value) {
      $filename = str_replace('_', '.', $filename);
      if (preg_match('/^\d+?\.\d+?\.php$/', $filename)) unlink($datadir . $filename);
    }
  }
  
}//class