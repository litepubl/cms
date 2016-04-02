<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class emailauth extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }

  public function email_login(array $args) {
    if (!isset($args['email']) || !isset($args['password'])) return $this->error('Invalid data', 403);
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
    $cookie = md5uniq();
    litepubl::$options->setcookies($cookie, $expired);

    return array(
      'id' => litepubl::$options->user,
      'pass' => $cookie,
      'regservice' => 'email',
      'adminflag' => litepubl::$options->ingroup('admin') ? 'true' : '',
    );
  }

  public function email_reg(array $args) {
    if (!litepubl::$options->usersenabled || !litepubl::$options->reguser) return array(
      'error' => array(
        'message' => tlocal::admin('users')->regdisabled,
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