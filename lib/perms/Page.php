<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\perms;

use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\view\Lang;

class Page extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    use \litepubl\view\EmptyViewTrait;

    public $perm;
    public $url;
    private $formresult;

    protected function create()
    {
        parent::create();
        $this->basename = 'passwordpage';
        $this->formresult = '';
        $this->url = '/check-password.php';
        $this->data['title'] = '';
    }

    private function checkspam($s)
    {
        if (!($s = @base64_decode($s))) {
            return false;
        }

        $sign = 'megaspamer';
        if (!Str::begin($s, $sign)) {
            return false;
        }

        $timekey = (int)substr($s, strlen($sign));
        return time() < $timekey;
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;

        $post = $context->request->getPost();
        if (empty($post) || !count($post)) {
            return;
        }

        $antispam = isset($post['antispam']) ? $post['antispam'] : '';
        if (!$this->checkspam($antispam)) {
            $response->status = 403;
            return;
        }

        $password = isset($post['password']) ? trim($post['password']) : '';
        if (!$password) {
            return;
        }

        if (!isset($this->perm)) {
            $idperm = isset($get['idperm']) ? (int)$get['idperm'] : 0;
            $perms = Perms::i();
            if (!$perms->itemExists($idperm)) {
                $response->status = 403;
                return;
            }

            $this->perm = Perm::i($idperm);
        }

        $backurl = isset($get['backurl']) ? $get['backurl'] : '';
        if ($this->perm->checkpassword($password)) {
            if ($backurl) {
                $response->redir($backurl);
                return;
            }
        } else {
            $this->formresult = Lang::i()->errpassword;
        }
    }

    public function getTitle(): string
    {
        return Lang::i('perms')->reqpassword;
    }

    public function getCont(): string
    {
        $result = $this->formresult == '' ? '' : sprintf('<h4>%s</h4>', $this->formresult);

        $args->antispam = base64_encode('megaspamer' . strtotime("+1 hour"));
        $args->remember = isset($_POST['remember']);
        $result.= $this->getSchema()->theme->parseArg($this->form, $args);
        return $result;
    }

    public function getForm($antispam, $remember)
    {
        $form = $this->getApp()->cache->getString('perms-form');
        if (!$form) {
            $form = $this->createForm();
            $this->getApp()->cache->setString('perms-form', $form);
        }

        return strtr($form, ['$antispam' => $antispam, '$remember' => $remember ? 'checked="checked"' : '', ]);
    }

    public function createForm()
    {
        $theme = $this->getSchema()->theme;
        $lang = Lang::i('perms');

        return strtr($theme->templates['content.admin.form'], ['$formtitle' => $lang->pwdaccess, '$items' => $theme->getinput('password', 'password', '', $lang->password) . $theme->getinput('checkbox', 'remember', '$remember', $lang->remember) . $theme->getinput('hidden', 'antispam', '$antispam') ,

        '[submit=update]' => $theme->getsubmit($lang->send) , ]);
    }

}

