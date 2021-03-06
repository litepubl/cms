<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\extrasidebars;

use litepubl\core\Event;
use litepubl\view\Theme;

class ExtraSidebars extends \litepubl\core\Plugin
{
    public $themes;

    protected function create()
    {
        parent::create();
        $this->addmap('themes', []);
        $this->data['beforepost'] = false;
        $this->data['afterpost'] = true;
    }

    public function fix(Event $event)
    {
        $theme = $event->theme;
        if (in_array($theme->name, $this->themes) && !isset($theme->templates['extrasidebars'])) {
            $s = & $theme->templates['index'];
            if ($this->beforepost) {
                $s.= '<!--$template.sidebar-->';
            }

            if ($this->afterpost) {
                $s.= '<!--$template.sidebar-->';
            }

            $count = substr_count($s, '$template.sidebar');
            while (count($theme->templates['sidebars']) < $count) {
                $theme->templates['sidebars'][] = $theme->templates['sidebars'][0];
            }
        }
    }

    public function themeParsed(Event $event)
    {
        $theme = $event->theme;
        if (in_array($theme->name, $this->themes) && !isset($theme->templates['extrasidebars'])) {
            $s = & $theme->templates['index'];
            $s = str_replace('<!--$template.sidebar-->', '', $s);
            $sidebar = 0;
            $tag = '$template.sidebar';
            $i = 0;
            while ($i = strpos($s, $tag, $i + 1)) {
                $s = substr_replace($s, $tag . $sidebar++, $i, strlen($tag));
            }

            $theme->templates['extrasidebars'] = $sidebar;
            $post = & $theme->templates['content.post'];
            if ($this->beforepost) {
                $post = str_replace('$post.content', $tag . $sidebar++ . '$post.content', $post);
            }

            if ($this->afterpost) {
                $post = str_replace('$post.content', '$post.content ' . $tag . $sidebar++, $post);
            }
        }
    }
}
