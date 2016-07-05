<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\plugins\regservices;

use litepubl\core\Context;
use litepubl\utils\Http;
use litepubl\view\Lang;

class Yandex extends Service
{

    protected function create()
    {
        parent::create();
        $this->data['name'] = 'yandex';
        $this->data['title'] = 'Yandex';
        $this->data['icon'] = 'yandex.png';
        $this->data['url'] = '/yandex-oauth2callback.php';
    }

    public function getAuthurl(): string
    {
        $url = 'https://oauth.yandex.ru/authorize?response_type=code' . $url.= '&client_id=' . $this->client_id;
        $url.= '&state=' . $this->newstate();
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
            'https://oauth.yandex.ru/token', array(
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code'
            )
        );

        if ($resp) {
            $tokens = json_decode($resp);
            if ($r = Http::get('https://login.yandex.ru/info?format=json&oauth_token=' . $tokens->access_token)) {
                $info = json_decode($r);
                return $this->addUser(
                    $context, array(
                    'service' => $this->name,
                    'uid' => $info->id,
                    'email' => isset($info->default_email) ? $info->default_email : $info->emails[0],
                    'name' => isset($info->real_name) ? $info->real_name : $info->display_name,
                    ), $info
                );
            }
        }

        $context->response->forbidden();
    }

    protected function getAdminInfo(Lang $lang): array
    {
        return array(
            'regurl' => 'https://oauth.yandex.ru/client/new',
            'client_id' => $lang->yandex_id,
            'client_secret' => $lang->yandex_secret
        );
    }
}
