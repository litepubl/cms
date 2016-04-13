<?php

namespace litepubl\tag;
use litepubl\view\Filter;

class Content extends \litepubl\core\Data
 {
    private $owner;
    private $items;

    public function __construct(Common $owner) {
        parent::__construct();
        $this->owner = $owner;
        $this->items = array();
    }

    public function getitem($id) {
        if (isset($this->items[$id])) {
return $this->items[$id];
}

        $item = array(
            'description' => '',
            'keywords' => '',
            'head' => '',
            'content' => '',
            'rawcontent' => ''
        );

        if ($r = $this->db->getitem($id)) {
$item = $r;
}

        $this->items[$id] = $item;
        return $item;
    }

    public function setitem($id, $item) {
        if (isset($this->items[$id]) && ($this->items[$id] == $item)) {
return;
}

        $this->items[$id] = $item;
        $item['id'] = $id;
        $this->db->addupdate($item);
    }

    public function edit($id, $content, $description, $keywords, $head) {
        $item = $this->getitem($id);
        $filter = Filter::i();
        $item = array(
            'content' => $filter->filter($content) ,
            'rawcontent' => $content,
            'description' => $description,
            'keywords' => $keywords,
            'head' => $head
        );
        $this->setitem($id, $item);
    }

    public function delete($id) {
        $this->db->iddelete($id);
    }

    public function getvalue($id, $name) {
        $item = $this->getitem($id);
        return $item[$name];
    }

    public function setvalue($id, $name, $value) {
        $item = $this->getitem($id);
        $item[$name] = $value;
        $this->setitem($id, $item);
    }

    public function getcontent($id) {
        return $this->getvalue($id, 'content');
    }

    public function setcontent($id, $content) {
        $item = $this->getitem($id);
        $filter = Filter::i();
        $item['rawcontent'] = $content;
        $item['content'] = $filter->filterpages($content);
        $item['description'] = tcontentfilter::getexcerpt($content, 80);
        $this->setitem($id, $item);
    }

    public function getdescription($id) {
        return $this->getvalue($id, 'description');
    }

    public function getkeywords($id) {
        return $this->getvalue($id, 'keywords');
    }

    public function gethead($id) {
        return $this->getvalue($id, 'head');
    }

}