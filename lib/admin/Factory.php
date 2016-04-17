<?php

namespace litepubl\admin;
use litepubl\view\Lang;
use litepubl\view\Args;

trait Factory
{

    public function getlang() {
        return Lang::admin();
    }

public function newTable() {
return new Table($this->admintheme);
}

public function tableItems(array $items, array $struct) {
$table = $this->newTable();
        $table->setstruct($struct);
        return $table->build($items);
}

public function newList() {
return new UList($this->admintheme);
}

public function newTabs() {
return new Tabs($this->admintheme);
}

public function newForm() {
return new Form(new Args());
}

public function newArgs() {
return new Args();
}

    public function getnotfound() {
        return $this->admintheme->geterr(tlocal::i()->notfound);
    }

    public function getfrom($perpage, $count) {
        if (litepubl::$urlmap->page <= 1) return 0;
        return min($count, (litepubl::$urlmap->page - 1) * $perpage);
    }

    public function confirmDelete($id, $mesg = false) {
        $args = new Args();
        $args->id = $id;
        $args->action = 'delete';
        $args->adminurl = $this->adminurl;
        $args->confirm = $mesg ? $mesg : Lang::i()->confirmdelete;

        $admin = $this->admintheme;
        return $admin->parsearg($admin->templates['confirmform'], $args);
}

    public function confirmDeleteItem($owner) {
        $id = (int)$this->getparam('id', 0);
$admin = $this->admintheme;
$lang = Lang::i();

        if (!$owner->itemexists($id)) {
return $admin->geterr($lang->notfound);
}

        if (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
            $owner->delete($id);
            return $admin->success($lang->successdeleted);
        } else {

            $args = new Args();
            $args->id = $id;
            $args->adminurl = $this->adminurl;
            $args->action = 'delete';
            $args->confirm = $lang->confirmdelete;
            return $admin->parsearg($admin->templates['confirmform'], $args);
    }

}