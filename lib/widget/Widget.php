<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\widget;

use litepubl\view\Schema;
use litepubl\view\Vars;

class Widget extends \litepubl\core\Events
{
    public $id;
    public $cache;
    public $template;
    protected $adminclass;
    protected $adminInstance;

    protected function create()
    {
        parent::create();
        $this->basename = 'widget';
        $this->cache = 'cache';
        $this->id = 0;
        $this->template = 'widget';
        $this->adminclass = 'litepubl\admin\widget\Widget';
    }

    public function addToSidebar(int $sidebar): int
    {
        $widgets = Widgets::i();
        $id = $widgets->add($this);
        $sidebars = Sidebars::i();
        $sidebars->insert($id, false, $sidebar, -1);

        $this->getApp()->cache->clear();
        return $id;
    }

    protected function getAdmin()
    {
        if (!$this->adminInstance) {
            $this->adminInstance = $this->getApp()->classes->getInstance($this->adminclass);
            $this->adminInstance->widget = $this;
        }

        return $this->adminInstance;
    }

    public function getWidgets(): Widgets
    {
        return Widgets::i();
    }

    public function getView()
    {
        return new View();
    }

    public function getWidget(int $id, int $sidebar): string
    {
        $vars = new Vars();
        $vars->widget = $this;

        try {
            $title = $this->getTitle($id);
            $content = $this->getContent($id, $sidebar);
        } catch (\Exception $e) {
            $this->getApp()->logException($e);
            return '';
        }

        return $this->getView()->getWidget($id, $sidebar, $title, $content, $this->template);
    }

    public function getDefTitle(): string
    {
        return '';
    }

    public function getTitle(int $id): string
    {
        if (!isset($id)) {
            $this->error('no id');
        }

        $widgets = Widgets::i();
        if (isset($widgets->items[$id])) {
            return $widgets->items[$id]['title'];
        }

        return $this->getDefTitle();
    }

    public function setTitle(int $id, string $title)
    {
        $widgets = Widgets::i();
        if (isset($widgets->items[$id]) && ($widgets->items[$id]['title'] != $title)) {
            $widgets->items[$id]['title'] = $title;
            $widgets->save();
        }
    }

    public function getContent(int $id, int $sidebar): string
    {
        return '';
    }

    public static function findSidebar(int $id): int
    {
        $schema = Schema::i();
        foreach ($schema->sidebars as $i => $sidebar) {
            foreach ($sidebar as $item) {
                if ($id == $item['id']) {
                    return $i;
                }
            }
        }

        return 0;
    }
}
