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


namespace litepubl;

use litepubl\admin\Form;
use litepubl\admin\Link;
use litepubl\core\Str;
use litepubl\utils\Filer;
use litepubl\view\Lang;
use litepubl\widget\Widgets;

class Admin extends \litepubl\admin\widget\Widget
{

    public function getContent(): string
    {
        $datadir = $this->getApp()->paths->data . 'keywords' . DIRECTORY_SEPARATOR;
        $selfdir = __DIR__ . DIRECTORY_SEPARATOR;
        $admin = $this->admin;
        $lang = $this->getLangAbout();
        $args = $this->args;
        if (isset($_GET['filename'])) {
            $filename = $_GET['filename'];
            if (!@file_exists($datadir . $filename)) {
                return $admin->geterr($lang->notfound);
            }

            $args->filename = $filename;
            $args->content = file_get_contents($datadir . $filename);
            $args->formtitle = $lang->edithead;
            return $admin->form(
                '
[editor=content]
', $args
            );
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $result = '';
        if ($page == 1) {
            $widget = Widget::i();
            $widgets = Widgets::i();
            $idwidget = $widgets->find($widget);
            $args->count = $widget->count;
            $args->trace = $widget->trace;
            $args->notify = $widget->notify;
            $args->optionsform = 1;
            $args->title = $widget->gettitle($idwidget);
            $args->blackwords = $admin->quote(implode("\n", tkeywordsplugin::i()->blackwords));
            $args->formtitle = $lang->name;
            $result.= $admin->form(
                '
[text=title]
      [text=count]
      [checkbox=trace]
      [checkbox=notify]
      [editor=blackwords]
      [hidden=optionsform]
', $args
            );
        }

        $from = 100 * ($page - 1);
        $filelist = Filer::getFiles($datadir);
        sort($filelist);
        $count = count($filelist);
        $pages = ceil($count / 100);
        $filelist = array_slice($filelist, $from, 100, true);
        $form = new Form($this->args);
        $form->class = 'header-left';
        $form->body = $admin->getCount($from, $from + count($filelist), $count);

        $tml =strtr(
            $admin->templates['checkbox.name'], [
            '$name' => '$filename',
            '$checked'=> '',
            ]
        );

        $tml .= Link::parse('href=/admin/plugins/?plugin=keywords&filename=$filename, text=' . $lang->edit);


        foreach ($filelist as $filename) {
            if (!preg_match('/^\d+?\.\d+?\.php$/', $filename)) {
                continue;
            }

            $form->body .= $admin->getSection(
                str_replace('$filename', $filename, $tml),
                sprintf('<ul>%s</ul>', file_get_contents($datadir . $filename))
            );
        }

        $links = $this->getLinkPages($page, $pages);
        $result.= $links;
        $form->submit = 'delete';
        $result.= $form->get();
        $result.= $links;
        return $result;
    }

    private function getLinkPages($page, $count)
    {
        $url = $this->getApp()->site->url . '/admin/plugins/' . $this->getApp()->site->q . 'plugin=' . basename(dirname(__file__));
        $result = "<a href='$url'>1</a>\n";
        for ($i = 2; $i <= $count; $i++) {
            $result.= "<a href='$url&page=$i'>$i</a>|\n";
        }
        return sprintf("<p>\n%s</p>\n", $result);
    }

    public function processForm()
    {
        $datadir = $this->getApp()->paths->data . 'keywords' . DIRECTORY_SEPARATOR;
        if (isset($_POST['optionsform'])) {
            extract($_POST, EXTR_SKIP);
            $plugin = Keywords::i();
            $widget = Widget::i();
            $widgets = Widgets::i();
            $idwidget = $widgets->find($widget);
            $widget->lock();
            $widget->setTitle($idwidget, $title);
            $widget->count = (int)$count;
            $widget->notify = isset($notify);
            $trace = isset($trace);
            if ($widget->trace != $trace) {
                if ($trace) {
                    $this->getApp()->router->afterrequest = $plugin->parseref;
                } else {
                    $this->getApp()->router->delete_event_class('afterrequest', get_class($plugin));
                }
            }

            $widget->trace = $trace;
            $widget->unlock();

            $plugin->blackwords = array();
            $words = Str::toArray($blackwords);
            if ($this->getApp()->options->language != 'en') {
                Lang::usefile('translit');
                foreach ($words as $word) {
                    $word = strtr($word, Lang::$self->ini['translit']);
                    $word = trim($word);
                    if (empty($word)) {
                        continue;
                    }

                    $plugin->blackwords[] = strtolower($word);
                }
            }
            $plugin->save();
            return;
        }

        if (isset($_GET['filename'])) {
            $filename = str_replace('_', '.', $_GET['filename']);
            $content = trim($_POST['content']);
            if ($content == '') {
                @unlink($datadir . $filename);
            } else {
                file_put_contents($datadir . $filename, $content);
            }
            return;
        }

        foreach ($_POST as $filename => $value) {
            $filename = str_replace('_', '.', $filename);
            if (preg_match('/^\d+?\.\d+?\.php$/', $filename)) {
                unlink($datadir . $filename);
            }
        }
    }
}
