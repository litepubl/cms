<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Lang;
use litepubl\view\Args;

class tadminusernews {

    public static function i() {
        return static::iGet(__class__);
    }

    public function getContent() {
        $plugin = tusernews::i();
        $lang = Lang::admin('usernews');
        $args = new Args();
        $form = '';
        foreach (array(
            '_changeposts',
            '_canupload',
            '_candeletefile',
            'checkspam',
            'insertsource'
        ) as $name) {
            $args->$name = $plugin->data[$name];
            //$args->data["\$lang.$name"] = $about[$name];
            $form.= "[checkbox=$name]";
        }

        foreach (array(
            'sourcetml',
            'editorfile'
        ) as $name) {
            $args->$name = $plugin->data[$name];
            //$args->data["\$lang.$name"] = $about[$name . 'label'];
            $form.= "[text=$name]";
        }

        $args->formtitle = $lang->formtitle;
        $html = tadminhtml::i();
        return $html->adminform($form, $args);
    }

    public function processForm() {
        $plugin = tusernews::i();
        foreach (array(
            '_changeposts',
            '_canupload',
            '_candeletefile',
            'checkspam',
            'insertsource'
        ) as $name) {
            $plugin->data[$name] = isset($_POST[$name]);
        }
        foreach (array(
            'sourcetml',
            'editorfile'
        ) as $name) {
            $plugin->data[$name] = $_POST[$name];
        }

        $plugin->save();
    }

}