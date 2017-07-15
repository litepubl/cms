<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

class Pool extends Data
{
    protected $perpool;
    protected $pool;
    protected $modified;
    protected $ongetitem;

    protected function create()
    {
        parent::create();
        $this->basename = 'poolitems';
        $this->perpool = 20;
        $this->pool = [];
        $this->modified = [];
    }

    public function getItem($id)
    {
        if (isset($this->ongetitem)) {
            return call_user_func_array(
                $this->ongetitem,
                [
                $id
                ]
            );
        }

        $this->error('Call abstract method getitem in class' . get_class($this));
    }

    public function getFilename($idpool)
    {
        return $this->basename . '.pool.' . $idpool;
    }

    public function loadpool($idpool)
    {
        if ($data = $this->getApp()->cache->get($this->getFilename($idpool))) {
            $this->pool[$idpool] = $data;
        } else {
            $this->pool[$idpool] = [];
        }
    }

    public function savepool($idpool)
    {
        if (!isset($this->modified[$idpool])) {
            $this->getApp()->onClose(
                function (Event $event) use ($idpool) {
                    $this->saveModified($idpool);
                    $event->once = true;
                }
            );
            $this->modified[$idpool] = true;
        }
    }

    public function savemodified($idpool)
    {
        $this->getApp()->cache->set($this->getFilename($idpool), $this->pool[$idpool]);
    }

    public function getIdpool($id)
    {
        $idpool = (int)floor($id / $this->perpool);
        if (!isset($this->pool[$idpool])) {
            $this->loadpool($idpool);
        }

        return $idpool;
    }

    public function get($id)
    {
        $idpool = $this->getidpool($id);
        if (isset($this->pool[$idpool][$id])) {
            return $this->pool[$idpool][$id];
        }
        $result = $this->getitem($id);
        $this->pool[$idpool][$id] = $result;
        $this->savepool($idpool);
        return $result;
    }

    public function set($id, $item)
    {
        $idpool = $this->getidpool($id);
        $this->pool[$idpool][$id] = $item;
        $this->savepool($idpool);
    }
}
