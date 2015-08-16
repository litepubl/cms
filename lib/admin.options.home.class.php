<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class adminhomeoptions extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
public function gethead() {
$result = parent::gethead();

    $result .= '<script type="text/javascript" src="$site.files/js/plugins/filereader.min.js"></script>';
    $result .= '<script type="text/javascript" src="$site.files/js/litepubl/admin/homeuploader.js"></script>';

return $result;
}

  public function getcontent() {
    $args = new targs();
    $lang = tlocal::admin('options');
    $html = $this->html;
      $home = thomepage::i();
      $tabs = new tuitabs();
      $args->image = $home->image;
      $args->smallimage = $home->smallimage;
      $args->parsetags = $home->parsetags;
      $args->showmidle = $home->showmidle;
      $args->midlecat = tposteditor::getcombocategories(array(), $home->midlecat);
      $args->showposts = $home->showposts;
      $args->invertorder = $home->invertorder;
      $args->showpagenator = $home->showpagenator;
      
      $args->idhome =  $home->id;
      $menus = tmenus::i();
      $args->homemenu =  $menus->home;
      
      $tabs->add($lang->options, '
      [checkbox=homemenu]
      [checkbox=showmidle]
      [combo=midlecat]
      [checkbox=showposts]
      [checkbox=invertorder]
      [checkbox=showpagenator]
      [checkbox=parsetags]
      ');
      
      $tabs->add($lang->images,'
      [text=image]
      [text=smallimage]' .
      $html->p->imagehelp);
      
      $tabs->add($lang->includecats,
      $html->h4->includehome .
      tposteditor::getcategories($home->includecats));
      
      $tabs->add($lang->excludecats,
      $html->h4->excludehome . str_replace('category-', 'exclude_category-',
      tposteditor::getcategories($home->excludecats)));
      
      $args->formtitle = $lang->homeform;
      return tuitabs::gethead() .
      $html->adminform(
    '<h4><a href="$site.url/admin/menu/edit/{$site.q}id=$idhome">$lang.hometext</a></h4>' .
      $tabs->get(), $args);
}

  
  public function processform() {
    extract($_POST, EXTR_SKIP);
      $home = thomepage::i();
      $home->lock();
      $home->image = $image;
      $home->smallimage = $smallimage;
      $home->parsetags = isset($parsetags);
      $home->showmidle = isset($showmidle);
      $home->midlecat = (int) $midlecat;
      $home->showposts = isset($showposts);
      $home->invertorder = isset($invertorder);
      $home->includecats = tadminhtml::check2array('category-');
      $home->excludecats = tadminhtml::check2array('exclude_category-');
      $home->showpagenator = isset($showpagenator);
      $home->postschanged();
      $home->unlock();
      
      $menus = tmenus::i();
      $menus->home = isset($homemenu);
      $menus->save();
}

  public function request($a) {
    if ($response = parent::request($a)) {
      return $response;
    }
    
      $name = 'image';
        if (!isset($_FILES[$name])) return;

      $result = array('result' => 'error');

        if (is_uploaded_file($_FILES[$name]['tmp_name']) &&
    !$_FILES[$name]['error'] &&
    strbegin($_FILES[$name]['type'], 'image/') &&
    ($data = file_get_contents($_FILES[$name]['tmp_name']))
    ) {

      $filename = litepublisher::$paths->files . 'image/home.jpg';
if (file_exists($filename)) {
@unlink($filename);
}

if (move_uploaded_file($_FILES[$name]['tmp_name'], $filename)) {
@chmod($filename, 0666);

$info = @getimagesize($filename);
    if ($info && $info[0] && $info[1]) {

$parser = tmediaparser::i();


      $result = array('result' => array(
'image' => $home->image,
'smallimage' => $home->smallimage
));
}
    }
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