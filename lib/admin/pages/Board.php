<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\pages;

use litepubl\admin\posts\Editor;
use litepubl\core\Context;
use litepubl\core\UserGroups;
use litepubl\view\Lang;
use litepubl\view\Schemes;

class Board extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{

    public function load()
    {
        return true;
    }
    public function save()
    {
        return true;
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;
        if ($context->checkAttack()) {
            return;
        }

        $app = $this->getApp();
        if (!$app->options->user) {
            return $response->redir('/admin/login/' . $app->site->q . 'backurl=' . urlencode($context->request->url));
        }

        if (!$app->options->hasgroup('editor')) {
            $url = UserGroups::i()->gethome($app->options->group);
            if ($url == '/admin/') {
                $response->status = 403;
                return;
            }

            return $response->redir($url);
        }

        Lang::usefile('admin');
    }

    public function getIdSchema(): int
    {
        return Schemes::i()->defaults['admin'];
    }

    public function getHead(): string
    {
        $editor = Editor::i();
        return $editor->gethead();
    }

    public function getTitle(): string
    {
        return Lang::get('common', 'board');
    }

    public function getKeywords(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function setIdSchema(int $id)
    {
    }

    public function getCont(): string
    {
        $editor = Editor::i();
        return $editor->getexternal();
    }
}
