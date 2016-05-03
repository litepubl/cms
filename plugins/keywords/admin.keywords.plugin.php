<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Str;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\core\Plugins;

class tadminkeywords extends tadminwidget {

    public static function i() {
        return static::iGet(__class__);
    }

    public function getContent() {
        $datadir =  $this->getApp()->paths->data . 'keywords' . DIRECTORY_SEPARATOR;
        $selfdir = dirname(__file__) . DIRECTORY_SEPARATOR;
        $tml = parse_ini_file($selfdir . 'keywords.templates.ini', false);
        $about = Plugins::getabout(Plugins::getname(__file__));
        $html = $this->html;
        $lang = $this->lang;
        $args = new Args();
        if (isset($_GET['filename'])) {
            $filename = $_GET['filename'];
            if (!@file_exists($datadir . $filename)) {
 return $html->h3->notfound;
}


            $args->filename = $filename;
            $args->content = file_get_contents($datadir . $filename);
            $args->formtitle = $about['edithead'];
            return $html->adminform('[editor=content]', $args);
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $result = '';
        if ($page == 1) {
            $widget = tkeywordswidget::i();
            $widgets = twidgets::i();
            $idwidget = $widgets->find($widget);
            $args->count = $widget->count;
            $args->trace = $widget->trace;
            $args->notify = $widget->notify;
            $args->optionsform = 1;
            $args->title = $widget->gettitle($idwidget);
            $args->blackwords = tadminhtml::specchars(implode("\n", tkeywordsplugin::i()->blackwords));
            $lang = Plugins::getlangabout(__file__);
            $args->formtitle = $about['name'];
            $result.= $html->adminform('[text=title]
      [text=count]
      [checkbox=trace]
      [checkbox=notify]
      [editor=blackwords]
      [hidden=optionsform]', $args);
        }

        $from = 100 * ($page - 1);
        $filelist = tfiler::getfiles($datadir);
        sort($filelist);
        $count = ceil(count($filelist) / 100);
        $links = $this->getlinkpages($page, $count);
        $result.= $links;
        $filelist = array_slice($filelist, $from, 100, true);
        $list = '';
        $args->url =  $this->getApp()->site->url . '/admin/plugins/' .  $this->getApp()->site->q . 'plugin=' . basename(dirname(__file__));
        foreach ($filelist as $filename) {
            if (!preg_match('/^\d+?\.\d+?\.php$/', $filename)) {
 continue;
}


            $args->filename = $filename;
            $args->content = file_get_contents($datadir . $filename);
            $list.= $html->parseArg($tml['item'], $args);
        }

        $args->list = $list;
        $result.= $html->parseArg($tml['form'], $args);
        $result.= $links;
        return $result;
    }

    private function getLinkpages($page, $count) {
        $url =  $this->getApp()->site->url . '/admin/plugins/' .  $this->getApp()->site->q . 'plugin=' . basename(dirname(__file__));
        $result = "<a href='$url'>1</a>\n";
        for ($i = 2; $i <= $count; $i++) {
            $result.= "<a href='$url&page=$i'>$i</a>|\n";
        }
        return sprintf("<p>\n%s</p>\n", $result);
    }

    public function processForm() {
        $datadir =  $this->getApp()->paths->data . 'keywords' . DIRECTORY_SEPARATOR;
        if (isset($_POST['optionsform'])) {
            extract($_POST, EXTR_SKIP);
            $plugin = tkeywordsplugin::i();
            $widget = tkeywordswidget::i();
            $widgets = twidgets::i();
            $idwidget = $widgets->find($widget);
            $widget->lock();
            $widget->settitle($idwidget, $title);
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
            if ( $this->getApp()->options->language != 'en') {
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
            if (preg_match('/^\d+?\.\d+?\.php$/', $filename)) unlink($datadir . $filename);
        }
    }

}