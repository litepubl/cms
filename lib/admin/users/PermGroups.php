<?php

namespace litepubl\admin\users;
use litepubl\admin\GetPerm;

class PermGroups extends Perm
{

    public function getform() {
        $this->args->author = $this->perm->author;
        $result = '[checkbox=author]';
$result .= $this->admin->h($this->lang->groups);
        $result.= GetPerm::groups($this->perm->groups);
        return $result;
    }

    public function processform() {
        $this->perm->author = isset($_POST['author']);
        $this->perm->groups = array_unique($this->admin->check2array('idgroup-'));
        parent::processform();
    }

}