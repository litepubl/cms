<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\plugins\sape;

use litepubl\view\Lang;

class Widget extends \litepubl\widget\Widget
{
    public $sape;
    public $counts;

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.sape';
        $this->cache = 'nocache';
        $this->adminclass = 'tadminsapeplugin';
        $this->data['user'] = '';
        $this->data['count'] = 2;
        $this->data['force'] = false;
        $this->addmap('counts', array());
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'links');
    }

    private function createsape()
    {
        if (!defined('_SAPE_USER')) {
            define('_SAPE_USER', $this->user);
            include_once __DIR__ . '/sape.php';
            $o['charset'] = 'UTF-8';
            $o['multi_site'] = true;
            if ($this->force) {
                $o['force_show_code'] = $this->force;
            }
            $this->sape = new \SAPE_client($o);
        }
    }

    public function getValid(): bool
    {
        $app = $this->getApp();
        return $this->user
        && $app->context->response->status == 200
         && !$app->context->request->isAdminPanel;
    }

    public function getWidget(int $id, int $sidebar): string
    {
        if (!$this->getValid()) {
            return '';
        }

        return parent::getWidget($id, $sidebar);
    }

    public function getContent(int $id, int $sidebar): string
    {
        $links = $this->getLinks();
        if (empty($links)) {
            return '';
        }

        return sprintf('<ul><li>%s</li></ul>', $links);
    }

    public function getCont()
    {
        return $this->getcontent(0, 0);
    }

    public function getLinks()
    {
        if (!$this->getValid()) {
            return '';
        }

        if (!isset($this->sape)) {
            $this->createsape();
        }
        return $this->sape->return_links($this->counts[$id]);
    }

    public function setCount(int $id, int $count)
    {
        $this->counts[$id] = $count;
        $widgets = $this->gettWidgets();

        foreach ($this->counts as $id => $count) {
            if (!isset($widgets->items[$id])) {
                unset($this->counts[$id]);
            }
        }

        $this->save();
    }

    public function add()
    {
        $id = $this->addToSidebar(0);
        $this->counts[$id] = 10;
        $this->save();
        return $id;
    }
}
