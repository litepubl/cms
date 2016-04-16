<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;
use litepubl\tag\Cats;
use litepubl\post\Files;
use litepubl\admin\GetPerm;

class Admin extends Base
{
    public $onfileperm;

    public static function i() {
        $result = getinstance(get_called_class());
        if (!$result->name && ($context = litepubl::$urlmap->context) && isset($context->idview)) {
            $result->name = tview::getview($context)->adminname;
            $result->load();
        }

        return $result;
    }

    public static function admin() {
        return Schema::i(Schemes::i()->defaults['admin'])->admintheme;
    }

    public function getparser() {
        return AdminParser::i();
    }

    public function shortcode($s, targs $args) {
        $result = trim($s);
        //replace [tabpanel=name{content}]
        if (preg_match_all('/\[tabpanel=(\w*+)\{(.*?)\}\]/ims', $result, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $name = $item[1];
                $replace = strtr($this->templates['tabs.panel'], array(
                    '$id' => $name,
                    '$content' => trim($item[2]) ,
                ));

                $result = str_replace($item[0], $replace, $result);
            }
        }

        if (preg_match_all('/\[(editor|text|email|password|upload|checkbox|combo|hidden|submit|button|calendar|tab|ajaxtab|tabpanel)[:=](\w*+)\]/i', $result, $m, PREG_SET_ORDER)) {
            $theme = Theme::i();
            $lang = lang::i();

            foreach ($m as $item) {
                $type = $item[1];
                $name = $item[2];
                $varname = '$' . $name;

                switch ($type) {
                    case 'editor':
                    case 'text':
                    case 'email':
                    case 'password':
                        if (isset($args->data[$varname])) {
                            $args->data[$varname] = static ::quote($args->data[$varname]);
                        } else {
                            $args->data[$varname] = '';
                        }

                        $replace = strtr($theme->templates["content.admin.$type"], array(
                            '$name' => $name,
                            '$value' => $varname
                        ));
                        break;


                    case 'calendar':
                        $replace = $this->getcalendar($name, $args->data[$varname]);
                        break;


                    case 'tab':
                        $replace = strtr($this->templates['tabs.tab'], array(
                            '$id' => $name,
                            '$title' => $lang->__get($name) ,
                            '$url' => '',
                        ));
                        break;


                    case 'ajaxtab':
                        $replace = strtr($this->templates['tabs.tab'], array(
                            '$id' => $name,
                            '$title' => $lang->__get($name) ,
                            '$url' => "\$ajax=$name",
                        ));
                        break;


                    case 'tabpanel':
                        $replace = strtr($this->templates['tabs.panel'], array(
                            '$id' => $name,
                            '$content' => isset($args->data[$varname]) ? $varname : '',
                        ));
                        break;


                    default:
                        $replace = strtr($theme->templates["content.admin.$type"], array(
                            '$name' => $name,
                            '$value' => $varname
                        ));
                }

                $result = str_replace($item[0], $replace, $result);
            }
        }

        return $result;
    }

    public function parsearg($s, targs $args) {
        $result = $this->shortcode($s, $args);
        $result = strtr($result, $args->data);
        $result = $args->callback($result);
        return $this->parse($result);
    }

    public function form($tml, targs $args) {
        return $this->parsearg(str_replace('$items', $tml, Theme::i()->templates['content.admin.form']) , $args);
    }

    public function gettable($head, $body, $footer = '') {
        return strtr($this->templates['table'], array(
            '$class' => Theme::i()->templates['content.admin.tableclass'],
            '$head' => $head,
            '$body' => $body,
            '$footer' => $footer,
        ));
    }

    public function success($text) {
        return str_replace('$text', $text, $this->templates['success']);
    }

    public function getcount($from, $to, $count) {
        return $this->h(sprintf(Lang::i()->itemscount, $from, $to, $count));
    }

    public function geticon($name, $screenreader = false) {
        return str_replace('$name', $name, $this->templates['icon']) . ($screenreader ? str_replace('$text', $screenreader, $this->templates['screenreader']) : '');
    }

    public function getsection($title, $content) {
        return strtr($this->templates['section'], array(
            '$title' => $title,
            '$content' => $content
        ));
    }

    public function geterr($content) {
        return strtr($this->templates['error'], array(
            '$title' => Lang::i()->error,
            '$content' => $content
        ));
    }

    public function help($content) {
        return str_replace('$content', $content, $this->templates['help']);
    }

    public function getcalendar($name, $date) {
        $date = datefilter::timestamp($date);

        $args = new targs();
        $args->name = $name;
        $args->title = Lang::i()->__get($name);
        $args->format = DateFilter::$format;

        if ($date) {
            $args->date = date(datefilter::$format, $date);
            $args->time = date(datefilter::$timeformat, $date);
        } else {
            $args->date = '';
            $args->time = '';
        }

        return $this->parsearg($this->templates['calendar'], $args);
    }

    public function getdaterange($from, $to) {
        $from = datefilter::timestamp($from);
        $to = datefilter::timestamp($to);

        $args = new targs();
        $args->from = $from ? date(datefilter::$format, $from) : '';
        $args->to = $to ? date(datefilter::$format, $to) : '';
        $args->format = datefilter::$format;

        return $this->parsearg($this->templates['daterange'], $args);
    }

    public function getcats(array $items) {
        Lang::i()->addsearch('editor');
        $result = $this->parse($this->templates['posteditor.categories.head']);
        Cats::i()->loadall();
        $result.= $this->getsubcats(0, $items);
        return $result;
    }

    protected function getsubcats($parent, array $items, $exclude = false) {
        $result = '';
        $args = new targs();
        $tml = $this->templates['posteditor.categories.item'];
        $categories = Cats::i();
        foreach ($categories->items as $id => $item) {
            if (($parent == $item['parent']) && !($exclude && in_array($id, $exclude))) {
                $args->add($item);
                $args->checked = in_array($item['id'], $items);
                $args->subcount = '';
                $args->subitems = $this->getsubcats($id, $items, $exclude);
                $result.= $this->parsearg($tml, $args);
            }
        }

        if ($result) {
            $result = str_replace('$item', $result, $this->templates['posteditor.categories']);
        }

        return $result;
    }

    public function processcategories() {
        $result = tadminhtml::check2array('category-');
        array_clean($result);
        array_delete_value($result, 0);
        return $result;
    }

    public function getfilelist(array $list) {
        $args = new Args();
        $args->fileperm = '';

        if (is_callable($this->onfileperm)) {
            call_user_func_array($this->onfileperm, array(
                $args
            ));
        } else if (litepubl::$options->show_file_perm) {
            $args->fileperm = GetPerm::combo(0, 'idperm_upload');
        }

        $files = Files::i();
        $where = litepubl::$options->ingroup('editor') ? '' : ' and author = ' . litepubl::$options->user;

        $db = $files->db;
        //total count files
        $args->count = (int)$db->getcount(" parent = 0 $where");
        //already loaded files
        $args->items = '{}';
        // attrib for hidden input
        $args->files = '';

        if (count($list)) {
            $items = implode(',', $list);
            $args->files = $items;
            $args->items = tojson($db->res2items($db->query("select * from $files->thistable where id in ($items) or parent in ($items)")));
        }

        return $this->parsearg($this->templates['posteditor.filelist'], $args);
    }

    public function check2array($prefix) {
        $result = array();
        foreach ($_POST as $key => $value) {
            if (strbegin($key, $prefix)) {
                $result[] = is_numeric($value) ? (int)$value : $value;
            }
        }

        return $result;
    }

}