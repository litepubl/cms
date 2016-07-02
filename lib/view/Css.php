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


namespace litepubl\view;

use litepubl\core\Str;

class Css extends Merger
{

    protected function create()
    {
        parent::create();
        $this->basename = 'cssmerger';
    }

    public function replaceUrl($m)
    {
        $url = $m[1];
        if (Str::begin($url, 'data:')) {
            return " url(\"$url\")";
        }

        $args = '';
        if ($i = strpos($url, '?')) {
            $args = substr($url, $i);
            $url = substr($url, 0, $i);
        }

        // else must be absolute url
        if ($realfile = realpath($url)) {
            $url = substr($realfile, strlen($this->getApp()->paths->home));
        }

        $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
        $url = $this->getApp()->site->files . '/' . ltrim($url, '/');
        $url = substr($url, strpos($url, '/', 9));
        return " url('$url$args')";
    }

    public function readFile($filename)
    {
        if ($result = parent::readfile($filename)) {
            chdir(dirname($filename));
            $result = preg_replace_callback('/\s*url\s*\(\s*[\'"]?(.*?)[\'"]?\s*\)/i', [$this, 'replaceurl'], $result);
            //delete comments
            return preg_replace('/\/\*.*?\*\//ims', '', $result);
        }
    }

    public function getFileName($section, $revision)
    {
        return sprintf('/files/js/%s.%s.css', $section, $revision);
    }

    public function addStyle($filename)
    {
        if (!($filename = $this->normfilename($filename))) {
            return false;
        }

        $template = MainView::i();
        if (strpos($template->heads, $this->basename . '_default')) {
            $this->add('default', $filename);
        } else {
            $template->addtohead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
        }
    }

    public function deleteStyle($filename)
    {
        if (!($filename = $this->normfilename($filename))) {
            return false;
        }

        $template = MainView::i();
        if (strpos($template->heads, $this->basename . '_default')) {
            $this->deletefile('default', $filename);
        } else {
            $template->deletefromhead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
        }
    }
}