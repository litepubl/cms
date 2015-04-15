<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tdownloaditemsInstall($self) {
  if (!dbversion) die("Downloads require database");
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  $manager = tdbmanager ::i();
  $manager->CreateTable($self->childtable, file_get_contents($dir .'downloaditem.sql'));
  $manager->addenum('posts', 'class', 'tdownloaditem');
  
  $optimizer = tdboptimizer::i();
  $optimizer->lock();
  $optimizer->childtables[] = 'downloaditems';
  $optimizer->addevent('postsdeleted', get_class($self), 'postsdeleted');
  $optimizer->unlock();
  
  tlocalmerger::i()->add('default', "plugins/" . basename(dirname(__file__)) . "/resource/" . litepublisher::$options->language . ".ini");
  
  $ini = parse_ini_file($dir . litepublisher::$options->language . '.install.ini', false);
  
  $tags = ttags::i();
  litepublisher::$options->downloaditem_themetag = $tags->add(0, $ini['themetag']);
  litepublisher::$options->downloaditem_plugintag = $tags->add(0, $ini['plugintag']);
  $base = basename(dirname(__file__));
  $classes = litepublisher::$classes;
  $classes->lock();
  /*
  //install polls if its needed
  $plugins = tplugins::i();
  if (!isset($plugins->items['polls'])) $plugins->add('polls');
  $polls = tpolls::i();
  $polls->garbage = false;
  $polls->save();
  */
  
  $classes->Add('tdownloaditem', 'downloaditem.class.php', $base);
  $classes->Add('tdownloaditemsmenu', 'downloaditems.menu.class.php', $base);
  $classes->Add('tdownloaditemeditor', 'admin.downloaditem.editor.class.php',$base);
  $classes->Add('tadmindownloaditems', 'admin.downloaditems.class.php', $base);
  $classes->Add('tdownloaditemcounter', 'downloaditem.counter.class.php', $base);
  $classes->Add('taboutparser', 'about.parser.class.php', $base);
  $classes->unlock();
  
  tadminhtml::i()->inidir(dirname(__file__) . '/resource/');
  $lang = tlocal::i('downloaditems');
  $lang->ini['downloaditems'] = $lang->ini['downloaditem'] + $lang->ini['downloaditems'];
  
  $adminmenus = tadminmenus::i();
  $adminmenus->lock();
  $parent = $adminmenus->createitem(0, 'downloaditems', 'editor', 'tadmindownloaditems');
  $adminmenus->items[$parent]['title'] = $lang->downloaditems;
  
  $idmenu = $adminmenus->createitem($parent, 'addurl', 'editor', 'tadmindownloaditems');
  $adminmenus->items[$idmenu]['title'] = $lang->addurl;
  
  $idmenu = $adminmenus->createitem($parent, 'editor', 'editor', 'tdownloaditemeditor');
  $adminmenus->items[$idmenu]['title'] = $lang->add;
  
  $idmenu = $adminmenus->createitem($parent, 'theme', 'editor', 'tadmindownloaditems');
  $adminmenus->items[$idmenu]['title'] = $lang->themes;
  
  $idmenu = $adminmenus->createitem($parent, 'plugin', 'editor', 'tadmindownloaditems');
  $adminmenus->items[$idmenu]['title'] = $lang->plugins;
  
  $adminmenus->unlock();
  
  $menus = tmenus::i();
  $menus->lock();
  $menu = tdownloaditemsmenu::i();
  $menu->type = '';
  $menu->url = '/downloads.htm';
  $menu->title = $ini['downloads'];
  $menu->content = '';
  $id = $menus->add($menu);
  litepublisher::$urlmap->db->setvalue($menu->idurl, 'type', 'get');
  
  foreach (array('theme', 'plugin') as $type) {
    $menu = tdownloaditemsmenu::i();
    $menu->type = $type;
    $menu->parent = $id;
    $menu->url = sprintf('/downloads/%ss.htm', $type);
    $menu->title = $lang->__get($type . 's');
    $menu->content = '';
    $menus->add($menu);
    litepublisher::$urlmap->db->setvalue($menu->idurl, 'type', 'get');
  }
  $menus->unlock();
  
  tjsmerger::i()->add('default', '/plugins/downloaditem/downloaditem.min.js');
  
  $parser = tthemeparser::i();
  $parser->parsed = $self->themeparsed;
  ttheme::clearcache();
  
  $linkgen = tlinkgenerator::i();
  $linkgen->data['downloaditem'] = '/[type]/[title].htm';
  $linkgen->save();
  litepublisher::$options->savemodified();
}

