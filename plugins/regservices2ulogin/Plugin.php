<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\regservices2ulogin;

use litepubl\core\Str;

class Plugin extends \litepubl\core\Items
{

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'regservices/users';
        $this->table = 'ulogin';
    }

    public function add(int $id, string $service, string $uid)
    {
        if (($id == 0) || ($service == '') || ($uid == '')) {
            return;
        }

        $this->db->insert(
            [
            'id' => $id,
            'service' => $service,
            'uid' => $uid
            ]
        );

        $this->added($id, $service);
    }

    public function find(string $service, string $uid)
    {
        return $this->db->findid('service = ' . Str::quote($service) . ' and uid = ' . Str::quote($uid));
    }
}
