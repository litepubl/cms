<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\pages;
    use litepubl\core\Context;
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

    public function request(Context $context)
    {
    $response = $context->response;
        if ($context->checkAttack()) {
return;
}

$app = $this->getApp();
        if (! $app->options->user) {
return $response->redir('/admin/login/' .  $app->site->q . 'backurl=' . urlencode( $context->request->url));
        }

        if (! $app->options->hasgroup('editor')) {
            $url = UserGroups::i()->gethome( $app->options->group);
            if ($url == '/admin/') {
$response->status = 403;
return;
            }

            return  $response->redir($url);
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