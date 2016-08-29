<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\admin;

use litepubl\view\Args;
use litepubl\view\Lang;

trait Factory
{

    public function getLang(string $name = ''): Lang
    {
        return Lang::admin($name);
    }

    public function newTable($admin = null): Table
    {
        return new Table($admin ? $admin : $this->admintheme);
    }

    public function tableItems(array $items, array $struct): string
    {
        $table = $this->newTable();
        $table->setStruct($struct);
        return $table->build($items);
    }

    public function newList(): UList
    {
        return new UList($this->admintheme);
    }

    public function newTabs(): Tabs
    {
        return new Tabs($this->admintheme);
    }

    public function newForm($args = null): Form
    {
        return new Form($args ? $args : new Args());
    }

    public function newArgs(): Args
    {
        return new Args();
    }

    public function getNotfound(): string
    {
        return $this->admintheme->geterr(Lang::i()->notfound);
    }

    public function getFrom(int $perpage, int $count): int
    {
        if ($this->getApp()->context->request->page <= 1) {
            return 0;
        }

        return min($count, ($this->getApp()->context->request->page - 1) * $perpage);
    }

    public function confirmDelete($id, $mesg = false)
    {
        $args = new Args();
        $args->id = $id;
        $args->action = 'delete';
        $args->adminurl = $this->adminurl;
        $args->confirm = $mesg ? $mesg : Lang::i()->confirmdelete;

        $admin = $this->admintheme;
        return $admin->parseArg($admin->templates['confirmform'], $args);
    }

    public function confirmDeleteItem($owner)
    {
        $id = (int)$this->getparam('id', 0);
        $admin = $this->admintheme;
        $lang = Lang::i();

        if (!$owner->itemExists($id)) {
            return $admin->geterr($lang->notfound);
        }

        if (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
            $owner->delete($id);
            return $admin->success($lang->successdeleted);
        }

        $args = new Args();
        $args->id = $id;
        $args->adminurl = $this->adminurl;
        $args->action = 'delete';
        $args->confirm = $lang->confirmdelete;
        return $admin->parseArg($admin->templates['confirmform'], $args);
    }

    /*
    * method can used as: extract($this->getStdInstances());
    */
    public function getStdInstances(): array
    {
        return [
        'result' => '',
        'lang' => $this->getLang(),
        'admin' => $this->adminTheme,
        'theme' => $this->theme,
        'args' => $this->newArgs(),
        'id' => $this->idGet(),
        ];
    }
}
