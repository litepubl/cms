<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tadminviewsspec extends tadminmenu {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public static function getspecclasses() {
        return array(
            'thomepage',
            'tarchives',
            'tnotfound404',
            'tsitemap'
        );
    }

    public function getcontent() {
        $result = '';
        $views = tviews::i();
        $html = $this->html;
        $lang = tlocal::i('views');
        $args = new targs();

        $tabs = new tabs($this->admintheme);
        $inputs = '';
        foreach (static ::getspecclasses() as $classname) {
            $obj = getinstance($classname);
            $args->classname = $classname;
            $name = substr($classname, 1);
            $args->title = $lang->{$name};
            $inputs = tadminviews::getcomboview($obj->idview, "idview-$classname");
            if (isset($obj->data['keywords'])) $inputs.= $html->getedit("keywords-$classname", $obj->data['keywords'], $lang->keywords);
            if (isset($obj->data['description'])) $inputs.= $html->getedit("description-$classname", $obj->data['description'], $lang->description);
            if (isset($obj->data['head'])) $inputs.= $html->getinput('editor', "head-$classname", tadminhtml::specchars($obj->data['head']) , $lang->head);

            $tabs->add($lang->{$name}, $inputs);
        }

        $args->formtitle = $lang->defaults;
        $result.= $html->adminform($tabs->get() , $args);

        return $result;
    }

    public function processform() {

        foreach (static ::getspecclasses() as $classname) {
            $obj = getinstance($classname);
            $obj->lock();
            $obj->setidview($_POST["idview-$classname"]);
            if (isset($obj->data['keywords'])) $obj->keywords = $_POST["keywords-$classname"];
            if (isset($obj->data['description '])) $obj->description = $_POST["description-$classname"];
            if (isset($obj->data['head'])) $obj->head = $_POST["head-$classname"];
            $obj->unlock();
        }
    }

} //class