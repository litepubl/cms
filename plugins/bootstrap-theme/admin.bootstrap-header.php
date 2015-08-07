<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class admin_bootstrap_header extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();

foreach (array('header', 'logo') as $name) {    
    $css = file_get_contents(dirname(__file__) . "/resource/css.$name.tml");
    $css = strtr($css, array(
    "\n" => '',
    "\r" => '',
    "'" => '"'
    ));
    
    $result .= "<script type=\"text/javascript\">litepubl.tml.$name = '$css';</script>";
}

    $result .= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
    $result .= '<script type="text/javascript" src="$site.files/plugins/bootstrap-theme/resource/header.js"></script>';

    return $result;
  }
  
  public function getcontent() {
    $tml = file_get_contents(dirname(__file__) . '/resource/content.tml');
    $lang = tlocal::inifile($this, '.admin.ini');
    $lang->addsearch('themeheader', 'editor');
    $html = tadminhtml::i();
    $args = new targs();
    return $html->parsearg($tml, $args);
  }
  
  public function request($a) {
    if ($response = parent::request($a)) {
      return $response;
    }
    
if (isset($_FILES['header'])) {
$name = 'image';
} elseif (isset($_FILES['logo'])) {
$name = 'logo';
} else {
return;
}

    if (is_uploaded_file($_FILES[$name]['tmp_name']) &&
    !$_FILES[$name]['error'] &&
    strbegin($_FILES[$name]['type'], 'image/') &&
    ($data = file_get_contents($_FILES[$name]['tmp_name']))
    ) {
      $css = file_get_contents(dirname(__file__) . "/resource/css.$name.tml");
      $css = str_replace('%%file%%', 'data:%s;base64,%s', $css);
      $css = sprintf($css, $_FILES[$name]['type'], base64_encode($data));
      
      $filename = litepublisher::$paths->files . "js/$name.css";
      file_put_contents($filename, $css);
      @chmod($filename, 0666);
      
      $merger = tcssmerger::i();
      $merger->lock();
      $merger->add('default', "files/js/$name.css");
      $merger->unlock();
      
      //file_put_contents($filename . '.tmp', $data);
      
      $result = array('result' => 'ok');
} else {
      $result = array('result' => 'error');
}

      $js = tojson($result);
      return "<?php
      header('Connection: close');
      header('Content-Length: ". strlen($js) . "');
      header('Content-Type: text/javascript; charset=utf-8');
      header('Date: ".date('r') . "');
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
      ?>" .
      $js;
  }
  
}//class