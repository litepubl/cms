<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\view;

use litepubl\core\Context;

trait EmptyViewTrait
{

    protected function createData()
    {
        parent::createData();
        $this->data['idschema'] = 1;
    }

    public function request(Context $context)
    {
    }

    public function getHead(): string
    {
        return '';
    }

    public function getKeywords(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getIdSchema(): int
    {
        return $this->data['idschema'];
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->IdSchema) {
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
