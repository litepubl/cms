<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\regservices;

use litepubl\core\Context;
use litepubl\utils\Http;
use litepubl\view\Lang;

class Facebook extends Service
{

    protected function create()
    {
        parent::create();
        $this->data['name'] = 'facebook';
        $this->data['title'] = 'FaceBook';
        $this->data['icon'] = 'facebook';
        $this->data['url'] = '/facebook-oauth2callback.php';
    }

    public function getAuthUrl(): string
    {
        $url = 'https://www.facebook.com/dialog/oauth?scope=email&';
        $url.= parent::getAuthUrl();
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
        $resp = Http::get('https://graph.facebook.com/oauth/access_token?' . http_build_query(array(
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->getApp()->site->url . $this->url,
        )));

        if ($resp) {
            $params = null;
            parse_str($resp, $params);

            if ($r = Http::get('https://graph.facebook.com/me?access_token=' . $params['access_token'])) {
                $info = json_decode($r);
                return $this->addUser($context, array(
                    'service' => $this->name,
                    'uid' => isset($info->id) ? $info->id : '',
                    'email' => isset($info->email) ? $info->email : '',
                    'name' => $info->name,
                    'website' => isset($info->link) ? $info->link : ''
                ), $info);
            }
        }

        $context->response->forbidden();
    }

    protected function getAdminInfo(Lang $lang): array
    {
        return array(
            'regurl' => 'https://developers.facebook.com/apps',
            'client_id' => 'App ID',
            'client_secret' => 'App Secret'
        );
    }
}
