<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\view;

use litepubl\core\Context;

traitEmptyViewTrait {

    protected function createData()
    {
        parent::createData();
        $this->data['idschema'] = 1;
    }

    public function request(Context $context)
    {
    }

    public function getHead()
    {
    }

    public function getKeywords()
    {
    }

    public function getDescription()
    {
    }

    public function getIdSchema()
    {
        return $this->data['idschema'];
    }

    public function setIdSchema($id)
    {
        if ($id != $this->IdSchema) {
            $this->data['idschema'] = $id;
            $this->save();
        }
    }

    public function getSchema()
    {
        return Schema::getSchema($this);
    }

    public function getView()
    {
        return $this;
    }

}

