<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin;

use litepubl\core\Items;
use litepubl\post\Post;
use litepubl\view\Admin;
use litepubl\view\Args;
use litepubl\view\Base;
use litepubl\view\Lang;

class Table
{
    const LEFT = 'text-left';
    const RIGHT = 'text-right';
    const CENTER = 'text-center';

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

    public static function fromItems(array $items, array $struct): string
    {
        $classname = get_called_class();
        $self = new $classname();
        return $self->buildItems($items, $struct);
    }

    public function __construct()
    {
        $this->head = '';
        $this->body = '';
        $this->footer = '';
        $this->callbacks = [];
        $this->args = new Args();
        $this->data = [];
    }

    public function setStruct(array $struct)
    {
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
            } elseif (is_callable($s)) {
                $name = '$callback' . $index;
                $this->body.= sprintf('<td class="%s">%s</td>', $colclass, $name);

                array_unshift($item, $this);
                $this->callbacks[$name] = [
                    'callback' => $s,
                    'params' => $item,
                ];
            } else {
                throw new Exception('Unknown column ' . var_export($s, true));
            }
        }

        $this->body.= '</tr>';
    }

    public function addCallback(string $varname, $callback, $param = null)
    {
        $this->callbacks[$varname] = [
            'callback' => $callback,
            'params' => [
                $this,
                $param
            ] ,
        ];
    }

    public function addFooter(string $footer)
    {
        $this->footer = sprintf('<tfoot><tr>%s</tr></tfoot>', $footer);
    }

    public function td(string $colclass, string $content): string
    {
        return sprintf('<td class="%s">%s</td>', static ::getcolclass($colclass), $content);
    }

    public function getAdmintheme(): Admin
    {
        if (!$this->admintheme) {
            $this->admintheme = Admin::i();
        }

        return $this->admintheme;
    }

    public function build(array $items): string
    {
        $body = '';

        foreach ($items as $id => $item) {
            $body.= $this->parseitem($id, $item);
        }

        return $this->getadmintheme()->gettable($this->head, $body, $this->footer);
    }

    public function parseItem($id, $item)
    {
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

        return $this->getAdminTheme()->parseArg($this->body, $args);
    }

    public function buildItems(array $items, array $struct): string
    {
        $this->setStruct($struct);
        return $this->build($items);
    }

    //predefined callbacks
    public function itemsCallback(Table $self, Items $owner)
    {
        $self->item = $owner->getItem($self->id);
        $self->args->add($self->item);
    }

    public function setOwner(Items $owner)
    {
        $this->addCallback(
            '$tempcallback' . count($this->callbacks), [
            $this,
            'itemsCallback'
            ], $owner
        );
    }

    public function posts_callback(Table $self)
    {
        $post = Post::i($self->id);
        Base::$vars['post'] = $post->getView();
        $self->args->poststatus = Lang::i()->__get($post->status);
    }

    public function setPosts(array $struct)
    {
        array_unshift($struct, $this->checkbox('checkbox'));
        $this->setStruct($struct);
        $this->addCallback(
            '$tempcallback' . count($this->callbacks), [
            $this,
            'posts_callback'
            ], false
        );
    }

    public function props(array $props): string
    {
        $lang = Lang::i();
        $this->setStruct(
            [
            [
                $lang->name,
                '$name'
            ] ,

            [
                $lang->property,
                '$value'
            ]
            ]
        );

        $body = '';
        $args = $this->args;
        $admintheme = $this->getadmintheme();

        foreach ($props as $k => $v) {
            if (($k === false) || ($v === false)) {
                continue;
            }

            if (is_array($v)) {
                foreach ($v as $kv => $vv) {
                    if ($k2 = $lang->__get($kv)) {
                        $kv = $k2;
                    }
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

        return $admintheme->getTable($this->head, $body);
    }

    public function inputs(array $inputs): string
    {
        $lang = Lang::i();
        $this->setStruct(
            [
            [
                $lang->name,
                '<label for="$name-input">$title</label>'
            ] ,

            [
                $lang->property,
                '$input'
            ]
            ]
        );

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

        return $admintheme->getTable($this->head, $body);
    }

    public function action(string $action, string $adminurl): array
    {
        $title = Lang::i()->__get($action);

        return [
            $title,
            "<a href=\"$adminurl=\$id&action=$action\">$title</a>"
        ];
    }

    public function checkbox(string $name): array
    {
        $admin = $this->getadmintheme();

        return [
            'text-center col-checkbox',
            $admin->templates['checkbox.invert'],
            str_replace('$name', $name, $admin->templates['checkbox.id'])
        ];
    }

    public function nameCheck(): array
    {
        $admin = Admin::i();

        return [
            'text-center col-checkbox',
            $admin->templates['checkbox.stub'],
            $admin->templates['checkbox.name']
        ];
    }

    public static function getColclass($s): string
    {
        //most case
        if (!$s || $s == 'left') {
            return 'text-left';
        }

        $map = [
            'left' => 'text-left',
            'right' => 'text-right',
            'center' => 'text-center'
        ];

        $list = explode(' ', $s);
        foreach ($list as $i => $v) {
            if (isset($map[$v])) {
                $list[$i] = $map[$v];
            }
        }

        return implode(' ', $list);
    }

    public function date($date): string
    {
        return Lang::i()->getDate($date);
    }

    public function dateTime($date): string
    {
        return Lang::i()->getDateTime($date);
    }
}
