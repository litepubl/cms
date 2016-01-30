<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class admintheme extends basetheme {
public $onfileperm;

  public static function i() {
    $result = getinstance(__class__);
    if (!$result->name && ($context = litepublisher::$urlmap->context)) {
      $result->name = tview::getview($context)->adminname;
      $result->load();
    }

    return $result;
  }

  public static function getinstance($name) {
    return self::getbyname(__class__, $name);
  }

  public function getparser() {
    return adminparser::i();
  }

  public function gettable($head, $body) {
    return strtr($this->templates['table'], array(
      '$class' => ttheme::i()->templates['content.admin.tableclass'],
      '$head' => $head,
      '$body' => $body
    ));
  }

public function success($text) {
return str_replace('$text', $text, $this->templates['success']);
}

  public function getsection($title, $content) {
    return strtr($this->templates['section'], array(
      '$title' => $title,
      '$content' => $content
    ));
  }

  public function geterr($content) {
    return strtr($this->templates['error'], array(
      '$title' => tlocal::i()->error,
      '$content' => $content
    ));
  }

  public function getcalendar($name, $date) {
    $date = datefilter::timestamp($date);

    $args = new targs();
    $args->name = $name;
    $args->title = tlocal::i()->__get($name);
    $args->format = datefilter::$format;

    if ($date) {
      $args->date = date(datefilter::$format, $date);
      $args->time = date(datefilter::$timeformat, $date);
    } else {
      $args->date = '';
      $args->time = '';
    }

    return $this->parsearg($this->templates['calendar'], $args);
  }

  public function getdaterange($from, $to) {
    $from = datefilter::timestamp($from);
    $to = datefilter::timestamp($to);

    $args = new targs();
    $args->from = $from ? date(datefilter::$format, $from) : '';
    $args->to = $to ? date(datefilter::$format, $to) : '';
    $args->format = datefilter::$format;

    return $this->parsearg($this->templates['daterange'], $args);
  }

  public function getcats(array $items) {
    $result = $this->parse($this->templates['posteditor.categories.head']);
tcategories::i()->loadall();
    $result.= $this->getsubcats(0, $items);
    return $result;
  }

  protected function getsubcats($parent, array $items, $exclude = false) {
    $result = '';
    $args = new targs();
    $tml = $this->templates['posteditor.categories.item'];
    $categories = tcategories::i();
    foreach ($categories->items as $id => $item) {
      if (($parent == $item['parent']) &&
      !($exclude && in_array($id, $exclude))) {
      $args->add($item);
      $args->checked = in_array($item['id'], $items);
      $args->subcount = '';
      $args->subitems = $this->getsubcats($id, $items, $exclude);
      $result.= $this->parsearg($tml, $args);
    }
}

    if ($result) {
$result = str_replace('$item', $result, $this->templates['posteditor.categories']);
}

return $result;
  }

  public function processcategories() {
    $result = tadminhtml::check2array('category-');
array_clean($result);
    array_delete_value($result, 0);
return $result;
  }

  public function getfilelist(array $list) {
    $args = new targs();
$args->fileperm = '';

if (is_callable($this->onfileperm)) {
        call_user_func_array($this->onfileperm, array($args));
} else if (litepublisher::$options->show_file_perm) {
$args->fileperm = tadminperms::getcombo(0, 'idperm_upload');
}

    $files = tfiles::i();
    $where = litepublisher::$options->ingroup('editor') ? '' : ' and author = ' . litepublisher::$options->user;

    $db = $files->db;
    //total count files
    $args->count = (int)$db->getcount(" parent = 0 $where");
    //already loaded files
    $args->items = '{}';
    // attrib for hidden input
    $args->files = '';

    if (count($list)) {
      $items = implode(',', $list);
      $args->files = $items;
      $args->items = tojson($db->res2items($db->query("select * from $files->thistable where id in ($items) or parent in ($items)")));
    }

return $this->parsearg($this->templates['posteditor.filelist'], $args);
  }

} //class