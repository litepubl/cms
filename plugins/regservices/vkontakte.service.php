<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tvkontakteregservice extends tregservice {

    public static function i() {
        return static::iGet(__class__);
    }

    protected function create() {
        parent::create();
        $this->data['name'] = 'vkontakte';
        $this->data['title'] = 'VKontakte';
        $this->data['icon'] = 'vkontakte.png';
        $this->data['url'] = '/vkontakte-oauth2callback.php';
    }

    public function getAuthurl() {
        $url = 'http://oauth.vk.com/authorize?';
        $url.= parent::getauthurl();
        return $url;
    }

    //handle callback
    public function request($arg) {
        if ($err = parent::request($arg)) {
 return $err;
}


        $code = $_REQUEST['code'];
        $resp = http::post('https://oauth.vk.com/access_token', array(
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' =>  $this->getApp()->site->url . $this->url,
            //'grant_type' => 'authorization_code'
            
        ));

        if ($resp) {
            $tokens = json_decode($resp);
            if ($r = http::get('https://api.vk.com/method/getProfiles?uids=' . $tokens->user_id . '&access_token=' . $tokens->access_token)) {
                $js = json_decode($r);
                $info = $js->response[0];
                return $this->adduser(array(
                    'service' => $this->name,
                    'uid' => $info->uid,
                    'name' => $info->first_name . ' ' . $info->last_name,
                    'website' => 'http://vk.com/id' . $info->uid
                ) , $info);
            }
        }

        return $this->errorauth();
    }

    protected function getAdmininfo($lang) {
        return array(
            'regurl' => 'http://vk.com/editapp?act=create',
            'client_id' => $lang->yandex_id,
            'client_secret' => $lang->vk_secret
        );
    }

}