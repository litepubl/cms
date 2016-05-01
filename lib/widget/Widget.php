<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\view\Theme;
use litepubl\view\Vars;
use litepubl\view\Schema;

class Widget extends \litepubl\core\Events
 {
    public $id;
    public $template;
    protected $adminclass;
protected $adminInstance;

    protected function create() {
        parent::create();
        $this->basename = 'widget';
        $this->cache = 'cache';
        $this->id = 0;
        $this->template = 'widget';
        $this->adminclass = '\litepubl\admin\widget\Widget';
    }

    public function addToSidebar($sidebar) {
        $widgets = Widgets::i();
        $id = $widgets->add($this);
        $sidebars = Sidebars::i();
        $sidebars->insert($id, false, $sidebar, -1);

        $this->getApp()->cache->clear();
        return $id;
    }

    protected function getAdmin() {
if (!$this->adminInstance) {
            $this->adminInstance = $this->getApp()->classes->getinstance($this->adminclass);
            $this->adminInstance->widget = $this;
        }

return $this->adminInstance;
    }

    public function getWidget($id, $sidebar) {
$vars = new Vars();
$vars->widget = $this;

        try {
            $title = $this->gettitle($id);
            $content = $this->getcontent($id, $sidebar);
        }
        catch(\Exception $e) {
             $this->getApp()->options->handexception($e);
            return '';
        }
$view = new View();
return $view->getidwidget($id, $title, $content, $this->template, $sidebar);
    }

    public function getDeftitle() {
        return '';
    }

    public function getTitle($id) {
        if (!isset($id)) $this->error('no id');
        $widgets = Widgets::i();
        if (isset($widgets->items[$id])) {
            return $widgets->items[$id]['title'];
        }
        return $this->getdeftitle();
    }

    public function setTitle($id, $title) {
        $widgets = Widgets::i();
        if (isset($widgets->items[$id]) && ($widgets->items[$id]['title'] != $title)) {
            $widgets->items[$id]['title'] = $title;
            $widgets->save();
        }
    }

    public function getContent($id, $sidebar) {
        return '';
    }

    public static function getCachefilename($id) {
        $theme = Theme::context();
        return sprintf('widget.%s.%d.php', $theme->name, $id);
    }

    public function expired($id) {
        switch ($this->cache) {
            case 'cache':
                Cache::i()->expired($id);
                break;


            case 'include':
                $sidebar = static ::findsidebar($id);
                $filename = static ::getCacheFilename($id, $sidebar);
                 $this->getApp()->cache->setString($filename, $this->getContent($id, $sidebar));
                break;
        }
    }

    public static function findsidebar($id) {
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

    public function expire() {
        $widgets = Widgets::i();
        foreach ($widgets->items as $id => $item) {
            if ($this instanceof $item['class']) $this->expired($id);
        }
    }

}