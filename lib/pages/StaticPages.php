<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\pages;

use litepubl\core\Context;
use litepubl\utils\LinkGenerator;
use litepubl\view\Filter;
use litepubl\view\Schema;

class StaticPages extends \litepubl\core\Items implements \litepubl\view\ViewInterface
{
    private $id;

    protected function create()
    {
        parent::create();
        $this->basename = 'staticpages';
    }

    public function request(Context $context)
    {
        $this->id = (int)$context->itemRoute['arg'];
    }

    public function getVal($name)
    {
        return $this->items[$this->id][$name];
    }

    public function getTitle(): string
    {
        return $this->getval('title');
    }

    public function getHead(): string
    {
        return '';
    }

    public function getKeywords(): string
    {
        return $this->getval('keywords');
    }

    public function getDescription(): string
    {
        return $this->getval('description');
    }

    public function getIdSchema(): int
    {
        return $this->getval('idschema');
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->data['idschema']) {
            $this->items[$this->id]['idschema'] = $id;
            $this->save();
        }
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    public function getCont(): string
    {
        $theme = $this->getSchema()->theme;
        return $theme->simple($this->getval('filtered'));
    }

    public function add(string $title, string $description, string $keywords, string $content): int
    {
        $filter = Filter::i();
        $title = Filter::escape($title);
        $linkgen = LinkGenerator::i();
        $url = $linkgen->createurl($title, 'menu', true);
        $this->items[++$this->autoid] = array(
            'idurl' => $this->getApp()->router->add($url, get_class($this), $this->autoid) ,
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

    public function edit(int $id, string $title, string $description, string $keywords, string $content)
    {
        if (!$this->itemExists($id)) {
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
        $this->getApp()->cache->clear();
    }

    public function delete($id)
    {
        $this->getApp()->router->deleteitem($this->items[$id]['idurl']);
        parent::delete($id);
    }
}
