<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\users;
use litepubl\pages\Users as UserPages;
use litepubl\core\UserGroups;
use litepubl\utils\LinkGenerator;
use litepubl\view\Lang;
use litepubl\view\Args;

class Options extends \litepubl\admin\Menu
{

    public function getcontent() {
        $result = '';
$admin = $this->admintheme;
        $lang = tlocal::i('users');
        $args = new targs();
        $args->formtitle = $lang->useroptions;

        $pages = UserPages::i();
        $args->createpage = $pages->createpage;

        $linkgen = LinkGenerator::i();
        $args->linkschema = $linkgen->data['user'];

        $groups = UserGroups::i();
        $args->defaulthome = $groups->defaulthome;

        return $htmladmin>form('[checkbox=createpage]
    [text=linkschema]
    [text=defaulthome]
' . $admin->h($lang->defaults) .
 GetPerm::groups($groups->defaults) ,
 $args);
    }

    public function processform() {
        $pages = UserPages::i();
        $pages->createpage = isset($_POST['createpage']);
        $pages->save();

        $groups = UserGroups::i();
        $groups->defaults = $this->admintheme->check2array('idgroup-');
        $groups->defaulthome = trim($_POST['defaulthome']);
        $groups->save();

        $linkgen = LinkGenerator::i();
        $linkgen->data['user'] = $_POST['linkschema'];
        $linkgen->save();
    }

}