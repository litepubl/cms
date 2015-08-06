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

$result .= "<script type=\"text/javascript\">ltoptions.header_tml ='" . file_get_contents(dirname(__file__) . '/resource/header.tml')) . "';</script>";
$result .= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
$result .= '<script type="text/javascript" src="$site.files/plugins/bootstrap-theme/resource/header.min.js"></script>';

return $result;
}
  
  public function getcontent() {
    $result = '';
    $views = tviews::i();
    $theme = tview::i($views->defaults['admin'])->theme;
    $html = $this->inihtml();
    $lang = tlocal::inifile($this, '.admin.ini');
    $args = new targs();
    
    $mainsidebars = array(
    'left' => $lang->left,
    'right' => $lang->right,
    );
    
    foreach ($views->items as $id => $item) {
      if (!isset($item['custom']['mainsidebar'])) continue;
      
      $result .= $html->h4($item['name']);
      $result .=$theme->getinput('combo', "mainsidebar-$id",
      tadminhtml::array2combo($mainsidebars, $item['custom']['mainsidebar']), $lang->mainsidebar);
      
      $result .=$theme->getinput('combo', "cssfile-$id",
      tadminhtml::array2combo($lang->ini['subthemes'], $item['custom']['cssfile']), $lang->cssfile);
      
      $result .= '<hr>';
    }
    
    $args->formtitle = $lang->customizeview;
    return $html->adminform($result, $args);
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
$css = file_get_contents(dirname(__file__) . '/resource/header.tml');
$css = strtr($css, array(
'%%type%%' => _FILES[$k]['type'],
'%%data%%' => base64_encode($data)
);

$filename = litepublisher::$paths->files . 'js/header.css';
file_put_contents($filename, $css);
@chmod($filename, 0666);
tcssmerger::i()->add('default', 'files/js/header.css');

    $lang = tlocal::inifile($this, '.admin.ini');
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