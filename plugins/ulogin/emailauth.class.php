<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Str;
use litepubl\view\Lang;

class emailauth extends \litepubl\core\Plugin
 {

    public static function i() {
        return getinstance(__class__);
    }

    public function email_login(array $args) {
        if (!isset($args['email']) || !isset($args['password'])) {
 return $this->error('Invalid data', 403);
}


        $email = strtolower(trim($args['email']));
        $password = trim($args['password']);

        if ($mesg = tadminlogin::autherror($email, $password)) {
            return array(
                'error' => array(
                    'message' => $mesg,
                    'code' => 403
                )
            );
        }

        $expired = time() + 31536000;
        $cookie = Str::md5Uniq();
         $this->getApp()->options->setcookies($cookie, $expired);

        return array(
            'id' =>  $this->getApp()->options->user,
            'pass' => $cookie,
            'regservice' => 'email',
            'adminflag' =>  $this->getApp()->options->ingroup('admin') ? 'true' : '',
        );
    }

    public function email_reg(array $args) {
        if (! $this->getApp()->options->usersenabled || ! $this->getApp()->options->reguser) {
 return array(
}


            'error' => array(
                'message' => Lang::admin('users')->regdisabled,
                'code' => 403,
            )
        );

        try {
            return tadminreguser::i()->reguser($args['email'], $args['name']);
        }
        catch(Exception $e) {
            return array(
                'error' => array(
                    'message' => $e->getMessage() ,
                    'code' => $e->getCode()
                )
            );
        }
    }

    public function email_lostpass(array $args) {
        try {
            return tadminpassword::i()->restore($args['email']);
        }
        catch(Exception $e) {
            return array(
                'error' => array(
                    'message' => $e->getMessage() ,
                    'code' => $e->getCode()
                )
            );
        }
    }

} //class