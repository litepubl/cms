<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\plugins\regservices;

use litepubl\core\Context;
use litepubl\core\Session;
use litepubl\utils\Http;
use litepubl\view\Lang;

class Twitter extends Service
{

    protected function create()
    {
        parent::create();
        $this->data['name'] = 'twitter';
        $this->data['title'] = 'Twitter';
        $this->data['icon'] = 'twitter';
        $this->data['url'] = '/twitter-oauth1callback.php';
    }

    public function getAuthUrl(): string
    {
        $oauth = $this->getOauth();
        if ($tokens = $oauth->getRequestToken()) {
            Session::start(md5($tokens['oauth_token']));
            $_SESSION['tokens'] = $tokens;
            session_write_close();
            return $oauth->get_authorize_url();
        }
        return false;
    }

    public function getOauth()
    {
        $oauth = new Oauth();
        $oauth->urllist['callback'] = $this->getApp()->site->url . $this->url;
        $oauth->key = $this->client_id;
        $oauth->secret = $this->client_secret;
        return $oauth;
    }

    //handle callback
    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;

        if (empty($_GET['oauth_token'])) {
            return $response->forbidden();
        }

        Session::start(md5($_GET['oauth_token']));
        if (!isset($_SESSION['tokens'])) {
            session_destroy();
            return $response->forbidden();
        }

        $tokens = $_SESSION['tokens'];
        session_destroy();
        $oauth = $this->getOauth();
        $oauth->setTokens($tokens['oauth_token'], $tokens['oauth_token_secret']);

        if ($tokens = $oauth->getAccessToken($_REQUEST['oauth_verifier'])) {
            if ($r = $oauth->get_data('https://api.twitter.com/1/account/verify_credentials.json')) {
                $info = json_decode($r);
                return $this->addUser(
                    $context, [
                    'uid' => $info->id,
                    'name' => $info->name,
                    'website' => 'http://twitter.com/account/redirect_by_id?id=' . $info->id_str
                    ], $info
                );
            }
        }

        $response->forbidden();
    }

    protected function getAdminInfo(Lang $lang): array
    {
        return [
            'regurl' => 'https://dev.twitter.com/apps/new',
            'client_id' => 'Consumer key',
            'client_secret' => 'Consumer secret'
        ];
    }
}
