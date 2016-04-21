<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\pages;
use litepul\view\Guard;
use litepul\view\Lang;
use litepul\view\Schemes;
use litepul\core\UserGroups;
use litepubl\view\Lang;

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
        if (! $this->getApp()->options->user) {
            return  $this->getApp()->router->redir('/admin/login/' .  $this->getApp()->site->q . 'backurl=' . urlencode( $this->getApp()->router->url));
        }

        if (! $this->getApp()->options->hasgroup('editor')) {
            $url = UserGroups::i()->gethome( $this->getApp()->options->group);
            if ($url == '/admin/') {
                return 403;
            }

            return  $this->getApp()->router->redir($url);
        }

        Lang::usefile('admin');
    }

    public function getHead() {
        $editor = PostEditor::i();
        return $editor->gethead();
    }

    public function getTitle() {
        return Lang::get('common', 'board');
    }

    public function getKeywords() {
        return '';
    }

    public function getDescription() {
        return '';
    }

    public function getIdSchema() {
        return Schemes::i()->defaults['admin'];
    }

    public function setIdSchema($id) {
    }

    public function getCont() {
        $editor = PostEditor::i();
        return $editor->getexternal();
    }

}