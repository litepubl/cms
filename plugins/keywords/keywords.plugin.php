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

class tkeywordsplugin extends tplugin {
    public $blackwords;

    public static function i() {
        return getinstance(__class__);
    }

    public function create() {
        parent::create();
        $this->addmap('blackwords', array());
    }

    public function urldeleted($id) {
        tfiler::deletemask( $this->getApp()->paths->data . 'keywords' . DIRECTORY_SEPARATOR . $item['id'] . ".*.php");
    }

    public function parseref($url) {
        if (Str::begin($url, '/admin/') || Str::begin($url, '/croncron.php')) {
 return;
}


        $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (empty($ref)) {
 return;
}


        $urlarray = parse_url($ref);
        if ($urlarray['scheme'] !== 'http') {
 return;
}


        $host = $urlarray['host'];
        if (($host == 'search.msn.com') || is_int(strpos($host, '.google.'))) {
            parse_str($urlarray['query']);
            $keywords = $q;
            if (isset($ie) && ($ie == 'windows-1251')) {
                $keywords = @iconv("windows-1251", "utf-8", $keywords);
            }
        } elseif ($host == 'www.rambler.ru') {
            parse_str($urlarray['query']);
            $keywords = @iconv("windows-1251", "utf-8", $words);
        } elseif (($host == 'www.yandex.ru') || ($host == 'yandex.ru')) {
            parse_str($urlarray['query']);
            $keywords = $text;
        } else {
            return;
        }

        $keywords = trim($keywords);
        if (empty($keywords)) {
 return;
}



        $c = substr_count($keywords, chr(208));
        if (($c < 3) && $this->hasru($keywords)) {
            $keywords = @iconv('windows-1251', 'utf-8', $keywords);
        }

        $keywords = trim($keywords);
        if (empty($keywords)) {
 return;
}


        if (strlen($keywords) <= 5) {
 return;
}


        foreach (array(
            'site:',
            'inurl:',
            'link:',
            '%',
            '@',
            '<',
            '>',
            'intext:',
            'http:',
            'ftp:',
            '\\'
        ) as $k) {
            if (false !== strpos($keywords, $k)) {
 return;
}


        }

        if ($this->inblack($keywords)) {
 return;
}


        $keywords = htmlspecialchars($keywords, ENT_QUOTES);

        //$link =" <a href=\"http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]\">$keywords</a>";
        $widget = tkeywordswidget::i();
        //if (in_array($link, $widget->links)) return;
        foreach ($widget->links as $item) {
            if ($keywords == $item['text']) {
 return;
}


        }
        $widget->links[] = array(
            'url' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'text' => $keywords
        );

        $widget->save();
    }

    private function hasru($s) {
        return preg_match('/[�-��-�]{1,}/', $s);
    }

    public function added($filename, $content) {
        $filename = basename($filename);
        $site =  $this->getApp()->site;
        $subject = "[$site->name] new keywords added";
        $body = "The new widget has been added on\n$site->url{$_SERVER['REQUEST_URI']}\n\nWidget content:\n\n$content\n\nYou can edit this links at:\n$site->url/admin/plugins/{$site->q}plugin=keywords&filename=$filename\n";

        tmailer::sendmail($site->name,  $this->getApp()->options->fromemail, 'admin',  $this->getApp()->options->email, $subject, $body);
    }

    public function inblack($s) {
        if ( $this->getApp()->options->language != 'en') {
            Lang::usefile('translit');
            $s = strtr($s, Lang::$self->ini['translit']);
        }
        $s = strtolower($s);
        foreach ($this->blackwords as $word) {
            if (false !== strpos($s, $word)) {
 return true;
}


        }
        return false;
    }

} //class