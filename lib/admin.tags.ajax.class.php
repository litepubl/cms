<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxtageditor extends tajaxposteditor  {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    litepublisher::$urlmap->addget('/admin/ajaxtageditor.htm', get_class($this));
  }
  
  public function request($arg) {
    if ($err = self::auth()) return $err;
    return $this->getcontent();
  }
  
  public function getcontent() {
    $type = tadminhtml::getparam('type', 'tags') == 'tags' ? 'tags' : 'categories';
    $tags = $type == 'tags' ? ttags::i() : tcategories::i();if ($err = self::auth()) return $err;
    $id = tadminhtml::idparam();
    if (($id > 0) && !$tags->itemexists($id)) return self::error403();
    
    $theme = tview::i(tviews::i()->defaults['admin'])->theme;
    $html = tadminhtml ::i();
    $html->section = 'tags';
    $lang = tlocal::i('tags');
    
    if ($id == 0) {
      $views = tviews::i();
      $name = $type == 'tags' ? 'tag' : 'category';
      $item = array(
      'title' => '',
      'idview' => isset($views->defaults[$name]) ? $views->defaults[$name] : 1,
      'idperm' => 0,
      'icon' => 0,
      'includechilds' => $tags->includechilds,
      'includeparents' => $tags->includeparents,
      'invertorder' => false,
      'lite' => $tags->lite,
      'liteperpage' => 1000,
      'url' => '',
      'keywords' => '',
      'description' => '',
      'head' => ''
      );
    } else {
      $item = $tags->getitem($id);
    }
    
    switch ($_GET['get']) {
      case 'view':
      if ($id > 0) {
        foreach (array('includechilds', 'includeparents', 'invertorder', 'lite') as $prop) {
          $item[$prop] = ((int) $item[$prop]) > 0;
        }
      }
      $args = new targs();
      $args->add($item);
      $result = $html->parsearg('[checkbox=includechilds] [checkbox=includeparents] [checkbox=invertorder] [checkbox=lite] [text=liteperpage]', $args);
      $result .= $this->getviewicon($item['idview'], $item['icon']);
      $result .= tadminperms::getcombo($item['idperm']);
      break;
      
      case 'seo':
      $args = targs::i();
      if ($id == 0) {
        $args->url = '';
        $args->keywords = '';
        $args->description = '';
        $args->head = '';
      } else {
        $args->add($tags->contents->getitem($id));
        $args->url = $tags->items[$id]['url'];
      }
      $result = $html->parsearg('[text=url] [text=description] [text=keywords] [editor=head]', $args);
      break;
      
      case 'text':
      $result = $this->geteditor('raw', $id == 0 ? '' : $tags->contents->getcontent($id), true);
      $result .= $this->gethead();
      break;
      
      default:
      $result = var_export($_GET, true);
    }
    return turlmap::htmlheader(false) . $result;
  }
  
}//class