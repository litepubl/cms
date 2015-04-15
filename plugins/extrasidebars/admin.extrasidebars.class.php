<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminextrasidebars implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = textrasidebars ::i();
    $html = tadminhtml::i();
    $themes = tadminthemes::getlist(
    '<li><input name="theme-$name" id="checkbox-theme-$name" type="checkbox" value="$name" $checked />
    <label for="checkbox-theme-$name"><img src="$site.files/themes/$name/$screenshot" alt="$name" /></label>
    $lang.version:$version $lang.author: <a href="$url">$author</a> $lang.description:  $description</li>',
    $plugin->themes);
    
    $args = targs::i();
    $lang = tplugins::getlangabout(__file__);
    $args->formtitle = $lang->name;
    $args->beforepost = $plugin->beforepost;
    $args->afterpost = $plugin->afterpost;
    
    return $html->adminform('[checkbox=beforepost] [checkbox=afterpost]' .
    "<h4>$lang->themes</h4><ul>$themes</ul>",
    $args);
  }
  
  public function processform() {
    $plugin = textrasidebars ::i();
    $plugin->beforepost = isset($_POST['beforepost']);
    $plugin->afterpost = isset($_POST['afterpost']);
    $plugin->themes =tadminhtml::check2array('theme-');
    $plugin->save();
    
    ttheme::clearcache();
  }
  
}//class