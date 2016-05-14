<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\view\Args;
use litepubl\view\Lang;

class tadminticketoptions extends \litepubl\admin\Menu
{

    public static function i($id = 0)
    {
        return parent::iteminstance(__class__, $id);
    }

    public function getContent()
    {
        $lang = Lang::admin('tickets');
        $args = new Args();
        $args->formtitle = $lang->admincats;
        $tickets = ttickets::i();
        return $this->html->adminform($this->admintheme->getcats($tickets->cats) , $args);
    }

    public function processForm()
    {
        $tickets = ttickets::i();
        $tickets->cats = $this->admintheme->processcategories();
        $tickets->save();
    }

}

