<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\singlecat;

use litepubl\post\Announce;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\view\Theme;

class Plugin extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['invertorder'] = false;
        $this->data['maxcount'] = 5;
        $this->data['tml'] = '<li><a href="$site.url$url" title="$title">$title</a></li>';
        $this->data['tmlitems'] = '<ul>$items</ul>';
    }

    public function themeParsed(Theme $theme)
    {
        $tag = '$singlecat.content';
        if (!strpos($theme->templates['content.post'], $tag)) {
            $theme->templates['content.post'] = str_replace('$post.content', '$post.content ' . $tag, $theme->templates['content.post']);
        }
    }

    public function getContent()
    {
        $post = $this->getApp()->context->model;
        if (!($post instanceof Post)) {
            return '';
        }

        if (count($post->categories) == 0) {
            return '';
        }

        $idcat = $post->categories[0];
        if ($idcat == 0) {
            return '';
        }

        $table = $this->getApp()->db->prefix . 'categoriesitems';
        $order = $this->invertorder ? 'asc' : 'desc';
        $posts = Posts::i();
        $annnounce= new Anounce();
        $result = $announce->getLinks(
            "$posts->thistable.id in
    (select  $table.post from $table where $table.item = $idcat)
    and $posts->thistable.id != $post->id
    order by $posts->thistable.posted  $order limit $this->maxcount", $this->tml
        );

        return str_replace('$items', $result, $this->tmlitems);
    }
}
