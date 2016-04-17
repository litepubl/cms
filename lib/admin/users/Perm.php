<?php

namespace litepubl\admin\users;

class Perm extends \litepubl\admin\Simple
{
    public $perm;

    public function getcontent() {
        $this->args->add($this->perm->data);
$this->lang->section = 'adminperm';
        $this->args->formtitle = $this->lang->editperm;
        $form = '[text=name] [hidden=id]';
        $form.= $this->getForm();
        return $this->admin->form($form, $this->args);
    }

    public function getForm() {
        return '';
    }

    public function processform() {
        $name = trim($_POST['name']);
        if ($name != '') {
$this->perm->name = $name;
}

        $this->perm->save();
    }

}