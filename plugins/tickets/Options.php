<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\tickets;

use litepubl\view\Args;
use litepubl\view\Lang;

class Options extends \litepubl\admin\Menu
{

    public function getContent()
    {
        $lang = Lang::admin('tickets');
        $args = new Args();
        $args->formtitle = $lang->admincats;
        $tickets = Tickets::i();
        return $this->admintheme->form($this->admintheme->getcats($tickets->cats), $args);
    }

    public function processForm()
    {
        $tickets = Tickets::i();
        $tickets->cats = $this->admintheme->processcategories();
        $tickets->save();
    }
}
