<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\extracontact;

use litepubl\pages\Contacts;

class ExtraContact extends \litepubl\core\Plugin implements \litepubl\admin\AdminInterface
{
    use \litepubl\admin\PanelTrait;

    public function __construct()
    {
        parent::__construct();
        $this->createInstances($this->getSchema());
    }

    public function getContent(): string
    {
        $contact = Contacts::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $items = '';
        foreach ($contact->data['extra'] as $name => $title) {
            $items.= "$name =$title\n";
        }
        $args->items = $items;

        $args->formtitle = $lang->formtitle;
        return $this->admin->form('[editor=items]', $args);
    }

    public function processForm()
    {
        $contact = Contacts::i('tcontactform');
        $contact->data['extra'] = parse_ini_string(trim($_POST['items']), false);
        $contact->save();
    }
}
