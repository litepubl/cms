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


namespace litepubl\admin\widget;

use litepubl\admin\Link;
use litepubl\core\PropException;

class Widget extends \litepubl\admin\Panel
{
    public $widget;

    public function __construct()
    {
        parent::__construct();
        $this->lang->section = 'widgets';
    }

    public function __get($name)
    {
        if (method_exists($this, $get = 'get' . $name)) {
            return $this->$get();
        }

        throw new PropException(get_class($this), $name);
    }

    protected function getAdminurl()
    {
        return Link::url('/admin/views/widgets/?idwidget=');
    }

    protected function getForm()
    {
        $title = $this->widget->gettitle($this->widget->id);
        $this->args->title = $title;
        $this->args->formtitle = $title . ' ' . $this->lang->widget;
        return $this->theme->getinput('text', 'title', $title, $this->lang->widgettitle);
    }

    public function getContent(): string
    {
        $form = $this->getForm();
        return $this->admin->form($form, $this->args);
    }

    public function processForm()
    {
        $widget = $this->widget;
        $widget->lock();
        if (isset($_POST['title'])) {
            $widget->settitle($widget->id, $_POST['title']);
        }

        $this->doProcessForm();
        $widget->unlock();
        return $this->admin->success($this->lang->updated);
    }

    protected function doProcessForm()
    {
    }
}