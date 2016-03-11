<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tfiles extends titems {
  public $itemsposts;
  public $cachetml;

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->basename = 'files';
    $this->table = 'files';
    $this->addevents('changed', 'edited', 'ongetfilelist', 'onlist');
    $this->itemsposts = tfileitems::i();
    $this->cachetml = array();
  }

  public function preload(array $items) {
    $items = array_diff($items, array_keys($this->items));
    if (count($items)) {
      $this->select(sprintf('(id in (%1$s)) or (parent in (%1$s))', implode(',', $items)) , '');
    }
  }

  public function geturl($id) {
    $item = $this->getitem($id);
    return litepublisher::$site->files . '/files/' . $item['filename'];
  }

  public function getlink($id) {
    $item = $this->getitem($id);
    $icon = '';
    if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
      $icon = $this->geticon($item['icon']);
    }
    return sprintf('<a href="%1$s/files/%2$s" title="%3$s">%4$s</a>', litepublisher::$site->files, $item['filename'], $item['title'], $icon . $item['description']);
  }

  public function geticon($id) {
    return sprintf('<img src="%s" alt="icon" />', $this->geturl($id));
  }

  public function gethash($filename) {
    return trim(base64_encode(md5_file($filename, true)) , '=');
  }

  public function additem(array $item) {
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    $item['author'] = litepublisher::$options->user;
    $item['posted'] = sqldate();
    $item['hash'] = $this->gethash($realfile);
    $item['size'] = filesize($realfile);

    //fix empty props
    foreach (array(
      'mime',
      'title',
      'description',
      'keywords'
    ) as $prop) {
      if (!isset($item[$prop])) $item[$prop] = '';
    }
    return $this->insert($item);
  }

  public function insert(array $item) {
    $item = $this->escape($item);
    $id = $this->db->add($item);
    $this->items[$id] = $item;
    $this->changed();
    $this->added($id);
    return $id;
  }

  public function escape(array $item) {
    foreach (array(
      'title',
      'description',
      'keywords'
    ) as $name) {
      $item[$name] = tcontentfilter::escape(tcontentfilter::unescape($item[$name]));
    }
    return $item;
  }

  public function edit($id, $title, $description, $keywords) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) return false;

    $item['title'] = $title;
    $item['description'] = $description;
    $item['keywords'] = $keywords;
    $item = $this->escape($item);
    $this->items[$id] = $item;
    $this->db->updateassoc($item);
    $this->changed();
    $this->edited($id);
    return true;
  }

  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    $this->itemsposts->updateposts($list, 'files');

    $item = $this->getitem($id);
    if ($item['idperm'] == 0) {
      @unlink(litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
    } else {
      @unlink(litepublisher::$paths->files . 'private' . DIRECTORY_SEPARATOR . basename($item['filename']));
      litepublisher::$urlmap->delete('/files/' . $item['filename']);
    }

    parent::delete($id);

    if ((int)$item['preview']) {
      $this->delete($item['preview']);
    }

    if ((int)$item['midle']) {
      $this->delete($item['midle']);
    }

    $this->getdb('imghashes')->delete("id = $id");
    $this->changed();
    return true;
  }

  public function setcontent($id, $content) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    if (file_put_contents($realfile, $content)) {
      $item['hash'] = $this->gethash($realfile);
      $item['size'] = filesize($realfile);
      $this->items[$id] = $item;
      if ($this->dbversion) {
        $item['id'] = $id;
        $this->db->updateassoc($item);
      } else {
        $this->save();
      }
    }
  }

  public function exists($filename) {
    return $this->indexof('filename', $filename);
  }

  public function getfilelist(array $list, $excerpt) {
    if ($result = $this->ongetfilelist($list, $excerpt)) return $result;
    if (count($list) == 0) return '';

    return $this->getlist($list, $excerpt ? $this->gettml('content.excerpts.excerpt.filelist') : $this->gettml('content.post.filelist'));
  }

  public function gettml($basekey) {
    if (isset($this->cachetml[$basekey])) return $this->cachetml[$basekey];

    $theme = ttheme::i();
    $result = array(
      'container' => $theme->templates[$basekey],
    );

    $key = $basekey . '.';
    foreach ($theme->templates as $k => $v) {
      if (strbegin($k, $key)) {
        $result[substr($k, strlen($key)) ] = $v;
      }
    }

    $this->cachetml[$basekey] = $result;
    return $result;
  }

  public function getlist(array $list, array $tml) {
    if (!count($list)) {
      return '';
    }

    $this->onlist($list);
    $result = '';
    $this->preload($list);

    //sort by media type
    $items = array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;

      $item = $this->items[$id];
      $type = $item['media'];
      if (isset($tml[$type])) {
        $items[$type][] = $id;
      } else {
        $items['file'][] = $id;
      }
    }

    $theme = ttheme::i();
    $args = new targs();
    $args->count = count($list);

    $url = litepublisher::$site->files . '/files/';

    $preview = ttheme::$vars['preview'] = new tarray2prop();
    $midle = ttheme::$vars['midle'] = new tarray2prop();

    $index = 0;

    foreach ($items as $type => $subitems) {
      $args->subcount = count($subitems);
      $sublist = '';
      foreach ($subitems as $typeindex => $id) {
        $item = $this->items[$id];
        $args->add($item);
        $args->link = $url . $item['filename'];
        $args->id = $id;
        $args->typeindex = $typeindex;
        $args->index = $index++;
        $args->preview = '';
        $preview->array = array();

        if ($idmidle = (int)$item['midle']) {
          $midle->array = $this->getitem($idmidle);
          $midle->link = $url . $midle->filename;
          $midle->json = $this->getjson($idmidle);
        } else {
          $midle->array = array();
          $midle->link = '';
          $midle->json = '';
        }

        if ((int)$item['preview']) {
          $preview->array = $this->getitem($item['preview']);
        } elseif ($type == 'image') {
          $preview->array = $item;
          $preview->id = $id;
        } elseif ($type == 'video') {
          $args->preview = $theme->parsearg($tml['videos.fallback'], $args);
          $preview->array = array();
        }

        if (count($preview->array)) {
          $preview->link = $url . $preview->filename;
          $args->preview = $theme->parsearg($tml['preview'], $args);
        }

        $args->json = $this->getjson($id);
        $sublist.= $theme->parsearg($tml[$type], $args);
      }

      $args->__set($type, $sublist);
      $result.= $theme->parsearg($tml[$type . 's'], $args);
    }

    unset(ttheme::$vars['preview'], $preview, ttheme::$vars['midle'], $midle);
    $args->files = $result;
    return $theme->parsearg($tml['container'], $args);
  }

  public function postedited($idpost) {
    $post = tpost::i($idpost);
    $this->itemsposts->setitems($idpost, $post->files);
  }

