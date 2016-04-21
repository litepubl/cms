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
use litepubl\core\Plugins;

class tsimpleimporter extends timporter {
    public $tagsmap;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->data['script'] = '';
        $this->addmap('tagsmap', array(
            'title' => 'title',
            'link' => 'link',
            'pubDate' => 'pubdate',
            'content:encoded' => 'content'
        ));
    }

    public function getContent() {
        $result = parent::getcontent();
        $tagsmap = '';
        foreach ($this->tagsmap as $key => $val) {
            $tagsmap.= "$key = $val\n";
        }
        $args = new Args();
        $args->tagsmap = $tagsmap;
        $args->script = $this->script;
        $about = Plugins::getabout(Plugins::getname(__file__));
        $args->maplabel = $about['maplabel'];
        $args->scriptlabel = $about['scriptlabel'];
        $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'form.tml');
        $html = tadminhtml::i();
        $result.= $html->parsearg($tml, $args);
        return $result;
    }

    public function processForm() {
        if ($_POST['form'] != 'options') {
 return parent::ProcessForm();
}


        $this->parsemap($_POST['tagsmap']);
        $this->script = $_POST['script'];
        $this->save();
    }

    public function parsemap($s) {
        $this->tagsmap = array();
        $lines = explode("\n", $s);
        foreach ($lines as $line) {
            if ($i = strpos($line, '=')) {
                $key = trim(substr($line, 0, $i));
                $val = trim(substr($line, $i + 1));
                $this->tagsmap[$key] = $val;
            }
        }
    }

    public function import($s) {
        require_once ( $this->getApp()->paths->lib . 'domrss.class.php');
        $a = xml2array($s);

        $router = \litepubl\core\Router::i();
        $router->lock();
        $cats = tcategories::i();
        $cats->lock();
        $tags = ttags::i();
        $tags->lock();
        $posts = tposts::i();
        $posts->lock();
        foreach ($a['rss']['channel'][0]['item'] as $item) {
            $post = $this->add($item);
            $posts->add($post);
            $post->free();
        }
        $posts->unlock();
        $tags->unlock();
        $cats->unlock();
        $router->unlock();
    }

    public function add(array $item) {
        $post = tpost::i();
        foreach ($this->tagsmap as $key => $val) {
            if (isset($item[$key])) {
                $post->{$val} = $item[$key];
            }
        }
        if ($this->script != '') eval($this->script);
        return $post;
    }

} //class