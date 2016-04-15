<?php

namespace litepubl\xmlrpc;
use litepubl\post\Post;

class Common extends \litepubl\core\Events
 {

    public function uninstall() {
        $caller = Server::i();
        $caller->deleteclass(get_class($this));
    }

    public static function auth($email, $password, $group) {
        if (litepubl::$options->auth($email, $password)) {
            if (litepubl::$options->hasgroup($group)) {
return true;
}
        }
        throw new Exception('Bad login/pass combination.', 403);
    }

    public static function canedit($email, $password, $idpost) {
        if (litepubl::$options->auth($email, $password)) {
            if (litepubl::$options->hasgroup('editor')) {
return true;
}

            if (litepubl::$options->hasgroup('author')) {
                if ($idpost == 0) {
return true;
}

                $post = Post::i($idpost);
                return $post->author == litepubl::$options->user;
            }
        }
        throw new Exception('Bad login/pass combination.', 403);
    }

    public static function xerror($code, $msg) {
        return new IXR_Error($code, $msg);
    }

}