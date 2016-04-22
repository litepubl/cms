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

class treguser extends titems {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        $this->dbversion = dbversion;
        parent::create();
        $this->basename = 'regservices/users';
        //$this->table = 'regservices';
        $this->table = 'ulogin';
    }

    public function add($id, $service, $uid) {
        if (($id == 0) || ($service == '') || ($uid == '')) {
 return;
}


        $this->db->insert(array(
            'id' => $id,
            'service' => $service,
            'uid' => $uid
        ));

        $this->added($id, $service);
    }

    public function find($service, $uid) {
        return $this->db->findid('service = ' . Str::quote($service) . ' and uid = ' . Str::quote($uid));
    }

} 