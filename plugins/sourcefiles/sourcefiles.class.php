<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tsourcefiles extends tplugin implements itemplate {
    public $item;
    public $geshi;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->data['url'] = '/source/';
        $this->data['zipurl'] = '';
        $this->data['idschema'] = 1;
    }

    public function getDir() {
        return  $this->getApp()->paths->data . 'sourcecache';
    }

    public function getFilename($url) {
        return $this->dir . '/' . md5($url) . '.txt';
    }

    public function clear() {
        tfiler::delete($this->dir, true, false);
    }

    public function loaditem($filename) {
        if (!file_exists($filename)) {
 return false;
}


        $s = file_get_contents($filename);
        if (!$s) {
 return false;
}



        return unserialize($s);
    }

    public function saveitem($filename, $data) {
        file_put_contents($filename, serialize($data));
        @chmod($filename, 0666);
    }

    public function request($arg) {
        $url = substr( $this->getApp()->router->url, strlen($this->url));
        $url = trim($url, '/');
        if (!$url) $url = '.';

        if (!($this->item = $this->loaditem($this->getfilename($url)))) {
            while ($url && $url != '.') {
                $url = dirname($url);
                if ($url == '.') {
                    return  $this->getApp()->router->redir($this->url);
                } else if (file_exists($this->getfilename($url))) {
                    return  $this->getApp()->router->redir($this->url . $url . '/');
                }
            }

            return 404;
        }

    }

    public function getIdschema() {
        return $this->data['idschema'];
    }

    public function setIdschema($id) {
        if ($id != $this->idschema) {
            $this->data['idschema'] = $id;
            $this->save();
        }
    }

    public function getView() {
        return Schema::getview($this);
    }

    public function getTitle() {
        return $this->item['filename'];
    }

    public function getKeywords() {
    }
    public function getDescription() {
    }
    public function getHead() {
        if ($this->item['style']) {
            return sprintf('<style type="text/css">%s</style>', $this->item['style']);
        }
    }

    public function getCont() {
        $result = sprintf('<h4>%s</h4>', $this->item['filename']);
        if ($this->item['type'] == 'file') {
            $dir = dirname($this->item['filename']);
            if ($item = $this->loaditem($this->getfilename($dir))) {
                $result.= $item['content'];
            }
        }

        $result.= $this->item['content'];
        return $this->view->theme->simple($result);
    }

    public function creategeshi() {
        if (!isset($this->geshi)) {
            define('GESHI_ROOT', dirname(__file__) . '/');
            require (dirname(__file__) . '/geshi.php');
            $this->geshi = new \GeSHi();
            $this->geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
            $this->geshi->enable_classes();
            //$this->geshi->enable_keyword_links(false);
            
        }
    }

    public function syntax($ext, $content) {
        /*
        if ($ext == 'php') {
        return array(
        'content' => highlight_string ($content, true),
        'style' => ''
        );
        }
        */
        switch ($ext) {
            case 'tml':
                $lang = 'html5';
                break;


            case 'less':
                $lang = 'css';
                break;


            case 'js':
                $lang = 'jquery';
                break;


            case 'json':
                $lang = 'javascript';
                break;


            default:
                $lang = $this->geshi->get_language_name_from_extension($ext);
        }

        $this->geshi->set_language($lang);
        $this->geshi->set_source($content);
        return array(
            'content' => $this->geshi->parse_code() ,
            'style' => $this->geshi->get_stylesheet()
        );
    }

    public function readzip($zipname) {
        $zip = new \zipArchive();
        if ($zip->open($zipname) !== true) {
            $this->error(sprintf('Error open "%s" zip archive', $zipname));
        }

        $this->creategeshi();
        $dirlist = array();
        $root = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (preg_match('/\.(min\.js|min\.css|jpg|jpeg|ico|png|gif|svg|swf|xap|otf|eot|ttf|woff|woff2)$/', $filename)) {
 continue;
}



            if (!$root) {
                $list = explode('/', trim($filename, '/'));
                $root = $list[0];
            }

            $filename = ltrim(substr(ltrim($filename, '/') , strlen($root)) , '/');
            $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
            $content = trim($zip->getFromIndex($i));
            if (!$content) {
 continue;
}



            $path = dirname($filename);
            if (isset($dirlist[$path])) {
                $dirlist[$path][] = basename($filename);
            } else {
                $dirlist[$path] = array(
                    basename($filename)
                );
            }

            $item = $this->syntax($ext, $content);
            $item['type'] = 'file';
            $item['filename'] = $filename;

            $this->saveitem($this->getfilename($filename) , $item);
        }

        $zip->close();

        $dirnames = array_keys($dirlist);
        //force dir tree
        foreach ($dirnames as $dirname) {
            $list = explode('/', $dirname);
            $dir = '';
            foreach ($list as $name) {
                if ($dir) $dir.= '/';
                $dir.= $name;
                if (!isset($dirlist[$dir])) {
                    $dirlist[$dir] = array();
                }
            }
        }

        $tml = '<li><a href="' .  $this->getApp()->site->url . $this->url . '%s">%s</a></li>';
        $tml_list = '<ul>%s</ul>';
        $dirnames = array_keys($dirlist);
        foreach ($dirlist as $dir => $filelist) {
            $list = '';
            if ($dir == '.') {
                $basedir = '';
            } else {
                $basedir = $dir . '/';
                $list.= sprintf($tml, dirname($dir) == '.' ? '' : dirname($dir) . '/', '..');
            }

            $subdirs = array();
            foreach ($dirnames as $i => $subdir) {
                if (($dir != $subdir) && ($dir == dirname($subdir))) {
                    $subdirs[] = basename($subdir);
                    unset($dirnames[$i]);
                }
            }

            sort($subdirs, SORT_NATURAL);
            foreach ($subdirs as $subdir) {
                $list.= sprintf($tml, $basedir . $subdir . '/', strtoupper($subdir));
            }

            sort($filelist, SORT_NATURAL);
            foreach ($filelist as $filename) {
                $list.= sprintf($tml, $basedir . $filename, $filename);
            }

            $this->saveitem($this->getfilename($dir) , array(
                'type' => 'dir',
                'filename' => $dir == '.' ? $root : $dir,
                'content' => sprintf($tml_list, $list) ,
                'style' => '',
            ));
        }
    }

} //class