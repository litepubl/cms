<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Args;

class tadminsapeplugin extends tadminwidget {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->widget = tsapeplugin::i();
    }

    public function getContent() {
        $result = '';
        $widget = $this->widget;
        $args = new Args();
        if ($widget->id != 0) {
            $args->maxcount = $widget->counts[$widget->id];
            $result.= $this->optionsform($this->widget->gettitle($this->widget->id) , $this->html->parsearg('[text=maxcount]', $args));
        }

        $args->user = $widget->user;
        $args->force = $widget->force;
        $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'sapeform.tml');
        $result.= $this->html->parsearg($tml, $args);
        return $result;
    }

    protected function doProcessForm(twidget $widget) {
        extract($_POST, EXTR_SKIP);
        if (isset($addwidget)) {
            $widget->add();
        } elseif (isset($sapeoptions)) {
            $widget->user = $user;
            $widget->force = isset($force);
        } else {
            $widget->counts[$widget->id] = (int)$maxcount;
        }
    }

}