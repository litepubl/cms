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
use litepubl\admin\Panel;

class Odnoklassniki extends Service
{

protected function create()
{
    parent::create();
    $this->data['public_key'] = '';
    $this->data['name'] = 'odnoklassniki';
    $this->data['title'] = 'odnoklassniki.ru';
    $this->data['icon'] = 'odnoklassniki';
    $this->data['url'] = '/odnoklassniki-oauth2callback.php';
}

public function getAuthUrl(): string
{
    $url = 'http://www.odnoklassniki.ru/oauth/authorize?';
    $url.= 'response_type=code';
    $url.= '&redirect_uri=' . urlencode($this->getApp()->site->url . $this->url . $this->getApp()->site->q . 'state=' . $this->newstate());
    $url.= '&client_id=' . $this->client_id;
    return $url;
}

    //handle callback
public function sign(array $request_params, $secret_key)
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
    $resp = Http::post('http://api.odnoklassniki.ru/oauth/token.do', array(
    'grant_type' => 'authorization_code',
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'redirect_uri' => $this->getApp()->site->url . $this->url . $this->getApp()->site->q . 'state=' . $_GET['state'],
    ));

    if ($resp) {
        $tokens = json_decode($resp);
        if (isset($tokens->error)) {
            return $context->response->forbidden();
        }

        $params = array(
            'application_key' => $this->public_key,
            'client_id' => $this->client_id,
            'method' => 'users.getCurrentUser',
            'format' => 'JSON',
        );

        $params['sig'] = strtolower($this->sign($params, md5($tokens->access_token . $this->client_secret)));
        $params['access_token'] = $tokens->access_token;

        if ($r = http::post('http://api.odnoklassniki.ru/fb.do', $params)) {
            $js = json_decode($r);
            if (!isset($js->error)) {
                return $this->addUser($context, array(
                    'uid' => $js->uid,
                    'name' => $js->name,
                    'website' => isset($js->link) ? $js->link : ''
                ), $js);
            }
        }
    }

        return $context->response->forbidden();
}

    protected function getAdminInfo(Lang $lang): array
    {
        return array(
            'regurl' => 'http://api.mail.ru/sites/my/add',
            'client_id' => $lang->odnoklass_id,
            'client_secret' => $lang->odnoklass_secret,
            'public_key' => $lang->odnoklass_public_key
        );
    }

    public function getTab(Panel $admin): string
    {
        $lang = $admin->lang;
        $a = $this->getAdminInfo($lang);
        $result = $admin->admin->help(sprintf($lang->odnoklass_reg, 'http://dev.odnoklassniki.ru/wiki/display/ok/How+to+add+application+on+site'));

        $theme =$admin->theme;
        $result.= $theme->getInput('text', "client_id_$this->name", $theme->quote($this->client_id), $a['client_id']);
        $result.= $theme->getInput('text', "client_secret_$this->name", $theme->quote($this->client_secret), $a['client_secret']);

        $result.= $theme->getInput('text', "public_key_$this->name", $theme->quote($this->public_key), $lang->odnoklass_public_key);
        return $result;
    }

    public function processForm()
    {
        if (isset($_POST["public_key_$this->name"])) {
            $this->public_key = $_POST["public_key_$this->name"];
        }
        parent::processForm();
    }

}
