<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\core;

class Users extends Items
{
    public $grouptable;

    protected function create()
    {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'users';
        $this->table = 'users';
        $this->grouptable = 'usergroup';
        $this->addevents('beforedelete');
    }

    public function res2items($res)
    {
        if (!$res) {
            return array();
        }

        $result = array();
        $db = $this->getApp()->db;
        while ($item = $db->fetchassoc($res)) {
            $id = (int)$item['id'];
            $item['idgroups'] = Str::toIntArray($item['idgroups']);
            $result[] = $id;
            $this->items[$id] = $item;
        }

        return $result;
    }

    public function add(array $values): int
    {
        return Usersman::i()->add($values);
    }

    public function edit(int $id, array $values)
    {
        return Usersman::i()->edit($id, $values);
    }

    public function setGroups(int $id, array $idgroups)
    {
        $idgroups = array_unique($idgroups);
        Arr::deleteValue($idgroups, '');
        Arr::deleteValue($idgroups, false);
        Arr::deleteValue($idgroups, null);

        $this->items[$id]['idgroups'] = $idgroups;
        $db = $this->getdb($this->grouptable);
        $db->delete("iduser = $id");
        foreach ($idgroups as $idgroup) {
            $db->add(array(
                'iduser' => $id,
                'idgroup' => $idgroup
            ));
        }
    }

    public function delete($id)
    {
        if ($id == 1) {
            return;
        }

        $this->beforedelete($id);
        $this->getdb($this->grouptable)->delete('iduser = ' . (int)$id);
        $this->pages->delete($id);
        $this->getdb('comments')->update("status = 'deleted'", "author = $id");
        return parent::delete($id);
    }

    public function getPages()
    {
        return \litepubl\pages\Users::i();
    }

    public function emailExists(string $email): int
    {
        if (!$email) {
            return 0;
        }

        if ($email == $this->getApp()->options->email) {
            return 1;
        }

        foreach ($this->items as $id => $item) {
            if ($email == $item['email']) {
                return $id;
            }

        }

        if ($item = $this->db->findItem('email = ' . Str::quote($email))) {
            $id = (int)$item['id'];
$item['idgroups'] = str::toIntArray($item['idgroups']);
            $this->items[$id] = $item;
            return $id;
        }

        return 0;
    }

    public function getPassword(int $id): string
    {
        return $id == 1 ? $this->getApp()->options->password : $this->getValue($id, 'password');
    }

    public function changePassword(int $id, string $password)
    {
        $item = $this->getitem($id);
        $this->setvalue($id, 'password', $this->getApp()->options->hash($item['email'] . $password));
    }

    public function approve($id)
    {
        $this->setValue($id, 'status', 'approved');
        $pages = $this->pages;
        if ($pages->createpage) {
            $pages->addPage($id);
        }
    }

    public function auth(string $email, string $password): int
    {
        return $this->authPassword($this->emailExists($email) , $password);
    }

    public function authPassword(int $id, string $password): int
    {
        if ($id && $password) {
        $item = $this->getItem($id);
        if ($item['password'] == $this->getApp()->options->hash($item['email'] . $password)) {
            if ($item['status'] == 'wait') {
$this->approve($id);
}

            return $id;
        }
}

        return 0;
    }

    public function authCookie(string $cookie)
    {
        if (empty($cookie)) {
            return false;
        }

        $cookie = $this->getApp()->options->hash($cookie);
        if ($cookie == $this->getApp()->options->hash('')) {
            return false;
        }

        if ($id = $this->findCookie($cookie)) {
            $item = $this->getitem($id);
            if (strtotime($item['expired']) > time()) {
                return $id;
            }

        }
        return false;
    }

    public function findCookie(string $cookie)
    {
        $cookie = Str::quote($cookie);
        if (($a = $this->select('cookie = ' . $cookie, 'limit 1')) && (count($a) > 0)) {
            return (int)$a[0];
        }
        return false;
    }

    public function getGroupName(int $id): string
    {
        $item = $this->getItem($id);
        $groups = UserGroups::i();
        return $groups->items[$item['idgroups'][0]]['name'];
    }

    public function clearCookie(int $id)
    {
        $this->setCookie($id, '', 0);
    }

    public function setCookie(int $id, string $cookie, int $expired)
    {
        if ($cookie) $cookie = $this->getApp()->options->hash($cookie);
        $expired = Str::sqlDate($expired);
        if (isset($this->items[$id])) {
            $this->items[$id]['cookie'] = $cookie;
            $this->items[$id]['expired'] = $expired;
        }

        $this->db->updateassoc(array(
            'id' => $id,
            'cookie' => $cookie,
            'expired' => $expired
        ));
    }

}

