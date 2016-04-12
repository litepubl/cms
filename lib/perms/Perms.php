<?php

namespace litepubl\perms;

class Perms extends \litepubl\core\Items
{
use \litepubl\core\DataStorageTrait;

    public $classes;
    public $tables;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'perms';
        $this->addmap('classes', array());
        $this->tables = array(
            'files',
            'posts',
            'tags',
            'categories'
        );
    }

    public function addclass(Perm $perm) {
        $this->classes[get_class($perm) ] = $perm->name;
        $this->save();
    }

    public function add(Perm $perm) {
        $this->lock();
        $id = ++$this->autoid;
        $perm->id = $id;
        $perm->data['class'] = get_class($perm);
        if ($perm->name == 'permission') {
$perm->name.= $id;
}

        $this->items[$id] = & $perm->data;
        $this->unlock();
        return $id;
    }

    public function delete($id) {
        if (($id == 1) || (!isset($this->items[$id]))) {
 return false;
}

            $db = litepubl::$db;
            foreach ($this->tables as $table) {
                $db->table = $table;
                $db->update('idperm = 0', "idperm = $id");
            }

        return parent::delete($id);
    }

}