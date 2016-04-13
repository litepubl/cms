<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin;
use litepul\view\Guard;
use litepul\view\Lang;
use litepul\view\Schemes;
use litepul\core\UserGroups;

class Board extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{

    protected function create() {
        parent::create();
        $this->cache = false;
    }

    public function load() {
        return true;
    }
    public function save() {
        return true;
    }

    public function request($id) {
        if ($s = Guard::checkattack()) {
return $s;
}
        if (!litepubl::$options->user) {
            return litepubl::$urlmap->redir('/admin/login/' . litepubl::$site->q . 'backurl=' . urlencode(litepubl::$urlmap->url));
        }

        if (!litepubl::$options->hasgroup('editor')) {
            $url = UserGroups::i()->gethome(litepubl::$options->group);
            if ($url == '/admin/') {
                return 403;
            }

            return litepubl::$urlmap->redir($url);
        }

        Lang::usefile('admin');
    }

    public function gethead() {
        $editor = PostEditor::i();
        return $editor->gethead();
    }

    public function gettitle() {
        return tlocal::get('common', 'board');
    }

    public function getkeywords() {
        return '';
    }

    public function getdescription() {
        return '';
    }

    public function getIdSchema() {
        return Schemes::i()->defaults['admin'];
    }

    public function setIdSchema($id) {
    }

    public function getcont() {
        $editor = PostEditor::i();
        return $editor->getexternal();
    }

}