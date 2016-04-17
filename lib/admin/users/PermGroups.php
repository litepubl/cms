<?php

namespace litepubl\admin\users;

class PermGroups extends Perm
{

    public function getform(targs $args) {
        $result = '[checkbox=author]
    <h4>$lang.groups</h4>';
        $args->author = $this->perm->author;
        $result.= tadmingroups::getgroups($this->perm->groups);
        return $result;
    }

    public function processform() {
        $this->perm->author = isset($_POST['author']);
        $this->perm->groups = array_unique(tadminhtml::check2array('idgroup-'));
        parent::processform();
    }

}