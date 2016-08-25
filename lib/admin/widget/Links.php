<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\admin\widget;

class Links extends Widget
{
    use \litepubl\admin\Factory;

    protected function getForm(): string
    {
        $this->args->redir = $this->widget->redir;
        return parent::getForm()
        . '[checkbox=redir]';
    }

    public function getContent(): string
    {
        $result = parent::getcontent();
        $widget = $this->widget;
        $args = $this->args;
        $id = isset($_GET['idlink']) ? (int)$_GET['idlink'] : 0;
        if (isset($widget->items[$id])) {
            $item = $widget->items[$id];
            $args->mode = 'edit';
        } else {
            $args->mode = 'add';
            $item = [
                'url' => '',
                'linktitle' => '',
                'text' => ''
            ];
        }

        $args->add($item);
        $args->linktitle = isset($item['title']) ? $item['title'] : (isset($item['linktitle']) ? $item['linktitle'] : '');
        $lang = $this->lang;
        $args->formtitle = $lang->editlink;
        $result.= $this->admin->form(
            '
    [text=url]
    [text=text]
    [text=linktitle]
    [hidden=mode]', $args
        );

        $adminurl = $this->adminurl . intval($_GET['idwidget']) . '&idlink';
        $tb = $this->newTable($this->admin);
        $tb->setStruct(
            [
            $tb->checkbox('checklink') ,
            [
                $lang->url,
                '<a href=\'$url\'>$url</a>'
            ] ,
            [
                $lang->anchor,
                '$text'
            ] ,
            [
                $lang->description,
                '$title'
            ] ,
            [
                $lang->edit,
                "<a href='$adminurl=\$id'>$lang->edit</a>"
            ] ,
            ]
        );

        $form = $this->newForm($args);
        $form->title = $lang->widgets;
        $result.= $form->getdelete($tb->build($widget->items));
        return $result;
    }

    public function processForm()
    {
        $widget = $this->widget;
        $widget->lock();
        if (isset($_POST['delete'])) {
            foreach ($_POST as $key => $value) {
                $id = (int)$value;
                if (isset($widget->items[$id])) {
                    $widget->delete($id);
                }
            }
        } elseif (isset($_POST['mode'])) {
            extract($_POST, EXTR_SKIP);
            switch ($mode) {
            case 'add':
                $_GET['idlink'] = $widget->add($url, $linktitle, $text);
                break;


            case 'edit':
                $widget->edit((int)$_GET['idlink'], $url, $linktitle, $text);
                break;
            }
        } else {
            extract($_POST, EXTR_SKIP);
            $widget->settitle($widget->id, $title);
            $widget->redir = isset($redir);
        }
        $widget->unlock();
        return $this->admin->success($this->lang->updated);
    }
}
