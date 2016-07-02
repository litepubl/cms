<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\xmlrpc;

use litepubl\Config;
use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\post\Posts;

class Livejournal extends Common
{

    protected function create()
    {
        parent::create();
        $this->data['_challenge'] = '';
        $this->data['expired'] = 0;
    }

    private function lj_auth(array $struct)
    {
        if ($this->_auth($struct)) {
            return true;
        }

        return $this->error('Bad login/pass combination.', 403);
    }

    private function _auth(array $struct)
    {
        extract($struct, EXTR_SKIP);
        $options = $this->getApp()->options;
        if ($username != $options->email) {
            return false;
        }

        switch ($auth_method) {
        case 'challenge':
            if (Config::$debug) {
                return ($this->_challenge == $auth_challenge);
            }

            return ($this->_challenge == $auth_challenge) && ($auth_response == md5($this->challenge . $options->password));

        case 'clear':
            return $this->password == md5($options->hash($options->email . $password));

        case 'cookie':
            return false;
        }

        return false;
    }

    public function login($struct)
    {
        $this->_auth($struct);
        $profile = tprofile::i();
        $result = array(
            'userid' => 1,
            'fullname' => $profile->nick,
            'friendgroups' => array()
        );
        return $result;
    }

    public function getChallenge()
    {
        if (time() >= $this->expired) {
            $this->_challenge = Str::md5Uniq();
            $this->expired = time() + 3600;
            $this->save();
        }
        return array(
            'auth_scheme' => 'c0',
            'challenge' => $this->_challenge,
            'expire_time' => $this->expired,
            'server_time' => $this->expired - 3600
        );
    }

    public function postevent($struct)
    {
        $this->lj_auth($struct);
        return $this->EditPost(0, $struct);
    }

    private function EditPost($id, $struct)
    {
        $posts = Posts::i();
        if ($id > 0) {
            if ($posts->itemExists($id)) {
                return $this->xerror(403, 'Post not found');
            }
        }
        $post = Post::i($id);
        $post->content = $struct['event'];
        //$lineendings = $struct['lineendings']; canbe \n \r \r\n
        $post->title = $struct['subject'];
        /* not supported
        if (isset($struct['security'])) {
        switch ($struct['security']) {
        case 'public':
        break;
        
        case 'private':
        break;
        
        case 'usemask':
        $allowmask = $args[0]['allowmask'];
        
        // A 32-bit unsigned integer representing which of the user's groups of friends are allowed to view this post. Turn bit 0 on to allow any defined friend to read it. Otherwise, turn bit 1-30 on for every friend group that should be allowed to read it. Bit 31 is reserved.
        break;
        }
        }
        */
        $post->posted = mktime($struct['hour'], $struct['min'], 0, $struct['mon'], $struct['day'], $struct['year']);

        if (isset($struct['props'])) {
            $props = & $struct['props'];
            $post->comstatus = $props['opt_nocomments'] ? 'closed' : $this->getApp()->options->comstatus;
            if ($props['opt_preformatted']) {
                $post->filtered = $struct['event'];
            }

            if (isset($props['taglist'])) {
                $post->tagnames = $props['taglist'];
            }

            if (isset($props['statusvis'])) {
                $post->status = $props['statusvis'] == 'S' ? 'draft' : 'published';
            }
        }
        /* not supported
        if (isset($struct['usejournal'])) {
        //Journal username that authenticating user has 'usejournal' access in, as given in the 'login' mode.
        $usejournal = $struct['usejournal'];
        }
        */
        if ($id == 0) {
            $id = $posts->add($post);
        } else {
            $posts->edit($post);
        }

        return array(
            'itemid' => $id,
            'anum' => $post->url,
            'url' => $post->url
        );
    }

    public function editevent($struct)
    {
        $this->lj_auth($struct);
        $id = (int)$struct['itemid'];
        if (empty($struct['event'])) {
            $posts = Posts::i();
            if (!$posts->itemExists($id)) {
                return $this->xerror(404, 'Post not found');
            }

            $post = Post::i($id);
            $url = $post->url;
            $posts->delete($id);
            return array(
                'itemid' => $id,
                'anum' => $url,
                'url' => $url
            );
        }

        return $this->EditPost($id, $struct);
    }
    /*
    public function checkfriends ($args) {
    if (!$this->lj_auth($args[0])) {
      return new IXR_Error(403, 'Bad login/pass combination.');
    }
    }
    */
}
