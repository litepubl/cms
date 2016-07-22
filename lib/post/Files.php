<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\post;

use litepubl\core\Str;
use litepubl\core\Event;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Theme;
use litepubl\view\Vars;

/**
 * Manage uploaded files
 *
 * @property-write callable $changed
 * @property-write callable $edited
 * @property-write callable $onGetFilelist
 * @property-write callable $onlist
 * @method         array changed(array $params)
 * @method         array edited(array $params)
 * @method         array onGetFilelist(array $params)
 * @method         array onlist(array $params)
 */

class Files extends \litepubl\core\Items
{
    public $cachetml;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'files';
        $this->table = 'files';
        $this->addEvents('changed', 'edited', 'ongetfilelist', 'onlist');
        $this->cachetml = array();
    }

    public function getItemsposts()
    {
        return FilesItems::i();
    }

    public function preload(array $items)
    {
        $items = array_diff($items, array_keys($this->items));
        if (count($items)) {
            $this->select(sprintf('(id in (%1$s)) or (parent in (%1$s))', implode(',', $items)), '');
        }
    }

    public function getUrl($id)
    {
        $item = $this->getitem($id);
        return $this->getApp()->site->files . '/files/' . $item['filename'];
    }

    public function getLink($id)
    {
        $item = $this->getitem($id);
        $icon = '';
        if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
            $icon = $this->geticon($item['icon']);
        }
        return sprintf('<a href="%1$s/files/%2$s" title="%3$s">%4$s</a>', $this->getApp()->site->files, $item['filename'], $item['title'], $icon . $item['description']);
    }

    public function getIcon($id)
    {
        return sprintf('<img src="%s" alt="icon" />', $this->geturl($id));
    }

    public function getHash($filename)
    {
        return trim(base64_encode(md5_file($filename, true)), '=');
    }

    public function additem(array $item)
    {
        $realfile = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
        $item['author'] = $this->getApp()->options->user;
        $item['posted'] = Str::sqlDate();
        $item['hash'] = $this->gethash($realfile);
        $item['size'] = filesize($realfile);

        //fix empty props
        foreach (array(
            'mime',
            'title',
            'description',
            'keywords'
        ) as $prop) {
            if (!isset($item[$prop])) {
                $item[$prop] = '';
            }
        }
        return $this->insert($item);
    }

    public function insert(array $item)
    {
        $item = $this->escape($item);
        $id = $this->db->add($item);
        $this->items[$id] = $item;
        $this->changed([]);
        $this->added(['id' => $id]);
        return $id;
    }

    public function escape(array $item)
    {
        foreach (array(
            'title',
            'description',
            'keywords'
        ) as $name) {
            $item[$name] = Filter::escape(Filter::unescape($item[$name]));
        }
        return $item;
    }

    public function edit($id, $title, $description, $keywords)
    {
        $item = $this->getitem($id);
        if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) {
            return false;
        }

        $item['title'] = $title;
        $item['description'] = $description;
        $item['keywords'] = $keywords;
        $item = $this->escape($item);
        $this->items[$id] = $item;
        $this->db->updateassoc($item);
        $this->changed([]);
        $this->edited(['id' => $id]);
        return true;
    }

    public function delete($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $list = $this->itemsposts->getposts($id);
        $this->itemsposts->deleteitem($id);
        $this->itemsposts->updateposts($list, 'files');

        $item = $this->getitem($id);
        if ($item['idperm'] == 0) {
            @unlink($this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
        } else {
            @unlink($this->getApp()->paths->files . 'private' . DIRECTORY_SEPARATOR . basename($item['filename']));
            $this->getApp()->router->delete('/files/' . $item['filename']);
        }

        parent::delete($id);

        if ((int)$item['preview']) {
            $this->delete($item['preview']);
        }

        if ((int)$item['midle']) {
            $this->delete($item['midle']);
        }

        $this->getdb('imghashes')->delete("id = $id");
        $this->changed([]);
        $this->deleted(['id' => $id]);
        return true;
    }

    public function setContent($id, $content)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        $item = $this->getitem($id);
        $realfile = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
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

    public function exists($filename)
    {
        return $this->indexof('filename', $filename);
    }

    public function getFilelist(array $list, $excerpt)
    {
        $r = $this->onGetFilelist(['list' => $list, 'excerpt' => $excerpt, 'result' => false]);
        if ($r['result']) {
            return $r['result'];
        }

        if (count($list) == 0) {
            return '';
        }

        return $this->getlist($list, $excerpt ? $this->gettml('content.excerpts.excerpt.filelist') : $this->gettml('content.post.filelist'));
    }

    public function getTml($basekey)
    {
        if (isset($this->cachetml[$basekey])) {
            return $this->cachetml[$basekey];
        }

        $theme = Theme::i();
        $result = array(
            'container' => $theme->templates[$basekey],
        );

        $key = $basekey . '.';
        foreach ($theme->templates as $k => $v) {
            if (Str::begin($k, $key)) {
                $result[substr($k, strlen($key)) ] = $v;
            }
        }

        $this->cachetml[$basekey] = $result;
        return $result;
    }

    public function getList(array $list, array $tml)
    {
        if (!count($list)) {
            return '';
        }

        $this->onlist(['list' => $list]);
        $result = '';
        $this->preload($list);

        //sort by media type
        $items = array();
        foreach ($list as $id) {
            if (!isset($this->items[$id])) {
                continue;
            }

            $item = $this->items[$id];
            $type = $item['media'];
            if (isset($tml[$type])) {
                $items[$type][] = $id;
            } else {
                $items['file'][] = $id;
            }
        }

        $theme = Theme::i();
        $args = new Args();
        $args->count = count($list);

        $url = $this->getApp()->site->files . '/files/';

        $preview = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        Theme::$vars['preview'] = $preview;
        $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        Theme::$vars['midle'] = $midle;

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
                $preview->exchangeArray([]);

                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($this->getitem($idmidle));
                    $midle->link = $url . $midle->filename;
                    $midle->json = $this->getjson($idmidle);
                } else {
                    $midle->exchangeArray([]);
                    $midle->link = '';
                    $midle->json = '';
                }

                if ((int)$item['preview']) {
                    $preview->exchangeArray($this->getitem($item['preview']));
                } elseif ($type == 'image') {
                    $preview->exchangeArray($item);
                    $preview->id = $id;
                } elseif ($type == 'video') {
                    $args->preview = $theme->parseArg($tml['videos.fallback'], $args);
                    $preview->exchangeArray([]);
                }

                if ($preview->count()) {
                    $preview->link = $url . $preview->filename;
                    $args->preview = $theme->parseArg($tml['preview'], $args);
                }

                $args->json = $this->getjson($id);
                $sublist.= $theme->parseArg($tml[$type], $args);
            }

            $args->__set($type, $sublist);
            $result.= $theme->parseArg($tml[$type . 's'], $args);
        }

        unset(Theme::$vars['preview'], $preview, Theme::$vars['midle'], $midle);
        $args->files = $result;
        return $theme->parseArg($tml['container'], $args);
    }

    public function postEdited(Event $event)
    {
        $post = Post::i($event->id);
        $this->itemsposts->setitems($event->id, $post->files);
    }

    public function getFirstimage(array $items)
    {
        foreach ($items as $id) {
            $item = $this->getitem($id);
            if (('image' == $item['media']) && ($idpreview = (int)$item['preview'])) {
                $baseurl = $this->getApp()->site->files . '/files/';
                $args = new Args();
                $args->add($item);
                $args->link = $baseurl . $item['filename'];
                $args->json = $this->getjson($id);

                $preview = new \ArrayObject($this->getitem($idpreview), \ArrayObject::ARRAY_AS_PROPS);
                $preview->link = $baseurl . $preview->filename;

                $midle = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
                if ($idmidle = (int)$item['midle']) {
                    $midle->exchangeArray($this->getitem($idmidle));
                    $midle->link = $baseurl . $midle->filename;
                    $midle->json = $this->getjson($idmidle);
                } else {
                    $midle->json = '';
                }

                $vars = new Vars();
                $vars->preview = $preview;
                $vars->midle = $midle;
                $theme = Theme::i();
                return $theme->parseArg($theme->templates['content.excerpts.excerpt.firstimage'], $args);
            }
        }

        return '';
    }

    public function getJson($id)
    {
        $item = $this->getitem($id);
        return Str::jsonAttr(
            array(
            'id' => $id,
            'link' => $this->getApp()->site->files . '/files/' . $item['filename'],
            'width' => $item['width'],
            'height' => $item['height'],
            'size' => $item['size'],
            'midle' => $item['midle'],
            'preview' => $item['preview'],
            )
        );
    }
}
