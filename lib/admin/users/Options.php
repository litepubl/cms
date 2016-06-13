<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin\users;

use litepubl\core\UserGroups;
use litepubl\pages\Users as UserPages;
use litepubl\utils\LinkGenerator;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\admin\GetPerm;

class Options extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $admin = $this->admintheme;
        $lang = Lang::i('users');
        $args = new Args();
        $args->formtitle = $lang->useroptions;

        $pages = UserPages::i();
        $args->createpage = $pages->createpage;

        $linkgen = LinkGenerator::i();
        $args->linkschema = $linkgen->data['user'];

        $groups = UserGroups::i();
        $args->defaulthome = $groups->defaulthome;

        return $admin->form('[checkbox=createpage]
    [text=linkschema]
    [text=defaulthome]
' . $admin->h($lang->defaults) . GetPerm::groups($groups->defaults) , $args);
    }

    public function processForm()
    {
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

