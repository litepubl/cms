<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\users;
use litepubl\pages\Users;
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

        $pages = Users::i();
        $args->createpage = $pages->createpage;

        $linkgen = LinkGenerator::i();
        $args->linkschema = $linkgen->data['user'];

        $groups = UserGroups::i();
        $args->defaulthome = $groups->defaulthome;

        return $htmladmin>form('[checkbox=createpage]
    [text=linkschema]
    [text=defaulthome]' . $html->h4->defaults . tadmingroups::getgroups($groups->defaults) , $args);
    }

    public function processform() {
        $pages = Users::i();
        $pages->createpage = isset($_POST['createpage']);
        $pages->save();

        $groups = UserGroups::i();
        $groups->defaults = tadminhtml::check2array('idgroup-');
        $groups->defaulthome = trim($_POST['defaulthome']);
        $groups->save();

        $linkgen = LinkGenerator::i();
        $linkgen->data['user'] = $_POST['linkschema'];
        $linkgen->save();
    }

}