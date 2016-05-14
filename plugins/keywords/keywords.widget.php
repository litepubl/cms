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

use litepubl\core\Plugins;
use litepubl\core\Str;
use litepubl\view\Theme;

class tkeywordswidget extends twidget
{
    public $links;

    public static function i()
    {
        return static ::iGet(__class__);
    }

    public function create()
    {
        parent::create();
        $this->basename = 'keywords' . DIRECTORY_SEPARATOR . 'index';
        $this->cache = 'nocache';
        $this->adminclass = 'tadminkeywords';
        $this->data['count'] = 6;
        $this->data['notify'] = true;
        $this->data['trace'] = true;
        $this->addmap('links', array());
    }

    public function getDeftitle()
    {
        $about = Plugins::getabout(Plugins::getname(__file__));
        return $about['deftitle'];
    }

    public function getWidget($id, $sidebar)
    {
        $content = $this->getcontent($id, $sidebar);
        if ($content == '') {
            return '';
        }

        $title = $this->gettitle($id);
        $theme = Theme::i();
        return $theme->getwidget($title, $content, $this->template, $sidebar);
    }

    public function getContent($id, $sidebar)
    {
        if ($this->getApp()->router->is404 || $this->getApp()->router->adminpanel || Str::begin($this->getApp()->router->url, '/croncron.php') || Str::end($this->getApp()->router->url, '.xml')) {
            return '';
        }

        $id = $this->getApp()->router->item['id'];
        $filename = $this->getApp()->paths->data . 'keywords' . DIRECTORY_SEPARATOR . $id . '.' . $this->getApp()->context->request->page . '.php';
        if (@file_exists($filename)) {
            $links = file_get_contents($filename);
        } else {
            if (count($this->links) < $this->count) {
                return '';
            }

            $arlinks = array_splice($this->links, 0, $this->count);
            $this->save();

            //$links = "\n<li>" . implode("</li>\n<li>", $arlinks)  . "</li>";
            $links = '';
            $text = '';
            foreach ($arlinks as $link) {
                $links.= sprintf('<li><a href="%s">%s</a></li>', $link['url'], $link['text']);
                $text.= $link['text'] . "\n";
            }
            file_put_contents($filename, $links);
            if ($this->notify) {
                $plugin = tkeywordsplugin::i();
                $plugin->added($filename, $text);
            }
        }

        $theme = Theme::i();
        return $theme->getwidgetcontent($links, $this->template, $sidebar);
    }

}

