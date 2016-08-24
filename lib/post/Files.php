<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\post;

use litepubl\core\Event;

/**
 * Manage uploaded files
 *
 * @property-read FilesItems $itemsPosts
 * @property-write callable $changed
 * @property-write callable $edited
 * @method         array changed(array $params)
 * @method         array edited(array $params)
 */

class Files extends \litepubl\core\Items
{

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'files';
        $this->table = 'files';
        $this->addEvents('changed', 'edited');
    }

    public function getItemsPosts(): FilesItems
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

    public function getUrl(int $id): string
    {
        $item = $this->getItem($id);
        return $this->getApp()->site->files . '/files/' . $item['filename'];
    }

    public function getLink(int $id): string
    {
        $item = $this->getItem($id);
        return sprintf('<a href="%1$s/files/%2$s" title="%3$s">%4$s</a>', $this->getApp()->site->files, $item['filename'], $item['title'], $item['description']);
    }

    public function getHash(string $filename): string
    {
        return trim(base64_encode(md5_file($filename, true)), '=');
    }

    public function addItem(array $item): int
    {
        $realfile = $this->getApp()->paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
        $item['author'] = $this->getApp()->options->user;
        $item['posted'] = Str::sqlDate();
        $item['hash'] = $this->gethash($realfile);
        $item['size'] = filesize($realfile);

        //fix empty props
        foreach (['mime', 'title', 'description', 'keywords'] as $prop) {
            if (!isset($item[$prop])) {
                $item[$prop] = '';
            }
        }

        return $this->insert($item);
    }

    public function insert(array $item): int
    {
        $item = $this->escape($item);
        $id = $this->db->add($item);
        $this->items[$id] = $item;
        $this->changed([]);
        $this->added(['id' => $id]);
        return $id;
    }

    public function escape(array $item): array
    {
        foreach (['title', 'description', 'keywords'] as $name) {
            $item[$name] = Filter::escape(Filter::unescape($item[$name]));
        }
        return $item;
    }

    public function edit(int $id, string $title, string $description, string $keywords)
    {
        $item = $this->getItem($id);
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
        $this->itemsPosts->deleteItem($id);
        $this->itemsPosts->updatePosts($list, 'files');

        $item = $this->getItem($id);
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

    public function setContent(int $id, string $content): bool
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
                $item['id'] = $id;
                $this->db->updateassoc($item);
        }

return true;
    }

    public function exists(string $filename): bool
    {
        return $this->indexOf('filename', $filename);
    }

    public function postEdited(Event $event)
    {
        $post = Post::i($event->id);
        $this->itemsPosts->setItems($post->id, $post->files);
    }

}
