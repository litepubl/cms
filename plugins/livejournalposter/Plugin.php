<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\plugins\livejournalposter;

use litepubl\Config;
use litepubl\post\Post;
use litepubl\view\Theme;
use litepubl\xmlrpc\IXR_Client;

class Plugin extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['host'] = '';
        $this->data['login'] = '';
        $this->data['password'] = '';
        $this->data['community'] = '';
        $this->data['privacy'] = 'public';
        $this->data['template'] = '';
    }

    public function sendPost($id)
    {
        if ($this->host == '' || $this->login == '') {
            return false;
        }

        $post = Post::i($id);
        Theme::$vars['post'] = $post;
        $theme = Theme::i();
        $content = $theme->parse($this->template);
        $date = getdate($post->posted);

        if ($post->status != 'published') {
            return;
        }

        $meta = $post->meta;

        $client = new IXR_Client($this->host, '/interface/xmlrpc');
        if (!$client->query('LJ.XMLRPC.getchallenge')) {
            if (Config::$debug) {
                $this->getApp()->getLogger()->warning('live journal: error challenge');
            }
            return false;
        }

        $response = $client->getResponse();
        $challenge = $response['challenge'];

        $args = array(
        'username' => $this->login,
        'auth_method' => 'challenge',
        'auth_challenge' => $challenge,
        'auth_response' => md5($challenge . md5($this->password)) ,
        'ver' => "1",
        'event' => $content,
        'subject' => $post->title,
        'year' => $date['year'],
        'mon' => $date['mon'],
        'day' => $date['mday'],
        'hour' => $date['hours'],
        'min' => $date['minutes'],
        'props' => array(
            'opt_nocomments' => !$post->commentsenabled,
            'opt_preformatted' => true,
            'taglist' => $post->tagnames
        )
        );

        switch ($this->privacy) {
        case "public":
            $args['security'] = "public";
            break;


        case "private":
            $args['security'] = "private";
            break;


        case "friends":
            $args['security'] = "usemask";
            $args['allowmask'] = 1;
        }

        if ($this->community != '') {
            $args['usejournal'] = $this->community;
        }

        if (isset($meta->ljid)) {
            $method = 'LJ.XMLRPC.editevent';
            $args['itemid'] = $meta->ljid;
        } else {
            $method = 'LJ.XMLRPC.postevent';
        }

        if (!$client->query($method, $args)) {
            if (Config::$debug) {
                $this->getApp()->getLogger()->warning('Something went wrong - ' . $client->getErrorCode() . ' : ' . $client->getErrorMessage());
            }
            return false;
        }

        if (!isset($meta->ljid)) {
            $response = $client->getResponse();
            $meta->ljid = $response['itemid'];
        }
        return $meta->ljid;
    }
}
