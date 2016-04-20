<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\pages;
use litepubl\view\Schema;
use litepubl\view\Filter;
use litepubl\utils\LinkGenerator;

class StaticPages extends \litepubl\core\Items implements \litepubl\view\ViewInterface
{
    private $id;

    protected function create() {
        parent::create();
        $this->basename = 'staticpages';
    }

    public function request($arg) {
        $this->id = (int)$arg;
    }

    public function getVal($name) {
        return $this->items[$this->id][$name];
    }

    public function getTitle() {
        return $this->getval('title');
    }

    public function getHead() {
    }

    public function getKeywords() {
        return $this->getval('keywords');
    }

    public function getDescription() {
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

    public function getSchema() {
        return Schema::getSchema($this);
    }

    public function getCont() {
        $theme = $this->getSchema()->theme;
        return $theme->simple($this->getval('filtered'));
    }

    public function add($title, $description, $keywords, $content) {
        $filter = Filter::i();
        $title = Filter::escape($title);
        $linkgen = LinkGenerator::i();
        $url = $linkgen->createurl($title, 'menu', true);
        $this->items[++$this->autoid] = array(
            'idurl' =>  $this->getApp()->router->add($url, get_class($this) , $this->autoid) ,
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
        if (!$this->itemexists($id)) {
 return false;
}


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
         $this->getApp()->router->clearcache();
    }

    public function delete($id) {
         $this->getApp()->router->deleteitem($this->items[$id]['idurl']);
        parent::delete($id);
    }

}