<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin;
use litepubl\view\Admin;
use litepubl\view\Base;
use litepubl\view\Lang;
use litepubl\post\Post;
use litepubl\view\Args;

class Table
{
    //current item in items
    public $item;
    //id or index of current item
    public $id;
    //template head and body table
    public $head;
    public $body;
    public $footer;
    //targs
    public $args;
    public $data;
    public $admintheme;
    public $callbacks;

    public static function fromitems(array $items, array $struct) {
        $classname = __class__;
        $self = new $classname();
        $self->setStruct($struct);
        return $self->build($items);
    }

    public function __construct() {
        $this->head = '';
        $this->body = '';
        $this->footer = '';
        $this->callbacks = array();
        $this->args = new Args();
        $this->data = array();
    }

    public function setStruct(array $struct) {
        $this->head = '';
        $this->body = '<tr>';

        foreach ($struct as $index => $item) {
            if (!$item || !count($item)) {
 continue;
}



            if (count($item) == 2) {
                $colclass = 'text-left';
            } else {
                $colclass = static ::getcolclass(array_shift($item));
            }

            $this->head.= sprintf('<th class="%s">%s</th>', $colclass, array_shift($item));

            $s = array_shift($item);
            if (is_string($s)) {
                $this->body.= sprintf('<td class="%s">%s</td>', $colclass, $s);
            } else if (is_callable($s)) {
                $name = '$callback' . $index;
                $this->body.= sprintf('<td class="%s">%s</td>', $colclass, $name);

                array_unshift($item, $this);
                $this->callbacks[$name] = array(
                    'callback' => $s,
                    'params' => $item,
                );
            } else {
                throw new Exception('Unknown column ' . var_export($s, true));
            }
        }

        $this->body.= '</tr>';
    }

    public function addCallback($varname, $callback, $param = null) {
        $this->callbacks[$varname] = array(
            'callback' => $callback,
            'params' => array(
                $this,
                $param
            ) ,
        );
    }

    public function addfooter($footer) {
        $this->footer = sprintf('<tfoot><tr>%s</tr></tfoot>', $footer);
    }

    public function td($colclass, $content) {
        return sprintf('<td class="%s">%s</td>', static ::getcolclass($colclass) , $content);
    }

    public function getAdmintheme() {
        if (!$this->admintheme) {
            $this->admintheme = Admin::i();
        }

        return $this->admintheme;
    }

    public function build(array $items) {
        $body = '';

        foreach ($items as $id => $item) {
            $body.= $this->parseitem($id, $item);
        }

        return $this->getadmintheme()->gettable($this->head, $body, $this->footer);
    }

    public function parseitem($id, $item) {
        $args = $this->args;

        if (is_array($item)) {
            $this->item = $item;
            $args->add($item);
            if (!isset($item['id'])) {
                $this->id = $id;
                $args->id = $id;
            }
        } else {
            $this->id = $item;
            $args->id = $item;
        }

        foreach ($this->callbacks as $name => $callback) {
            $args->data[$name] = call_user_func_array($callback['callback'], $callback['params']);
        }

        return $this->getadmintheme()->parseArg($this->body, $args);
    }

    //predefined callbacks
    public function titems_callback(Table $self, titems $owner) {
        $self->item = $owner->getitem($self->id);
        $self->args->add($self->item);
    }

    public function setOwner(titems $owner) {
        $this->addCallback('$tempcallback' . count($this->callbacks) , array(
            $this,
            'titems_callback'
        ) , $owner);
    }

    public function posts_callback(Table $self) {
        $post = Post::i($self->id);
        Base::$vars['post'] = $post->getView();
        $self->args->poststatus = Lang::i()->__get($post->status);
    }

    public function setPosts(array $struct) {
        array_unshift($struct, $this->checkbox('checkbox'));
        $this->setStruct($struct);
        $this->addCallback('$tempcallback' . count($this->callbacks) , array(
            $this,
            'posts_callback'
        ) , false);
    }

    public function props(array $props) {
        $lang = Lang::i();
        $this->setStruct(array(
            array(
                $lang->name,
                '$name'
            ) ,

            array(
                $lang->property,
                '$value'
            )
        ));

        $body = '';
        $args = $this->args;
        $admintheme = $this->getadmintheme();

        foreach ($props as $k => $v) {
            if (($k === false) || ($v === false)) {
 continue;
}



            if (is_array($v)) {
                foreach ($v as $kv => $vv) {
                    if ($k2 = $lang->__get($kv)) $kv = $k2;
                    $args->name = $kv;
                    $args->value = $vv;
                    $body.= $admintheme->parseArg($this->body, $args);
                }
            } else {
                if ($k2 = $lang->__get($k)) {
                    $k = $k2;
                }

                $args->name = $k;
                $args->value = $v;
                $body.= $admintheme->parseArg($this->body, $args);
            }
        }

        return $admintheme->gettable($this->head, $body);
    }

    public function inputs(array $inputs) {
        $lang = Lang::i();
        $this->setStruct(array(
            array(
                $lang->name,
                '<label for="$name-input">$title</label>'
            ) ,

            array(
                $lang->property,
                '$input'
            )
        ));

        $body = '';
        $args = $this->args;
        $admintheme = $this->getadmintheme();

        foreach ($inputs as $name => $type) {
            if (($name === false) || ($type === false)) {
                {
 continue;
}


            }

            switch ($type) {
                case 'combo':
                    $input = '<select name="$name" id="$name-input">$value</select>';
                    break;


                case 'text':
                    $input = '<input type="text" name="$name" id="$name-input" value="$value" />';
                    break;


                default:
                    $this->error('Unknown input type ' . $type);
            }

            $args->name = $name;
            $args->title = $lang->$name;
            $args->value = $args->$name;
            $args->input = $admintheme->parseArg($input, $args);
            $body.= $admintheme->parseArg($this->body, $args);
        }

        return $admintheme->gettable($this->head, $body);
    }

    public function action($action, $adminurl) {
        $title = Lang::i()->__get($action);

        return array(
            $title,
            "<a href=\"$adminurl=\$id&action=$action\">$title</a>"
        );
    }

    public function checkbox($name) {
        $admin = $this->getadmintheme();

        return array(
            'text-center col-checkbox',
            $admin->templates['checkbox.invert'],
            str_replace('$name', $name, $admin->templates['checkbox.id'])
        );
    }

    public function namecheck() {
        $admin = Admin::i();

        return array(
            'text-center col-checkbox',
            $admin->templates['checkbox.stub'],
            $admin->templates['checkbox.name']
        );
    }

    public static function getColclass($s) {
        //most case
        if (!$s || $s == 'left') {
            return 'text-left';
        }

        $map = array(
            'left' => 'text-left',
            'right' => 'text-right',
            'center' => 'text-center'
        );

        $list = explode(' ', $s);
        foreach ($list as $i => $v) {
            if (isset($map[$v])) {
                $list[$i] = $map[$v];
            }
        }

        return implode(' ', $list);
    }

    public function date($date) {
        if ($date == Lang::ZERODATE) {
            return Lang::i()->noword;
        } else {
            return Lang::date(strtotime($date) , 'd F Y');
        }
    }

    public function datetime($date) {
        if ($date == Lang::ZERODATE) {
            return Lang::i()->noword;
        } else {
            return Lang::date(strtotime($date) , 'd F Y H:i');
        }
    }

}