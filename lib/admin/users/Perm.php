<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\users;

class Perm extends \litepubl\admin\Panel
{
    public $perm;

    public function getContent(): string
    {
        $this->args->add($this->perm->data);
        $this->lang->section = 'adminperm';
        $this->args->formtitle = $this->lang->editperm;
        $form = '[text=name] [hidden=id]';
        $form.= $this->getForm();
        return $this->admin->form($form, $this->args);
    }

    public function getForm()
    {
        return '';
    }

    public function processForm()
    {
        $name = trim($_POST['name']);
        if ($name != '') {
            $this->perm->name = $name;
        }

        $this->perm->save();
    }
}
