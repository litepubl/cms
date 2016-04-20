<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;

trait ViewTrait
{

    protected function createData() {
        parent::createData();
        $this->data['idschema'] = 1;
        $this->data['keywords'] = '';
        $this->data['description'] = '';
        $this->data['head'] = '';
    }

    public function request($arg) {
}

    public function getHead() {
        return $this->data['head'];
    }

    public function getKeywords() {
        return $this->data['keywords'];
    }

    public function getDescription() {
        return $this->data['description'];
    }

    public function getIdSchema() {
        return $this->data['idschema'];
    }

    public function setIdSchema($id) {
        if ($id != $this->data['idschema']) {
            $this->data['idschema'] = $id;
            $this->save();
        }
    }

    public function getSchema() {
        return Schema::getSchema($this);
    }

}