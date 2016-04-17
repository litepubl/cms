<?php

namespace litepubl\admin\users;

class Password extends Perm
{

    public function getform(targs $args) {
        $args->password = '';
        return '[password=password]';
    }

    public function processform() {
        $this->perm->password = $_POST['password'];
        parent::processform();
    }

} //class
