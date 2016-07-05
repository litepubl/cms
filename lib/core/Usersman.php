<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\core;

class Usersman extends Data
{

    public function add(array $values)
    {
        $users = Users::i();
        $email = trim($values['email']);
        if ($users->emailexists($email)) {
            return false;
        }

        $groups = UserGroups::i();
        if (isset($values['idgroups'])) {
            $idgroups = $this->cleangroups($values['idgroups']);
            if (!count($idgroups)) {
                $idgroups = $groups->defaults;
            }
        } else {
            $idgroups = $groups->defaults;
        }

        $password = empty($values['password']) ? Str::md5Uniq() : $values['password'];

        $item = array(
            'email' => $email,
            'name' => isset($values['name']) ? trim($values['name']) : '',
            'website' => isset($values['website']) ? trim($values['website']) : '',
            'password' => $this->getApp()->options->hash($email . $password) ,
            'cookie' => Str::md5Uniq() ,
            'expired' => Str::sqlDate() ,
            'idgroups' => implode(',', $idgroups) ,
            'trust' => 0,
            'status' => isset($values['status']) ? $values['status'] : 'approved',
        );

        $id = $users->db->add($item);
        $item['idgroups'] = $idgroups;
        $users->items[$id] = $item;
        $users->setgroups($id, $item['idgroups']);
        if ('approved' == $item['status']) {
            $users->pages->add($id);
        }

        $users->added($id);
        return $id;
    }

    public function edit($id, array $values)
    {
        $users = Users::i();
        if (!$users->itemExists($id)) {
            return false;
        }

        $item = $users->getitem($id);
        foreach ($item as $k => $v) {
            if (!isset($values[$k])) {
                continue;
            }

            switch ($k) {
            case 'password':
                if ($values['password'] != '') {
                    $item['password'] = $this->getApp()->options->hash($values['email'] . $values['password']);
                }
                break;


            case 'idgroups':
                $item['idgroups'] = $this->cleanGroups($values['idgroups']);
                break;


            default:
                $item[$k] = trim($values[$k]);
            }
        }

        $users->items[$id] = $item;
        $item['id'] = $id;

        $users->setgroups($id, $item['idgroups']);
        $item['idgroups'] = implode(',', $item['idgroups']);
        $users->db->updateassoc($item);

        $pages = $users->pates;
        if (isset($values['status']) && ('approved' == $values['status']) && ($item['status'] != $values['status'])) {
            if ($pages->itemExists($id)) {
                if ($pages->createpage) {
                    $pages->addpage($id);
                }
            } else {
                $pages->add($id);
            }
        }

        $pages->edit($id, $values);
        return true;
    }

    public function cleangroups($v)
    {
        if (is_array($v)) {
            return $this->checkgroups(array_unique($v));
        }

        if (is_string($v)) {
            $v = trim($v);
            if (strpos($v, ',')) {
                return $this->checkgroups(explode(',', $v));
            }
        }

        if ($id = $this->cleangroup($v)) {
            return array(
                $id
            );
        }
    }

    public function checkgroups(array $a)
    {
        $result = array();
        foreach ($a as $val) {
            if ($id = $this->cleangroup($val)) {
                $result[] = $id;
            }
        }

        return array_unique($result);
    }

    public function cleangroup($v)
    {
        if (is_string($v)) {
            $v = trim($v);
        }
        if (is_numeric($v)) {
            $id = (int)$v;
            if (UserGroups::i()->itemExists($id)) {
                return $id;
            }
        } else {
            return UserGroups::i()->getidgroup($v);
        }
        return false;
    }
}
