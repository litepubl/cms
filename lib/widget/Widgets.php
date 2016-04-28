<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
    use litepubl\core\Context;
    use litepubl\core\Response;
use litepubl\view\Schema;
use litepubl\view\Theme;
use litepubl\view\ViewInterface;
use litepubl\core\Arr;
use litepubl\core\Str;

class Widgets extends \litepubl\core\Items implements \litepubl\core\ResponsiveInterface
{
use \litepubl\core\PoolStorageTrait;

    public $classes;
    public $currentSidebar;
    public $idwidget;
    public $idurlcontext;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->addevents('onwidget', 'onadminlogged', 'onadminpanel', 'ongetwidgets', 'onsidebar');
        $this->basename = 'widgets';
        $this->currentSidebar = 0;
        $this->idurlcontext = 0;
        $this->addMap('classes', array());
    }

    public function add(Widget $widget) {
        return $this->addItem(array(
            'class' => get_class($widget) ,
            'cache' => $widget->cache,
            'title' => $widget->getTitle(0) ,
            'template' => $widget->template
        ));
    }

    public function addExt(Widget $widget, $title, $template) {
        return $this->addItem(array(
            'class' => get_class($widget) ,
            'cache' => $widget->cache,
            'title' => $title,
            'template' => $template
        ));
    }

    public function addClass(Widget $widget, $class) {
        $this->lock();
        $id = $this->add($widget);
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = array();
        }

        $this->classes[$class][] = array(
            'id' => $id,
            'order' => 0,
            'sidebar' => 0,
            'ajax' => false
        );

        $this->unlock();
        return $id;
    }

    public function subClass($id) {
        foreach ($this->classes as $class => $items) {
            foreach ($items as $item) {
                if ($id == $item['id']) {
                    return $class;
                }
            }
        }

        return false;
    }

    public function delete($id) {
        if (!isset($this->items[$id])) {
            return false;
        }

        foreach ($this->classes as $class => $items) {
            foreach ($items as $i => $item) {
                if ($id == $item['id']) {
                    Arr::delete($this->classes[$class], $i);
                }
            }
        }

        unset($this->items[$id]);
        $this->deleted($id);
        $this->save();
        return true;
    }

    public function deleteClass($class) {
        $this->unbind($class);
        $deleted = array();
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
                unset($this->items[$id]);
                $deleted[] = $id;
            }
        }

        if (count($deleted) > 0) {
            foreach ($this->classes as $name => $items) {
                foreach ($items as $i => $item) {
                    if (in_array($item['id'], $deleted)) {
                        Arr::delete($this->classes[$name], $i);
                    }
                }

                if (!count($this->classes[$name])) {
                    unset($this->classes[$name]);
                }
            }
        }

        if (isset($this->classes[$class])) {
            unset($this->classes[$class]);
        }

        $this->save();

        foreach ($deleted as $id) {
            $this->deleted($id);
        }
    }

    public function class2id($class) {
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
                return $id;
            }
        }

        return false;
    }

    public function getWidget($id) {
        if (!isset($this->items[$id])) {
            return $this->error("The requested $id widget not found");
        }

        $class = $this->items[$id]['class'];
        if (!class_exists($class)) {
            $this->delete($id);
            return $this->error("The $class class not found");
        }

        $result = static::iGet($class);
        $result->id = $id;
        return $result;
    }

    public function getSidebar(Context $context) {
        return $this->getSidebarIndex($context, $this->currentSidebar++);
    }

    public function getSidebarIndex(Context $context, $sidebar) {
        $items = new \ArrayObject($this->getWidgets($context, $schema, $sidebar), ArrayObject::ARRAY_AS_PROPS);
        if ($context instanceof WidgetsInterface) {
            $context->getWidgets($items, $sidebar);
        }

        if ( $this->getApp()->options->admincookie) {
            $this->onadminlogged($items,                $sidebar           );
        }

        if ( $this->getApp()->router->adminpanel) {
            $this->onadminpanel($items, $sidebar);
        }

        $this->ongetwidgets($items, $sidebar);
        $result = $this->getSidebarContent($items, $sidebar, !$schema->customsidebar && $schema->disableajax);

$str = new Str($result);
        if ($context instanceof WidgetsInterface) {
            $context->getSidebar($str, $sidebar);
        }

        $this->onsidebar($str, $sidebar);
        return $str->value;
    }

    private function getWidgets($context, Schema $schema, $sidebar) {
        $theme = $schema->theme;
        if (($schema->id > 1) && !$schema->customsidebar) {
            $schema = Schema::i(1);
        }

        $items = isset($schema->sidebars[$sidebar]) ? $schema->sidebars[$sidebar] : array();

        $subItems = $this->getSubItems($context, $sidebar);
        $items = $this->joinItems($items, $subItems);
        if ($sidebar + 1 == $theme->sidebarscount) {
            for ($i = $sidebar + 1; $i < count($schema->sidebars); $i++) {
                $subItems = $this->joinItems($schema->sidebars[$i], $this->getSubItems($context, $i));

                //delete copies
                foreach ($subItems as $index => $subItem) {
                    $id = $subItem['id'];
                    foreach ($items as $item) {
                        if ($id == $item['id']) {
Arr::delete($subItems, $index);
}
                    }
                }

                foreach ($subItems as $item) {
$items[] = $item;
}
            }
        }

        return $items;
    }

    private function getSubItems($context, $sidebar) {
        $result = array();
        foreach ($this->classes as $class => $items) {
            if ($context instanceof $class) {
                foreach ($items as $item) {
                    if ($sidebar == $item['sidebar']) $result[] = $item;
                }
            }
        }

        return $result;
    }

    private function joinitems(array $items, array $subitems) {
        if (count($subitems) == 0) {
 return $items;
}


        if (count($items) > 0) {
            //delete copies
            for ($i = count($items) - 1; $i >= 0; $i--) {
                $id = $items[$i]['id'];
                foreach ($subitems as $subitem) {
                    if ($id == $subitem['id']) Arr::delete($items, $i);
                }
            }
        }
        //join
        foreach ($subitems as $item) {
            $count = count($items);
            $order = $item['order'];
            if (($order < 0) || ($order >= $count)) {
                $items[] = $item;
            } else {
                Arr::insert($items, $item, $order);
            }
        }

        return $items;
    }

    protected function getSidebarcontent(array $items, $sidebar, $disableajax) {
        $result = '';
        foreach ($items as $item) {
            $id = $item['id'];
            if (!isset($this->items[$id])) {
 continue;
}


            $cachetype = $this->items[$id]['cache'];
            if ($disableajax) $item['ajax'] = false;
            if ($item['ajax'] === 'inline') {
                switch ($cachetype) {
                    case 'cache':
                    case 'nocache':
                    case false:
                        $content = $this->getinline($id, $sidebar);
                        break;


                    default:
                        $content = $this->getajax($id, $sidebar);
                        break;
                }
            } elseif ($item['ajax']) {
                $content = $this->getajax($id, $sidebar);
            } else {
                switch ($cachetype) {
                    case 'cache':
                        $content = Cache::i()->getcontent($id, $sidebar, false);
                        break;


                    case 'include':
                        $content = $this->includewidget($id, $sidebar);
                        break;


                    case 'nocache':
                    case false:
                        $widget = $this->getwidget($id);
                        $content = $widget->getwidget($id, $sidebar);
                        break;


                    case 'code':
                        $content = $this->getcode($id, $sidebar);
                        break;
                }
            }
            $this->callevent('onwidget', array(
                $id, &$content
            ));
            $result.= $content;
        }

        return $result;
    }

    public function getAjax($id, $sidebar) {
        $theme = Theme::i();
        $title = $theme->getajaxtitle($id, $this->items[$id]['title'], $sidebar, 'ajaxwidget');
        $content = "<!--widgetcontent-$id-->";
        return $theme->getidwidget($id, $title, $content, $this->items[$id]['template'], $sidebar);
    }

    public function getInline($id, $sidebar) {
        $theme = Theme::i();
        $title = $theme->getajaxtitle($id, $this->items[$id]['title'], $sidebar, 'inlinewidget');
        if ('cache' == $this->items[$id]['cache']) {
            $cache = Cache::i();
            $content = $cache->getcontent($id, $sidebar);
        } else {
            $widget = $this->getwidget($id);
            $content = $widget->getcontent($id, $sidebar);
        }

        $content = sprintf('<!--%s-->', $content);
        return $theme->getidwidget($id, $title, $content, $this->items[$id]['template'], $sidebar);
    }

    private function includewidget($id, $sidebar) {
        $filename = Widget::getcachefilename($id, $sidebar);
        if (! $this->getApp()->router->cache->exists($filename)) {
            $widget = $this->getwidget($id);
            $content = $widget->getcontent($id, $sidebar);
             $this->getApp()->router->cache->set($filename, $content);
        }

        $theme = Theme::i();
        return $theme->getidwidget($id, $this->items[$id]['title'], "\n<?php echo litepubl::\$router->cache->get('$filename'); ?>\n", $this->items[$id]['template'], $sidebar);
    }

    private function getCode($id, $sidebar) {
        $class = $this->items[$id]['class'];
        return "\n<?php
    \$widget = $class::i();
    \$widget->id = \$id;
    echo \$widget->getwidget($id, $sidebar);
    ?>\n";
    }

    public function find(Widget $widget) {
        $class = get_class($widget);
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
 return $id;
}


        }
        return false;
    }

    private static function getGet($name) {
        return isset($_GET[$name]) ? (int)$_GET[$name] : false;
    }

    private function errorRequest(Response $response, $mesg) {
$response->status = 400;
$response->body = $mesg;
    }

    public function request(Context $context)
    {
    $response = $context->response;
        $response->cache = false;
        $id = static ::getget('id');
        $sidebar = static ::getget('sidebar');
        $this->idurlcontext = static ::getget('idurl');
        if (($id === false) || ($sidebar === false) || !$this->itemexists($id)) {
 return $this->errorRequest('Invalid params');
}

        $themename = isset($_GET['themename']) ? trim($_GET['themename']) : Schema::i(1)->themename;
        if (!preg_match('/^\w[\w\.\-_]*+$/', $themename) || !Theme::exists($themename)) $themename = Schema::i(1)->themename;
        $theme = Theme::getTheme($themename);

        try {
            $response->body= $this->getwidgetcontent($id, $sidebar);
        }
        catch(\Exception $e) {
            return $this->errorRequest('Cant get widget content');
        }
    }

    public function getWidgetcontent($id, $sidebar) {
        if (!isset($this->items[$id])) {
            return false;

        }

        switch ($this->items[$id]['cache']) {
            case 'cache':
                $cache = Cache::i();
                $result = $cache->getcontent($id, $sidebar);
                break;


            case 'include':
                $filename = Widget::getcachefilename($id, $sidebar);
                $result =  $this->getApp()->router->cache->get($filename);
                if (!$result) {
                    $widget = $this->getwidget($id);
                    $result = $widget->getcontent($id, $sidebar);
                     $this->getApp()->router->cache->set($filename, $result);
                }
                break;


            case 'nocache':
            case 'code':
            case false:
                $widget = $this->getwidget($id);
                $result = $widget->getcontent($id, $sidebar);
                break;
        }

        return $result;
    }

    public function getPos($id) {
        return tsidebars::getpos($this->sidebars, $id);
    }

    public function &finditem($id) {
        foreach ($this->classes as $class => $items) {
            foreach ($items as $i => $item) {
                if ($id == $item['id']) {
 return $this->classes[$class][$i];
}


            }
        }
        $item = null;
        return $item;
    }

}