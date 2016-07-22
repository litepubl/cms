<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\xmlrpc;

use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\utils\http;

class Pingback extends Common
{

    public static function i()
    {
        return static ::iGet(__class__);
    }

    public function ping($from, $to)
    {
        if (!Str::begin($to, $this->getApp()->site->url)) {
            return new IXR_Error(0, 'Is there no link to us?');
        }

        $url = substr($to, strlen($this->getApp()->site->url));
        if (!($item = $this->getApp()->router->find_item($url))) {
            return $this->xerror(0, 'Is there no link to us?');
        }

        if ($item['class'] != $this->getApp()->classes->classes['post']) {
            return $this->xerror(33, 'The specified target URL cannot be used as a target. It either doesn\'t exist, or it is not a pingback-enabled resource.');
        }

        $post = Post::i($item['arg']);
        if (!$post->pingenabled || ($post->status != 'published')) {
            return $this->xerror(33, 'The specified target URL cannot be used as a target. It either doesn\'t exist, or it is not a pingback-enabled resource.');
        }

        $pingbacks = $post->pingbacks;
        if ($pingbacks->exists($from)) {
            return new IXR_Error(48, 'The pingback has already been registered.');
        }

        if (!($s = http::get($from))) {
            return new IXR_Error(16, 'The source URL does not exist.');
        }

        $s = str_replace('<!DOC', '<DOC', $s);
        $s = preg_replace('/[\s\r\n\t]+/', ' ', $s); // normalize spaces
        if (!preg_match('|<title>([^<]*?)</title>|is', $s, $matchtitle) || empty($matchtitle[1])) {
            return new IXR_Error(32, 'We cannot find a title on that page.');
        }

        $s = strip_tags($s, '<a>');
        if (!preg_match("|<a([^>]+?" . preg_quote($to) . "[^>]*)>[^>]+?</a>|", $s, $match)) {
            return new IXR_Error(17, 'The source URL does not contain a link to the target URL, and so cannot be used as a source.');
        }

        if (preg_match('/nofollow|noindex/is', $match[1])) {
            return new IXR_Error(32, 'The source URL contain nofollow or noindex atribute');
        }

        $pingbacks->add($from, $matchtitle[1]);

        return "Pingback from $from to $to registered. Keep the web talking! :-)";
    }
}
