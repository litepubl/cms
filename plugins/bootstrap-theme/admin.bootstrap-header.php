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

$css = file_get_contents(dirname(__file__) . '/resource/css.tml');
$css = strtr($css, array(
"\n" => '',
"\r" => '',
"'" => '"'
));

$result .= "<script type=\"text/javascript\">litepubl.tml.header = '" . $css  . "';</script>";
$result .= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
$result .= '<script type="text/javascript" src="$site.files/plugins/bootstrap-theme/resource/header.min.js"></script>';
//$result .= "<script type=\"text/javascript\">alert(litepubl.tml.header );</script>";
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

$k = 'image';
    if (isset($_FILES[$k]) &&
 is_uploaded_file($_FILES[$k]['tmp_name']) &&
 !$_FILES[$k]['error'] &&
strbegin($_FILES[$k]['type'], 'image/') &&
($data = file_get_contents($_FILES[$k]['tmp_name']))
) {
$css = file_get_contents(dirname(__file__) . '/resource/css.tml');
$css = str_replace('%%file%%', sprintf('data:%s;base64,%s', $_FILES[$k]['type'], base64_encode($data)), $css);
$filename = litepublisher::$paths->files . 'js/header.css';
file_put_contents($filename, $css);
@chmod($filename, 0666);

$merger = tcssmerger::i();
$merger->lock();
$merger->add('default', 'files/js/header.css');
$merger->unlock();

//file_put_contents($filename . '.tmp', $data);

    $lang = tlocal::inifile($this, '.admin.ini');

$result = array('result' => 'ok');
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
}

}//class