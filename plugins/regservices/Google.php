<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\plugins\regservices;

use litepubl\core\Context;
use litepubl\utils\Http;
use litepubl\view\Lang;

class Google extends Service
{

    protected function create()
    {
        parent::create();
        $this->data['name'] = 'google';
        $this->data['title'] = 'Google';
        $this->data['icon'] = 'google';
        $this->data['url'] = '/google-oauth2callback.php';
    }

    public function getAuthUrl(): string
    {
        $url = 'https://accounts.google.com/o/oauth2/auth';
        $url.= '?scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&';
        $url.= parent::getauthurl();
        return $url;
    }

    //handle callback
    public function request(Context $context)
    {
        parent::request($context);

        if ($context->response->status != 200) {
                return;
        }

        $code = $_REQUEST['code'];
        $resp = Http::post(
            'https://accounts.google.com/o/oauth2/token', [
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->getApp()->site->url . $this->url,
            'grant_type' => 'authorization_code'
            ]
        );

        if ($resp) {
            $tokens = json_decode($resp);
            if ($r = Http::get('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $tokens->access_token)) {
                $info = json_decode($r);
                return $this->addUser(
                    $context, [
                    //'uid' => $info->id, session depended
                    'service' => $this->name,
                    'email' => isset($info->email) ? $info->email : '',
                    'name' => $info->name,
                    'website' => isset($info->link) ? $info->link : ''
                    ], $info
                );
            }
        }

        $context->response->forbidden();
    }

    protected function getAdminInfo(Lang $lang): array
    {
        return [
            'regurl' => 'https://code.google.com/apis/console/',
            'client_id' => $lang->client_id,
            'client_secret' => $lang->client_secret
        ];
    }
}
