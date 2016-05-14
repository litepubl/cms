<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

class tsinglecat extends \litepubl\core\Plugin
{

    public static function i()
    {
        return static ::iGet(__class__);
    }

    protected function create()
    {
        parent::create();
        $this->data['invertorder'] = false;
        $this->data['maxcount'] = 5;
        $this->data['tml'] = '<li><a href="$site.url$url" title="$title">$title</a></li>';
        $this->data['tmlitems'] = '<ul>$items</ul>';
    }

    public function themeparsed(ttheme $theme)
    {
        $tag = '$singlecat.content';
        if (!strpos($theme->templates['content.post'], $tag)) {
            $theme->templates['content.post'] = str_replace('$post.content', '$post.content ' . $tag, $theme->templates['content.post']);
        }
    }

    public function getContent()
    {
        $post = $this->getApp()->router->context;
        if (!($post instanceof tpost)) {
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
        $posts = tposts::i();
        $result = $posts->getlinks("$posts->thistable.id in
    (select  $table.post from $table where $table.item = $idcat)
    and $posts->thistable.id != $post->id
    order by $posts->thistable.posted  $order limit $this->maxcount", $this->tml);

        return str_replace('$items', $result, $this->tmlitems);
    }

}

