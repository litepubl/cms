<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class twidgets extends titems_storage {
    public $classes;
    public $currentsidebar;
    public $idwidget;
    public $idurlcontext;

    public static function i($id = null) {
        return getinstance(__class__);
    }

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
                    array_delete($this->classes[$class], $i);
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
                        array_delete($this->classes[$name], $i);
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

    public function getwidget($id) {
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

    public function getsidebar($context, tview $view) {
        return $this->getsidebarindex($context, $view, $this->currentsidebar++);
    }

    public function getsidebarindex($context, tview $view, $sidebar) {
        $items = $this->getwidgets($context, $view, $sidebar);
        if ($context instanceof iwidgets) {
            $context->getwidgets($items, $sidebar);

        }

        if (litepubl::$options->admincookie) {
            $this->callevent('onadminlogged', array(&$items,
                $sidebar
            ));
        }

        if (litepubl::$urlmap->adminpanel) {
            $this->callevent('onadminpanel', array(&$items,
                $sidebar
            ));
        }

        $this->callevent('ongetwidgets', array(&$items,
            $sidebar
        ));

        $result = $this->getsidebarcontent($items, $sidebar, !$view->customsidebar && $view->disableajax);

        if ($context instanceof iwidgets) {
            $context->getsidebar($result, $sidebar);
        }

        $this->callevent('onsidebar', array(&$result,
            $sidebar
        ));
        return $result;
    }

    private function getwidgets($context, tview $view, $sidebar) {
        $theme = $view->theme;
        if (($view->id > 1) && !$view->customsidebar) {
            $view = tview::i(1);
        }

        $items = isset($view->sidebars[$sidebar]) ? $view->sidebars[$sidebar] : array();

        $subitems = $this->getsubitems($context, $sidebar);
        $items = $this->joinitems($items, $subitems);
        if ($sidebar + 1 == $theme->sidebarscount) {
            for ($i = $sidebar + 1; $i < count($view->sidebars); $i++) {
                $subitems = $this->joinitems($view->sidebars[$i], $this->getsubitems($context, $i));

                //delete copies
                foreach ($subitems as $index => $subitem) {
                    $id = $subitem['id'];
                    foreach ($items as $item) {
                        if ($id == $item['id']) array_delete($subitems, $index);
                    }
                }

                foreach ($subitems as $item) $items[] = $item;
            }
        }

        return $items;
    }

    private function getsubitems($context, $sidebar) {
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
        if (count($subitems) == 0) return $items;
        if (count($items) > 0) {
            //delete copies
            for ($i = count($items) - 1; $i >= 0; $i--) {
                $id = $items[$i]['id'];
                foreach ($subitems as $subitem) {
                    if ($id == $subitem['id']) array_delete($items, $i);
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
                array_insert($items, $item, $order);
            }
        }

        return $items;
    }

    protected function getsidebarcontent(array $items, $sidebar, $disableajax) {
        $result = '';
        foreach ($items as $item) {
            $id = $item['id'];
            if (!isset($this->items[$id])) continue;
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
                        $content = twidgetscache::i()->getcontent($id, $sidebar, false);
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

    public function getajax($id, $sidebar) {
        $theme = ttheme::i();
        $title = $theme->getajaxtitle($id, $this->items[$id]['title'], $sidebar, 'ajaxwidget');
        $content = "<!--widgetcontent-$id-->";
        return $theme->getidwidget($id, $title, $content, $this->items[$id]['template'], $sidebar);
    }

    public function getinline($id, $sidebar) {
        $theme = ttheme::i();
        $title = $theme->getajaxtitle($id, $this->items[$id]['title'], $sidebar, 'inlinewidget');
        if ('cache' == $this->items[$id]['cache']) {
            $cache = twidgetscache::i();
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
        if (!litepubl::$urlmap->cache->exists($filename)) {
            $widget = $this->getwidget($id);
            $content = $widget->getcontent($id, $sidebar);
            litepubl::$urlmap->cache->set($filename, $content);
        }

        $theme = ttheme::i();
        return $theme->getidwidget($id, $this->items[$id]['title'], "\n<?php echo litepubl::\$urlmap->cache->get('$filename'); ?>\n", $this->items[$id]['template'], $sidebar);
    }

    private function getcode($id, $sidebar) {
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
            if ($class == $item['class']) return $id;
        }
        return false;
    }

    public function xmlrpcgetwidget($id, $sidebar, $idurl) {
        if (!isset($this->items[$id])) return $this->error("Widget $id not found");
        $this->idurlcontext = $idurl;
        $result = $this->getwidgetcontent($id, $sidebar);
        //fix bug for javascript client library
        if ($result == '') return 'false';
    }

    private static function getget($name) {
        return isset($_GET[$name]) ? (int)$_GET[$name] : false;
    }

    private static function error_request($s) {
        return '<?php header(\'HTTP/1.1 400 Bad Request\', true, 400); ?>' . turlmap::htmlheader(false) . $s;
    }

    public function request($arg) {
        $this->cache = false;
        $id = static ::getget('id');
        $sidebar = static ::getget('sidebar');
        $this->idurlcontext = static ::getget('idurl');
        if (($id === false) || ($sidebar === false) || !$this->itemexists($id)) return $this->error_request('Invalid params');
        $themename = isset($_GET['themename']) ? trim($_GET['themename']) : tview::i(1)->themename;
        if (!preg_match('/^\w[\w\.\-_]*+$/', $themename) || !ttheme::exists($themename)) $themename = tviews::i(1)->themename;
        $theme = ttheme::getinstance($themename);

        try {
            $result = $this->getwidgetcontent($id, $sidebar);
            return turlmap::htmlheader(false) . $result;
        }
        catch(Exception $e) {
            return $this->error_request('Cant get widget content');
        }
    }

    public function getwidgetcontent($id, $sidebar) {
        if (!isset($this->items[$id])) {
            return false;

        }

        switch ($this->items[$id]['cache']) {
            case 'cache':
                $cache = twidgetscache::i();
                $result = $cache->getcontent($id, $sidebar);
                break;


            case 'include':
                $filename = twidget::getcachefilename($id, $sidebar);
                $result = litepubl::$urlmap->cache->get($filename);
                if (!$result) {
                    $widget = $this->getwidget($id);
                    $result = $widget->getcontent($id, $sidebar);
                    litepubl::$urlmap->cache->set($filename, $result);
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

    public function getpos($id) {
        return tsidebars::getpos($this->sidebars, $id);
    }

    public function &finditem($id) {
        foreach ($this->classes as $class => $items) {
            foreach ($items as $i => $item) {
                if ($id == $item['id']) return $this->classes[$class][$i];
            }
        }
        $item = null;
        return $item;
    }

} //class