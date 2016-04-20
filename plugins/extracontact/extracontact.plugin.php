<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class textracontact extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $contact = tcontactform::singleinstance('tcontactform');
        $about = tplugins::getabout(tplugins::getname(__file__));
        $args = new Args();
        $items = '';
        foreach ($contact->data['extra'] as $name => $title) {
            $items.= "$name =$title\n";
        }
        $args->items = $items;

        $args->formtitle = $about['formtitle'];
        $args->data['$lang.items'] = $about['items'];
        $html = tadminhtml::i();
        return $html->adminform('[editor=items]', $args);
    }

    public function processForm() {
        $contact = tcontactform::singleinstance('tcontactform');
        $contact->data['extra'] = parse_ini_string(trim($_POST['items']), false);
        $contact->save();
    }

} //class