public function getfirstimage(array $items) {
    foreach ($items as $id) {
      $item = $this->getitem($id);
      if ('image' == $item['media']) {
$baseurl = litepublisher::$site->files . '/files/';
        $args = new targs();
        $args->add($item);
        $args->link = $baseurl . $item['filename'];
$args->json = $this->getjson($id);

$preview = new tarray2prop();
        $preview->array = $this->getitem($item['preview']);
        $preview->link = $baseurl . $preview->filename;

$midle = new tarray2prop();
        if ($idmidle = (int)$item['midle']) {
          $midle->array = $this->getitem($idmidle);
          $midle->link = $baseurl . $midle->filename;
          $midle->json = $this->getjson($idmidle);
        } else {
          $midle->array = array();
          $midle->json = '';
        }

$vars = new themevars();
$vars->preview = $preview;
$vars->midle = $midle;
        $theme = ttheme::i();
return $theme->parsearg($theme->templates['content.excerpts.excerpt.firstimage'], $args);
}
      }

return '';
}

public function getjson($id) {
$item = $this->getitem($id);
return jsonattr(array(
            'id' => $id,
            'link' => litepublisher::$site->files . '/files/' . $item['filename'],
            'width' => $item['width'],
            'height' => $item['height'],
            'size' => $item['size'],
'midle' => $item['midle'],
'preview' => $item['preview'],
          ));
}

} //class