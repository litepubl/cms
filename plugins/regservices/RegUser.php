<?php

namespace litepubl\plugins\regservices;

class RegUser extends \litepubl\core\Items
{

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'regservices' . DIRECTORY_SEPARATOR . 'users';
        $this->table = 'regservices';
    }

    public function add(int $id, string $service, string $uid)
    {
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

    public function find(string $service, string $uid): int
    {
        return $this->db->findId('service = ' . Str::quote($service) . ' and uid = ' . Str::quote($uid));
    }

}
