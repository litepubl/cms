<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\xmlrpc;

use litepubl\post\Post;

class Common extends \litepubl\core\Events
{

    public function uninstall()
    {
        $caller = Server::i();
        $caller->deleteclass(get_class($this));
    }

    public static function auth($email, $password, $group)
    {
        if ($this->getApp()->options->auth($email, $password)) {
            if ($this->getApp()->options->hasgroup($group)) {
                return true;
            }
        }
        throw new Exception('Bad login/pass combination.', 403);
    }

    public static function canedit($email, $password, $idpost)
    {
        if ($this->getApp()->options->auth($email, $password)) {
            if ($this->getApp()->options->hasgroup('editor')) {
                return true;
            }

            if ($this->getApp()->options->hasgroup('author')) {
                if ($idpost == 0) {
                    return true;
                }

                $post = Post::i($idpost);
                return $post->author == $this->getApp()->options->user;
            }
        }
        throw new Exception('Bad login/pass combination.', 403);
    }

    public static function xerror($code, $msg)
    {
        return new IXR_Error($code, $msg);
    }

}

