<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;
use litepubl\core\litepubl;
use litepubl\theme\Schema;
use litepubl\theme\Filter;

class StaticPages extends \litepubl\core\Items implements \litepubl\theme\ControlerInterface
{
    private $id;

    protected function create() {
        parent::create();
        $this->basename = 'staticpages';
    }

    public function request($arg) {
        $this->id = (int)$arg;
    }

    public function getval($name) {
        return $this->items[$this->id][$name];
    }

    public function gettitle() {
        return $this->getval('title');
    }

    public function gethead() {
    }

    public function getkeywords() {
        return $this->getval('keywords');
    }

    public function getdescription() {
        return $this->getval('description');
    }

    public function getIdSchema() {
        return $this->getval('idschema');
    }

    public function setIdSchema($id) {
        if ($id != $this->data['idschema']) {
            $this->items[$this->id]['idschema'] = $id;
            $this->save();
        }
    }

    public function getschema() {
        return Schema::getSchema($this);
    }

    public function getcont() {
        $theme = $this->getSchema()->theme;
        return $theme->simple($this->getval('filtered'));
    }

    public function add($title, $description, $keywords, $content) {
        $filter = Filter::i();
        $title = Filter::escape($title);
        $linkgen = tlinkgenerator::i();
        $url = $linkgen->createurl($title, 'menu', true);
        $this->items[++$this->autoid] = array(
            'idurl' => litepubl::$router->add($url, get_class($this) , $this->autoid) ,
            'url' => $url,
            'title' => $title,
            'filtered' => $filter->filter($content) ,
            'rawcontent' => $content,
            'description' => Filter::escape($description) ,
            'keywords' => Filter::escape($keywords) ,
            'idschema' => 1
        );
        $this->save();
        return $this->autoid;
    }

    public function edit($id, $title, $description, $keywords, $content) {
        if (!$this->itemexists($id)) return false;
        $filter = Filter::i();
        $item = $this->items[$id];
        $this->items[$id] = array(
            'idurl' => $item['idurl'],
            'url' => $item['url'],
            'title' => $title,
            'filtered' => $filter->filter($content) ,
            'rawcontent' => $content,
            'description' => Filter::escape($description) ,
            'keywords' => Filter::escape($keywords) ,
            'idschema' => $item['idschema']
        );
        $this->save();
        litepubl::$router->clearcache();
    }

    public function delete($id) {
        litepubl::$router->deleteitem($this->items[$id]['idurl']);
        parent::delete($id);
    }

}