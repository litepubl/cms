<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tsapeplugin extends twidget {
    public $sape;
    public $counts;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'widget.sape';
        $this->cache = 'nocache';
        $this->adminclass = 'tadminsapeplugin';
        $this->data['user'] = '';
        $this->data['count'] = 2;
        $this->data['force'] = false;
        $this->addmap('counts', array());
    }

    public function getDeftitle() {
        return Lang::get('default', 'links');
    }

    private function createsape() {
        if (!defined('_SAPE_USER')) {
            define('_SAPE_USER', $this->user);
             $this->getApp()->classes->include_file( $this->getApp()->paths->plugins . 'sape' . DIRECTORY_SEPARATOR . 'sape.php');
            $o['charset'] = 'UTF-8';
            $o['multi_site'] = true;
            if ($this->force) $o['force_show_code'] = $this->force;
            $this->sape = new \SAPE_client($o);
        }
    }

    public function getWidget($id, $sidebar) {
        if ($this->user == '') {
 return '';
}


        if ( $this->getApp()->router->is404 ||  $this->getApp()->router->adminpanel) {
 return '';
}


        return parent::getwidget($id, $sidebar);
    }

    public function getContent($id, $sidebar) {
        $links = $this->getlinks();
        if (empty($links)) {
 return '';
}


        return sprintf('<ul><li>%s</li></ul>', $links);
    }

    public function getCont() {
        return $this->getcontent(0, 0);
    }
    public function getLinks() {
        if ($this->user == '') {
 return '';
}


        if ( $this->getApp()->router->is404 ||  $this->getApp()->router->adminpanel) {
 return '';
}


        if (!isset($this->sape)) $this->createsape();
        return $this->sape->return_links($this->counts[$id]);
    }

    public function setCount($id, $count) {
        $this->counts[$id] = $count;
        $widgets = twidgets::i();
        foreach ($this->counts as $id => $count) {
            if (!isset($widgets->items[$id])) unset($this->counts[$id]);
        }
        $this->save();
    }

    public function add() {
        $id = $this->addtosidebar(0);
        $this->counts[$id] = 10;
        $this->save();
        return $id;
    }

} //class

?>