<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tajaxposteditor extends tevents {
    public $idpost;
    private $isauthor;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'ajaxposteditor';
        $this->data['eventnames'] = & $this->eventnames;
        $this->map['eventnames'] = 'eventnames';

        $this->data['head'] = '';
        $this->data['visual'] = '';
        //'/plugins/ckeditor/init.js';
        $this->data['ajaxvisual'] = true;
    }

    public function addevent($name, $class, $func, $once = false) {
        if (!in_array($name, $this->eventnames)) {
            $this->eventnames[] = $name;
        }

        return parent::addevent($name, $class, $func, $once);
    }

    public function delete_event($name) {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
            array_delete_value($this->eventnames, $name);
            $this->save();
        }
    }

    protected static function error403() {
        return '<?php header(\'HTTP/1.1 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false) . 'Forbidden';
    }

    public static function auth() {
        $options = litepubl::$options;
        if (!$options->user) return static ::error403();
        if (!$options->hasgroup('editor')) {
            if (!$options->hasgroup('author')) return static ::error403();
        }
    }

    public function request($arg) {
        $this->cache = false;
        turlmap::sendheader(false);

        if ($err = static ::auth()) return $err;
        $this->idpost = tadminhtml::idparam();
        $this->isauthor = litepubl::$options->ingroup('author');
        if ($this->idpost > 0) {
            $posts = tposts::i();
            if (!$posts->itemexists($this->idpost)) return static ::error403();
            if (!litepubl::$options->hasgroup('editor')) {
                if (litepubl::$options->hasgroup('author')) {
                    $this->isauthor = true;
                    $post = tpost::i($this->idpost);
                    if (litepubl::$options->user != $post->author) return static ::error403();
                }
            }
        }

        return $this->getcontent();
    }

    public function getcontent() {
        $theme = tview::i(tviews::i()->defaults['admin'])->theme;
        $lang = tlocal::i('editor');
        $post = tpost::i($this->idpost);
        $vars = new themevars();
        $vars->post = $post;

        switch ($_GET['get']) {
            case 'tags':
                $result = $theme->getinput('text', 'tags', $post->tagnames, $lang->tags);
                $lang->section = 'editor';
                $result.= $theme->h($lang->addtags);
                $items = array();
                $tags = $post->factory->tags;
                $list = $tags->getsorted(-1, 'name', 0);
                foreach ($list as $id) {
                    $items[] = '<a href="" class="posteditor-tag">' . $tags->items[$id]['title'] . "</a>";
                }
                $result.= sprintf('<p>%s</p>', implode(', ', $items));
                break;


            case 'status':
            case 'access':
                $args = new targs();
                $args->comstatus = tadminhtml::array2combo(array(
                    'closed' => $lang->closed,
                    'reg' => $lang->reg,
                    'guest' => $lang->guest,
                    'comuser' => $lang->comuser
                ) , $post->comstatus);

                $args->pingenabled = $post->pingenabled;
                $args->status = tadminhtml::array2combo(array(
                    'published' => $lang->published,
                    'draft' => $lang->draft
                ) , $post->status);

                $args->perms = tadminperms::getcombo($post->idperm);
                $args->password = $post->password;
                $result = admintheme::admin()->parsearg('[combo=comstatus]
      [checkbox=pingenabled]
      [combo=status]
      $perms
      [password=password]
      <p>$lang.notepassword</p>', $args);

                break;


            case 'view':
                $result = tadminviews::getcomboview($post->idview);
                break;


            default:
                $name = trim($_GET['get']);
                if (isset($this->events[$name])) {
                    $result = $this->callevent($name, array(
                        $post
                    ));
                } else {
                    $result = var_export($_GET, true);
                }
        }

        //tfiler::log($result);
        return turlmap::htmlheader(false) . $result;
    }

    public function gettext($text, $admintheme = null) {
        if (!$admintheme) {
            $admintheme = admintheme::admin();
        }

        $args = new targs();
        if ($this->visual) {
            if ($this->ajaxvisual) {
                $args->scripturl = $this->visual;
                $args->visual = $admintheme->parsearg($admintheme->templates['posteditor.text.visual'], $args);
            } else {
                $args->visual = ttemplate::i()->getjavascript($this->visual);
            }
        } else {
            $args->visual = '';
        }

        $args->raw = $text;
        return $admintheme->parsearg($admintheme->templates['posteditor.text'], $args);
    }

} //class