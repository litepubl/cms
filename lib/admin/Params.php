<?php

namespace litepubl\admin;

trait Params
{

    public function idget() {
        return (int)$this->getparam('id', 0);
    }

    public function getparam($name, $default) {
        return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
    }

    public function idparam() {
        return (int)$this->getparam('id', 0);
    }

    public function getaction() {
        return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
    }

    public function getConfirmed() {
        return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
    }

}