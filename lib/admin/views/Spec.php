<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;
use litepubl\admin\GetSchema;

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
        $theme = $this->theme;
        $lang = tlocal::i('views');
        $args = new Args();

        $tabs = $this->newTabs();
        $inputs = '';
        foreach (static ::getspecclasses() as $classname) {
            $obj = getinstance($classname);
$name = 
$classname = str_replace('\\', '-', $classname);
            $args->classname = $classname;
            $args->title = $lang->{$name};
            $inputs = GetSchema::combo($obj->idview, "idview-$classname");
            if (isset($obj->data['keywords'])) {
$inputs.= $theme->getinput('text', "keywords-$classname", $obj->data['keywords'], $lang->keywords);
}

            if (isset($obj->data['description'])) {
$inputs.= $theme->getinput('text', "description-$classname", $obj->data['description'], $lang->description);
}

            if (isset($obj->data['head'])) {
$inputs.= $theme->getinput('editor', "head-$classname", $theme->quote($obj->data['head']) , $lang->head);
}

            $tabs->add($lang->{$name}, $inputs);
        }

        $args->formtitle = $lang->defaults;
        $result.= $this->admintheme->form($tabs->get() , $args);
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