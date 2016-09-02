<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\tag;

use litepubl\view\Filter;

class Content extends \litepubl\core\Data
{
    private $owner;
    private $items;

    public function __construct(Common $owner)
    {
        parent::__construct();
        $this->owner = $owner;
        $this->items = [];
    }

    public function getItem($id)
    {
        if (isset($this->items[$id])) {
            return $this->items[$id];
        }

        $item = [
            'description' => '',
            'keywords' => '',
            'head' => '',
            'content' => '',
            'rawcontent' => ''
        ];

        if ($r = $this->db->getitem($id)) {
            $item = $r;
        }

        $this->items[$id] = $item;
        return $item;
    }

    public function setItem($id, $item)
    {
        if (isset($this->items[$id]) && ($this->items[$id] == $item)) {
            return;
        }

        $this->items[$id] = $item;
        $item['id'] = $id;
        $this->db->addupdate($item);
    }

    public function edit($id, $content, $description, $keywords, $head)
    {
        $item = $this->getitem($id);
        $filter = Filter::i();
        $item = [
            'content' => $filter->filter($content) ,
            'rawcontent' => $content,
            'description' => $description,
            'keywords' => $keywords,
            'head' => $head
        ];
        $this->setitem($id, $item);
    }

    public function delete($id)
    {
        $this->db->iddelete($id);
    }

    public function getValue($id, $name)
    {
        $item = $this->getitem($id);
        return $item[$name];
    }

    public function setValue($id, $name, $value)
    {
        $item = $this->getitem($id);
        $item[$name] = $value;
        $this->setitem($id, $item);
    }

    public function getContent($id)
    {
        return $this->getvalue($id, 'content');
    }

    public function setContent($id, $content)
    {
        $item = $this->getitem($id);
        $filter = Filter::i();
        $item['rawcontent'] = $content;
        $item['content'] = $filter->filterpages($content);
        $item['description'] = Filter::getexcerpt($content, 80);
        $this->setitem($id, $item);
    }

    public function getDescription($id)
    {
        return $this->getvalue($id, 'description');
    }

    public function getKeywords($id)
    {
        return $this->getvalue($id, 'keywords');
    }

    public function getHead($id)
    {
        return $this->getvalue($id, 'head');
    }
}
