<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\regservices;

use litepubl\core\Context;
use litepubl\utils\Http;
use litepubl\view\Lang;

class MailRu extends Service
{

    protected function create()
    {
        parent::create();
        $this->data['name'] = 'mailru';
        $this->data['title'] = 'mail.ru';
        $this->data['icon'] = 'mailru.png';
        $this->data['url'] = '/mailru-oauth2callback.php';
    }

    public function getAuthUrl(): string
    {
        $url = 'https://connect.mail.ru/oauth/authorize?';
        $url.= parent::getauthurl();
        return $url;
    }

    //handle callback
    public function sign(array $request_params, string $secret_key): string
    {
        ksort($request_params);
        $params = '';
        foreach ($request_params as $key => $value) {
            $params.= "$key=$value";
        }
        return md5($params . $secret_key);
    }

    public function request(Context $context)
    {
        parent::request($context);

        if ($context->response->status != 200) {
                return;
        }

        $code = $_REQUEST['code'];
        $resp = Http::post(
            'https://connect.mail.ru/oauth/token', array(
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->getApp()->site->url . $this->url,
            'grant_type' => 'authorization_code'
            )
        );

        if ($resp) {
            $tokens = json_decode($resp);

            $params = array(
                'method' => 'users.getInfo',
                'app_id' => $this->client_id,
                'session_key' => $tokens->access_token,
                'uids' => $tokens->x_mailru_vid,
                'secure' => '1',
                'format' => 'json',
            );

            ksort($params);
            $params['sig'] = $this->sign($params, $this->client_secret);
            if ($r = Http::get('http://www.appsmail.ru/platform/api?' . http_build_query($params))) {
                $js = json_decode($r);
                $info = $js[0];
                return $this->addUser(
                    $context, array(
                    'uid' => $info->uid,
                    'email' => isset($info->email) ? $info->email : '',
                    'name' => $info->nick,
                    'website' => isset($info->link) ? $info->link : ''
                    ), $info
                );
            }
        }

        $context->response->forbidden();
    }

    protected function getAdminInfo(Lang $lang): array
    {
        return array(
            'regurl' => 'http://api.mail.ru/sites/my/add',
            'client_id' => 'ID',
            'client_secret' => $lang->mailru_secret
        );
    }
}
