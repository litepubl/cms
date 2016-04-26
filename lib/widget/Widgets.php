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
use litepubl\core\Arr;

class Widgets extends \litepubl\core\Items implements \litepubl\core\ResponsiveInterface
{
use \litepubl\core\PoolStorageTrait;

    public $classes;
    public $currentsidebar;
    public $idwidget;
    public $idurlcontext;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->addevents('onwidget', 'onadminlogged', 'onadminpanel', 'ongetwidgets', 'onsidebar');
        $this->basename = 'widgets';
        $this->currentsidebar = 0;
        $this->idurlcontext = 0;
        $this->addmap('classes', array());
    }

    public function add(twidget $widget) {
        return $this->additem(array(
            'class' => get_class($widget) ,
            'cache' => $widget->cache,
            'title' => $widget->gettitle(0) ,
            'template' => $widget->template
        ));
    }

    public function addext(twidget $widget, $title, $template) {
        return $this->additem(array(
            'class' => get_class($widget) ,
            'cache' => $widget->cache,
            'title' => $title,
            'template' => $template
        ));
    }

    public function addclass(twidget $widget, $class) {
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

    public function subclass($id) {
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

    public function deleteclass($class) {
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

        $result = getinstance($class);
        $result->id = $id;
        return $result;
    }

    public function getSidebar($context, tview $schema) {
        return $this->getsidebarindex($context, $schema, $this->currentsidebar++);
    }

    public function getSidebarindex($context, tview $schema, $sidebar) {
        $items = $this->getwidgets($context, $schema, $sidebar);
        if ($context instanceof iwidgets) {
            $context->getwidgets($items, $sidebar);

        }

        if ( $this->getApp()->options->admincookie) {
            $this->callevent('onadminlogged', array(&$items,
                $sidebar
            ));
        }

        if ( $this->getApp()->router->adminpanel) {
            $this->callevent('onadminpanel', array(&$items,
                $sidebar
            ));
        }

        $this->callevent('ongetwidgets', array(&$items,
            $sidebar
        ));

        $result = $this->getsidebarcontent($items, $sidebar, !$schema->customsidebar && $schema->disableajax);

        if ($context instanceof iwidgets) {
            $context->getsidebar($result, $sidebar);
        }

        $this->callevent('onsidebar', array(&$result,
            $sidebar
        ));
        return $result;
    }

    private function getWidgets($context, Schema $schema, $sidebar) {
        $theme = $schema->theme;
        if (($schema->id > 1) && !$schema->customsidebar) {
            $schema = Schema::i(1);
        }

        $items = isset($schema->sidebars[$sidebar]) ? $schema->sidebars[$sidebar] : array();

        $subitems = $this->getsubitems($context, $sidebar);
        $items = $this->joinitems($items, $subitems);
        if ($sidebar + 1 == $theme->sidebarscount) {
            for ($i = $sidebar + 1; $i < count($schema->sidebars); $i++) {
                $subitems = $this->joinitems($schema->sidebars[$i], $this->getsubitems($context, $i));

                //delete copies
                foreach ($subitems as $index => $subitem) {
                    $id = $subitem['id'];
                    foreach ($items as $item) {
                        if ($id == $item['id']) Arr::delete($subitems, $index);
                    }
                }

                foreach ($subitems as $item) $items[] = $item;
            }
        }

        return $items;
    }

    private function getSubitems($context, $sidebar) {
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
        $filename = twidget::getcachefilename($id, $sidebar);
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

    public function find(twidget $widget) {
        $class = get_class($widget);
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
 return $id;
}


        }
        return false;
    }

    public function xmlrpcgetwidget($id, $sidebar, $idurl) {
        if (!isset($this->items[$id])) {
 return $this->error("Widget $id not found");
}


        $this->idurlcontext = $idurl;
        $result = $this->getwidgetcontent($id, $sidebar);
        //fix bug for javascript client library
        if ($result == '') {
 return 'false';
}


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
                $filename = twidget::getcachefilename($id, $sidebar);
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