<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\plugins\usernews;

use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\view\Lang;

class Editor extends \litepubl\admin\Editor
{
    public function getTabsTemplate()
    {
        $result = '';
        $plugin = Plugin::i();
        if ($plugin->insertsource) {
            $result .= '[text=sourceurl]';
        }

        $result .= '$categories';
        return $result;
    }

    public function getArgsTab(Post $post, Args $args)
    {
        $args->id = $post->id;
        $args->ajax = $this->getajaxlink($post->id);
        $args->categories = $this->getCategories($post);

        $plugin = Plugin::i();
        if ($plugin->insertsource) {
            $args->data['$lang.sourceurl'] = Lang::admin()->get('usernews', 'sourceurl');
            $args->sourceurl = isset($post->meta->sourceurl) ? $post->meta->sourceurl : '';
        }
    }

    protected function processtab(Post $post)
    {
        extract($_POST, EXTR_SKIP);

        $post->title = $title;
        $post->categories = $this->admintheme->processcategories();
        $post->content = $raw;

        $plugin = Plugin::i();
        if ($plugin->insertsource) {
            $post->meta->sourceurl = $sourceurl;
            $post->filtered = sprintf($plugin->sourcetml, $post->meta->sourceurl) . $post->filtered;
        }
    }

    public function canProcess()
    {
        if ($err = parent::canProcess()) {
                return $err;
        }

        $id = (int)$_POST['id'];
        if ($id == 0) {
                $plugin = Plugin::i();
            if ($plugin->checkspam) {
                $posts = Posts::i();
                $hold = $posts->db->getcount('status = \'draft\' and author = ' . $this->getApp()->options->user);
                $approved = $posts->db->getcount('status = \'published\' and author = ' . $this->getApp()->options->user);
                if ($approved < 3 && $hold - $approved >= 2) {
                    return Lang::admin('usernews')->manydrafts ;
                }
            }
        }
    }
}
