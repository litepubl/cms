<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\admin;

use litepubl\view\Admin;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Form
{
    public $args;
    public $title;
    public $before;
    public $body;
    //items deprecated
    public $items;
    public $submit;
    public $inline;

    //attribs for <form>
    public $action;
    public $method;
    public $enctype;
    public $id;
    public $class;
    public $target;

    public function __construct($args = null)
    {
        $this->args = $args;
        $this->title = '';
        $this->before = '';
        $this->body = '';
        $this->items = & $this->body;
        $this->submit = 'update';
        $this->inline = false;

        $this->action = '';
        $this->method = 'post';
        $this->enctype = '';
        $this->id = '';
        $this->class = '';
        $this->target = '';
    }

    public function line(string $content): string
    {
        return str_replace('$content', $content, $this->getAdminTheme()->templates['inline']);
    }

    public function getAdmintheme(): Admin
    {
        return Admin::i();
    }

    public function __set(string $k, $v)
    {
        switch ($k) {
        case 'upload':
            if ($v) {
                $this->enctype = 'multipart/form-data';
                $this->submit = 'upload';
            } else {
                $this->enctype = '';
                $this->submit = 'update';
            }
            break;
        }
    }

    public function centerGroup(string $buttons): string
    {
        return str_replace('$buttons', $buttons, $this->getAdminTheme()->templates['centergroup']);
    }

    public function hidden(string $name, string $value): string
    {
        return sprintf('<input type="hidden" name="%s" value="%s" />', $name, $value);
    }

    public function getDelete(string $table): string
    {
        $this->body = $table;
        $this->body.= $this->hidden('action', 'delete');
        $this->submit = 'delete';

        return $this->get();
    }

    public function __tostring()
    {
        return $this->get();
    }

    public function getTml(): string
    {
        $admin = $this->getadmintheme();
        $title = $this->title ? str_replace('$title', $this->title, $admin->templates['form.title']) : '';

        $attr = "action=\"$this->action\"";
        foreach (array(
            'method',
            'enctype',
            'target',
            'id',
            'class'
        ) as $k) {
            if ($v = $this->$k) {
                $attr.= sprintf(' %s="%s"', $k, $v);
            }
        }

        $theme = Theme::i();
        $lang = Lang::i();
        $body = $this->body;

        if ($this->inline) {
            if ($this->submit) {
                $body.= $theme->getInput('button', $this->submit, '', $lang->__get($this->submit));
            }

            $body = $this->line($body);
        } else {
            if ($this->submit) {
                $body.= $theme->getInput('submit', $this->submit, '', $lang->__get($this->submit));
            }
        }

        return strtr(
            $admin->templates['form'], array(
            '$title' => $title,
            '$before' => $this->before,
            '$attr' => $attr,
            '$body' => $body,
            )
        );
    }

    public function get(): string
    {
        return $this->getAdminTheme()->parseArg($this->getTml(), $this->args);
    }

    public function getButtons(): string
    {
        $result = '';
        $theme = Theme::i();
        $lang = Lang::i();

        $a = func_get_args();
        foreach ($a as $name) {
            $result.= strtr(
                $theme->templates['content.admin.button'], array(
                '$lang.$name' => $lang->__get($name) ,
                '$name' => $name,
                )
            );
        }

        return $result;
    }
}
