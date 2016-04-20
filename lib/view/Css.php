<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;
use litepubl\core\Str;

class Css extends Js
 {

    protected function create() {
        parent::create();
        $this->basename = 'cssmerger';
    }

    public function replaceurl($m) {
        $url = $m[1];
        if (Str::begin($url, 'data:')) {
 return " url(\"$url\")";
}



        $args = '';
        if ($i = strpos($url, '?')) {
            $args = substr($url, $i);
            $url = substr($url, 0, $i);
        }

        if ($realfile = realpath($url)) {
            $url = substr($realfile, strlen( $this->getApp()->paths->home));
        } // else must be absolute url
        $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        $url =  $this->getApp()->site->files . '/' . ltrim($url, '/');
        $url = substr($url, strpos($url, '/', 9));
        return " url('$url$args')";
    }

    public function readfile($filename) {
        if ($result = parent::readfile($filename)) {
            chdir(dirname($filename));
            $result = preg_replace_callback('/\s*url\s*\(\s*[\'"]?(.*?)[\'"]?\s*\)/i', array(
                $this,
                'replaceurl'
            ) , $result);
            //delete comments
            $result = preg_replace('/\/\*.*?\*\//ims', '', $result);
            return $result;
        }
    }

    public function getFilename($section, $revision) {
        return sprintf('/files/js/%s.%s.css', $section, $revision);
    }

    public function addstyle($filename) {
        if (!($filename = $this->normfilename($filename))) {
 return false;
}


        $template = ttemplate::i();
        if (strpos($template->heads, $this->basename . '_default')) {
            $this->add('default', $filename);
        } else {
            $template->addtohead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
        }
    }

    public function deletestyle($filename) {
        if (!($filename = $this->normfilename($filename))) {
 return false;
}


        $template = ttemplate::i();
        if (strpos($template->heads, $this->basename . '_default')) {
            $this->deletefile('default', $filename);
        } else {
            $template->deletefromhead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
        }
    }

} //class