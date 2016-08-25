<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\plugins\keywords;

use litepubl\core\Plugins;
use litepubl\core\Str;

class Widget extends \litepubl\widget\Widget
{
    public $links;

    public function create()
    {
        parent::create();
        $this->basename = 'keywords' . DIRECTORY_SEPARATOR . 'index';
        $this->cache = 'nocache';
        $this->adminclass = __NAMESPACE__ . '\Admin';
        $this->data['count'] = 6;
        $this->data['notify'] = true;
        $this->data['trace'] = true;
        $this->addmap('links', []);
    }

    public function getDefTitle(): string
    {
        $about = Plugins::getabout(Plugins::getname(__file__));
        return $about['deftitle'];
    }

    public function getWidget(int $id, int $sidebar): string
    {
        $content = $this->getContent($id, $sidebar);
        if (!$content) {
            return '';
        }

        $title = $this->getTitle($id);
        return $this->getView()->getWidget($id, $sidebar, $title, $content, $this->template);
    }

    public function getContent(int $id, int $sidebar): string
    {
        $app = $this->getApp();
        if (!isset($app->context)
            || $app->context->request->isAdminPanel
            || ($app->context->response->status != 200)
            || Str::begin($app->context->request->url, '/croncron.php')
            || ($app->context->response->headers['Content-type'] != 'text/html;charset=utf-8')
        ) {
            return '';
        }

        $id = $app->context->itemRoute['id'];
        $filename = $app->paths->data . 'keywords' . DIRECTORY_SEPARATOR . $id . '.' . $app->context->request->page . '.php';
        if (file_exists($filename)) {
            $links = file_get_contents($filename);
        } else {
            if (count($this->links) < $this->count) {
                return '';
            }

            $arlinks = array_splice($this->links, 0, $this->count);
            $this->save();

            $links = '';
            $text = '';
            foreach ($arlinks as $link) {
                $links.= sprintf('<li><a href="%s">%s</a></li>', $link['url'], $link['text']);
                $text.= $link['text'] . "\n";
            }

            file_put_contents($filename, $links);
            if ($this->notify) {
                $plugin = Keywords::i();
                $plugin->added($filename, $text);
            }
        }

        return $this->getView()->getContent($links, $this->template, $sidebar);
    }
}
