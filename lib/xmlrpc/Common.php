<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
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
        $options = static::getAppInstance()->options;
        if ($options->auth($email, $password)
            && $options->hasGroup($group)
        ) {
                return true;
        }

        throw new Exception('Bad login/pass combination.', 403);
    }

    public static function canEdit($email, $password, $idpost)
    {
        $options = static::getAppInstance()->options;
        if ($options->auth($email, $password)) {
            if ($options->hasGroup('editor')) {
                return true;
            }

            if ($options->hasgroup('author')) {
                if ($idpost == 0) {
                    return true;
                }

                $post = Post::i($idpost);
                return $post->author == $options->user;
            }
        }
        throw new Exception('Bad login/pass combination.', 403);
    }

    public static function xerror($code, $msg)
    {
        return new IXR_Error($code, $msg);
    }
}
