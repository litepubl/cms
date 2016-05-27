<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\metatags;

use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\view\Theme;
use litepubl\tag\View as CatView;
use litepubl\view\MainView;

class Plugin extends \litepubl\core\Plugin
{

    public function themeParsed(Theme $theme)
    {
        $theme->templates['index'] = strtr($theme->templates['index'], array(
            '$template.keywords' => '$metatags.keywords',
            '$template.description' => '$metatags.description',
        ));
    }

    public function getList()
    {
        $context = $this->getApp()->context;
if (!$context) {
return false;
}

        if ($context->view instanceof CatView) {
            $list = $context->view->getIdPosts($context->id);
        } elseif (isset($context->view->idposts)) {
            $list = $context->view->idposts;
        } else {
            return false;
        }

        if (count($list)) {
            Posts::i()->loadItems($list);
            return array_slice($list, 0, 3);
        }

        return false;
    }

    public function getKeywords()
    {
        if ($list = $this->getlist()) {
            $result = '';
            foreach ($list as $id) {
                $post = Post::i($id);
                $result.= $post->keywords . ', ';
            }
            return trim($result, ', ');
        }

        return MainView::i()->getkeywords();
    }

    public function getDescription()
    {
        if ($list = $this->getlist()) {
            $result = '';
            foreach ($list as $id) {
                $post = Post::i($id);
                $result.= $post->title . ' ';
                if (strlen($result) > 250) break;
            }
            return $result;
        }
        return MainView::i()->getdescription();
    }

}