function tdownloaditemsUninstall($self) {
  //die("Warning! You can lost all downloaditems!");
  tposts::unsub($self);
  
  $adminmenus = tadminmenus::i();
  $adminmenus->deletetree($adminmenus->url2id('/admin/downloaditems/'));
  
  $menus = tmenus::i();
  $menus->deletetree($menus->class2id('tdownloaditemsmenu'));
  
  $parser = tthemeparser::i();
  $parser->unbind($self);
  ttheme::clearcache();
  
  $classes = litepublisher::$classes;
  $classes->lock();
  $classes->delete('tdownloaditem');
  $classes->delete('tdownloaditemsmenu');
  $classes->delete('tdownloaditemeditor');
  $classes->delete('tadmindownloaditems');
  $classes->delete('tdownloaditemcounter');
  $classes->delete('taboutparser');
  $classes->unlock();
  
  $merger = tlocalmerger::i();
  $merger->deleteplugin(tplugins::getname(__file__));
  
  $manager = tdbmanager ::i();
  $manager->deletetable($self->childtable);
  $manager->delete_enum('posts', 'class', 'tdownloaditem');
  
  $optimizer = tdboptimizer::i();
  $optimizer->lock();
  $optimizer->unbind($self);
  if (false !== ($i = array_search('downloaditems', $optimizer->childtables))) {
    unset($optimizer->childtables[$i]);
  }
  $optimizer->unlock();
  
  tjsmerger::i()->deletefile('default', '/plugins/downloaditem/downloaditem.min.js');
  
  litepublisher::$options->delete('downloaditem_themetag');
  litepublisher::$options->delete('downloaditem_plugintag');
  litepublisher::$options->savemodified();
}

function getd_download_js() {
  $result ='<script type="text/javascript">';
  $result .= "\n\$(document).ready(function() {\n";
    $result .= "if (\$(\"a[rel='theme'], a[rel='plugin']\").length) {\n";
      $result .= '$.load_script("$site.files/plugins/' . basename(dirname(__file__)) . "/downloaditem.min.js\");\n";
    $result .= "}\n";
  $result.= "});\n";
  $result .= "</script>";
  return $result;
}

function add_downloaditems_to_theme($theme) {
  if (empty($theme->templates['custom']['downloadexcerpt'])) {
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    ttheme::$vars['lang'] = tlocal::admin('downloaditems');
    $custom = &$theme->templates['custom'];
    $custom['downloaditem'] = $theme->replacelang(file_get_contents($dir . 'downloaditem.tml'), tlocal::i('downloaditem'));
    $lang = tlocal::i('downloaditems');
    $custom['downloadexcerpt'] = $theme->replacelang(file_get_contents($dir . 'downloadexcerpt.tml'), $lang);
    $custom['siteform'] = $theme->parse(file_get_contents($dir . 'siteform.tml'));
    
    //admin
    $admin = &$theme->templates['customadmin'];
    $admin['downloadexcerpt'] = array(
    'type' => 'editor',
    'title' => $lang->downloadexcerpt
    );
    
    $admin['downloaditem'] = array(
    'type' => 'editor',
    'title' => $lang->downloadlinks
    );
    
    $admin['siteform'] = array(
    'type' => 'editor',
    'title' => $lang->siteform
    );
  }
  //var_dump($theme->templates['customadmin'], $theme->templates['custom']);
}