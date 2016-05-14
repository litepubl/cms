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
use litepubl\view\Schema;
use litepubl\view\ViewInterface;
use litepubl\core\Arr;
use litepubl\core\Str;

class Widgets extends \litepubl\core\Items
{
use \litepubl\core\PoolStorageTrait;

    public $classes;
    public $currentSidebar;
    public $idwidget;
    public $onFindContextCallback;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->addevents('onwidget', 'onadminlogged', 'onadminpanel', 'ongetwidgets', 'onsidebar');
        $this->basename = 'widgets';
        $this->currentSidebar = 0;
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

    public function getSidebar(ViewInterface $view) {
        return $this->getSidebarIndex($view, $this->currentSidebar++);
    }

    public function getSidebarIndex(ViewInterface $view, $sidebar) {
        $items = new \ArrayObject($this->getWidgets($view, $sidebar), \ArrayObject::ARRAY_AS_PROPS);
        if ($view instanceof WidgetsInterface) {
            $view->getWidgets($items, $sidebar);
        }

$options = $this->getApp()->options;
        if ($options->adminFlag && $options->group == 'admin') {
            $this->onadminlogged($items,                $sidebar           );
        }

/*
        if ( $router->adminpanel) {
            $this->onadminpanel($items, $sidebar);
        }
*/
        $this->ongetwidgets($items, $sidebar);

$schema = Schema::getSchema($view);
        $result = $this->getSidebarContent($items, $sidebar, !$schema->customsidebar && $schema->disableajax);

$str = new Str($result);
        if ($view instanceof WidgetsInterface) {
            $view->getSidebar($str, $sidebar);
        }

        $this->onsidebar($str, $sidebar);
        return $str->value;
    }

    private function getWidgets(ViewInterface $view, $sidebar) {
$schema = Schema::getSchema($view);
        $theme = $schema->theme;
        if (($schema->id > 1) && !$schema->customsidebar) {
            $schema = Schema::i(1);
        }

        $items = isset($schema->sidebars[$sidebar]) ? $schema->sidebars[$sidebar] : array();

        $subItems = $this->getSubItems($view, $sidebar);
        $items = $this->joinItems($items, $subItems);
        if ($sidebar + 1 == $theme->sidebarsCount) {
            for ($i = $sidebar + 1; $i < count($schema->sidebars); $i++) {
                $subItems = $this->joinItems($schema->sidebars[$i], $this->getSubItems($view, $i));

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

    private function getSubItems(ViewInterface $view, $sidebar) {
        $result = array();
        foreach ($this->classes as $class => $items) {
            if ($view instanceof $class) {
                foreach ($items as $item) {
                    if ($sidebar == $item['sidebar']) $result[] = $item;
                }
            }
        }

        return $result;
    }

    private function joinItems(array $items, array $subitems) {
        if (count($subitems) == 0) {
 return $items;
}

        if (count($items)) {
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

    protected function getSidebarContent(\ArrayObject $items, $sidebar, $disableajax) {
        $result = '';
//for call event  getwidget
$str = new Str();

        foreach ($items as $item) {
            $id = $item['id'];
            if (!isset($this->items[$id])) {
 continue;
}

            $cachetype = $this->items[$id]['cache'];
            if ($disableajax) {
$item['ajax'] = false;
}

            if ($item['ajax'] === 'inline') {
                switch ($cachetype) {
                    case 'cache':
                    case 'nocache':
                    case false:
                        $content = $this->getInline($id, $sidebar);
                        break;


                    default:
                        $content = $this->getAjax($id, $sidebar);
                        break;
                }
            } elseif ($item['ajax']) {
                $content = $this->getAjax($id, $sidebar);
            } else {
                switch ($cachetype) {
                    case 'cache':
                        $content = Cache::i()->getContent($id, $sidebar, false);
                        break;


                    case 'include':
                        $content = $this->includeWidget($id, $sidebar);
                        break;


                    case 'nocache':
                    case false:
                        $widget = $this->getWidget($id);
                        $content = $widget->getWidget($id, $sidebar);
                        break;


                    case 'code':
                        $content = $this->getCode($id, $sidebar);
                        break;
                }
            }

$str->value = $content;
            $this->onwidget($id,$str);
            $result.= $str->value;
        }

        return $result;
    }

    public function getAjax($id, $sidebar) {
        $view = new View();
        $title = $view->getAjax($id, $this->items[$id]['title'], $sidebar, 'ajaxwidget');
        $content = "<!--widgetcontent-$id-->";
        return $view->getWidgetId($id, $title, $content, $this->items[$id]['template'], $sidebar);
    }

    public function getInline($id, $sidebar) {
$view = new View();
        $title = $view->getAjax($id, $this->items[$id]['title'], $sidebar, 'inlinewidget');
        if ('cache' == $this->items[$id]['cache']) {
            $cache = Cache::i();
            $content = $cache->getContent($id, $sidebar);
        } else {
            $widget = $this->getWidget($id);
            $content = $widget->getContent($id, $sidebar);
        }

        $content = sprintf('<!--%s-->', $content);
        return $view->getWidgetId($id, $title, $content, $this->items[$id]['template'], $sidebar);
    }

    private function includeWidget($id, $sidebar) {
        $filename = Widget::getCacheFilename($id, $sidebar);
$cache = $this->getApp()->cache;
        if (! $cache->exists($filename)) {
            $widget = $this->getWidget($id);
            $content = $widget->getContent($id, $sidebar);
             $cache->setString($filename, $content);
        }

$view = new View();
        return $view->getWidgetId(
$id,
 $this->items[$id]['title'],
 "\n<?php echo litepubl::\$app->cache->getString('$filename'); ?>\n",
 $this->items[$id]['template'],
 $sidebar
);
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

    public function getWidgetContent($id, $sidebar) {
        if (!isset($this->items[$id])) {
            return false;
        }

        switch ($this->items[$id]['cache']) {
            case 'cache':
                $cache = Cache::i();
                $result = $cache->getcontent($id, $sidebar);
                break;


            case 'include':
                $filename = Widget::getCacheFilename($id, $sidebar);
                $result =  $this->getApp()->cache->getString($filename);
                if (!$result) {
                    $widget = $this->getWidget($id);
                    $result = $widget->getContent($id, $sidebar);
                     $this->getApp()->cache->setString($filename, $result);
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
        return Sidebars::getpos($this->sidebars, $id);
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

public function findContext($class)
{
$app = $this->getApp();
        if ( $app->context->view instanceof $class) {
 return  $app->context->view;
        } elseif ( $app->context->model instanceof $class) {
 return  $app->context->model;
}

if (is_callable($this->onFindContextCallback)) {
return call_user_func_array($this->onFindContextCallback, [$class]);
    }

return false;
}

}