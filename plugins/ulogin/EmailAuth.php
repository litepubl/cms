<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\ulogin;

use litepubl\admin\pages\Login;
use litepubl\admin\pages\Password;
use litepubl\admin\pages\RegUser;
use litepubl\core\Str;
use litepubl\view\Lang;

class EmailAuth extends \litepubl\core\Plugin
{

    public function email_login(array $args)
    {
        if (!isset($args['email']) || !isset($args['password'])) {
            return $this->error('Invalid data', 403);
        }

        $email = strtolower(trim($args['email']));
        $password = trim($args['password']);

        if ($mesg = Login::authError($email, $password)) {
            return [
                'error' => [
                    'message' => $mesg,
                    'code' => 403
                ]
            ];
        }

        $expired = time() + 31536000;
        $cookie = Str::md5Uniq();
        $this->getApp()->options->setCookies($cookie, $expired);

        return [
            'id' => $this->getApp()->options->user,
            'pass' => $cookie,
            'regservice' => 'email',
            'adminflag' => $this->getApp()->options->ingroup('admin') ? 'true' : '',
        ];
    }

    public function email_reg(array $args)
    {
        if (!$this->getApp()->options->usersenabled || !$this->getApp()->options->reguser) {
            return [
            'error' => [
                'message' => Lang::admin('users')->regdisabled,
                'code' => 403,
            ]
            ];
        }

        try {
            return RegUser::i()->regUser($args['email'], $args['name']);
        } catch (\Exception $e) {
            return [
                'error' => [
                    'message' => $e->getMessage() ,
                    'code' => $e->getCode()
                ]
            ];
        }
    }

    public function email_lostpass(array $args)
    {
        try {
            return Password::i()->restore($args['email']);
        } catch (\Exception $e) {
            return [
                'error' => [
                    'message' => $e->getMessage() ,
                    'code' => $e->getCode()
                ]
            ];
        }
    }
}
