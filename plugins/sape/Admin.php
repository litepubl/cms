<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\sape;

use litepubl\admin\Form;

class Admin extends \litepubl\admin\widget\Widget
{

    public function __construct()
    {
        parent::__construct();
        $this->widget = Widget::i();
    }

    public function getContent(): string
    {
        $result = '';
        $form = '';
        $widget = $this->widget;
        $lang = $this->getLangAbout();
        $args = $this->args;

        if ($widget->id != 0) {
            $args->maxcount = $widget->counts[$widget->id];
            $form = parent::getForm();
            $forrm .= '[text=maxcount]';
        }

        $args->user = $widget->user;
        $args->force = $widget->force;
        $args->sapeoptions = 1;
        $args->formtitle = $lang->formtitle;

        $form .= '
[text=user]
[checkbox=force]
[hidden=sapeoptions]
';

        $result .= $this->admin->form($form, $args);
        $addform = new Form($args);
        $addform->title = $lang->addtitle;
        $addform->submit = 'addwidget';

        $result .= $addform->get();
        return $result;
    }

    protected function doProcessForm()
    {
        extract($_POST, EXTR_SKIP);
        $widget = $this->widget;
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
