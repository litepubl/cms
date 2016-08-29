<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\view;

use litepubl\core\Context;

trait ViewTrait
{

    protected function createData()
    {
        parent::createData();
        $this->data['idschema'] = 1;
        $this->data['keywords'] = '';
        $this->data['description'] = '';
        $this->data['head'] = '';
    }

    public function request(Context $context)
    {
    }

    public function getHead(): string
    {
        return $this->data['head'];
    }

    public function getKeywords(): string
    {
        return $this->data['keywords'];
    }

    public function getDescription(): string
    {
        return $this->data['description'];
    }

    public function getTitle(): string
    {
        return isset($this->data['title']) ? $this->data['title'] : '';
    }

    public function getCont(): string
    {
        return '';
    }

    public function getIdSchema(): int
    {
        return $this->data['idschema'];
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->data['idschema']) {
            $this->data['idschema'] = $id;
            $this->save();
        }
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    public function getView(): ViewInterface
    {
        return $this;
    }
}
