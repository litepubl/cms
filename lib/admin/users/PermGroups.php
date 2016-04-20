<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\users;
use litepubl\admin\GetPerm;

class PermGroups extends Perm
{

    public function getForm() {
        $this->args->author = $this->perm->author;
        $result = '[checkbox=author]';
$result .= $this->admin->h($this->lang->groups);
        $result.= GetPerm::groups($this->perm->groups);
        return $result;
    }

    public function processForm() {
        $this->perm->author = isset($_POST['author']);
        $this->perm->groups = array_unique($this->admin->check2array('idgroup-'));
        parent::processForm();
    }

}