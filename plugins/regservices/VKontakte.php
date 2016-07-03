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

class VKontakte extends Service
{

    protected function create()
    {
        parent::create();
        $this->data['name'] = 'vkontakte';
        $this->data['title'] = 'VKontakte';
        $this->data['icon'] = 'vk';
        $this->data['url'] = '/vkontakte-oauth2callback.php';
    }

    public function getAuthUrl(): string
    {
        $url = 'http://oauth.vk.com/authorize?';
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
        $resp = Http::post(
            'https://oauth.vk.com/access_token', array(
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->getApp()->site->url . $this->url,
            )
        );

        if ($resp) {
            $tokens = json_decode($resp);
            if ($r = Http::get('https://api.vk.com/method/getProfiles?uids=' . $tokens->user_id . '&access_token=' . $tokens->access_token)) {
                $js = json_decode($r);
                $info = $js->response[0];
                return $this->addUser(
                    $context, array(
                    'service' => $this->name,
                    'uid' => $info->uid,
                    'name' => $info->first_name . ' ' . $info->last_name,
                    'website' => 'http://vk.com/id' . $info->uid
                    ), $info
                );
            }
        }

        $context->response->forbidden();
    }

    protected function getAdminInfo(Lang $lang): array
    {
        return array(
            'regurl' => 'http://vk.com/editapp?act=create',
            'client_id' => $lang->yandex_id,
            'client_secret' => $lang->vk_secret
        );
    }
}
