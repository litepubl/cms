<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\plugins\usernews;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->lang;
        $args = $this->args;
        $form = '';
        foreach (array(
            '_changeposts',
            '_canupload',
            '_candeletefile',
            'checkspam',
            'insertsource'
        ) as $name) {
            $args->$name = $plugin->data[$name];
            $form.= "[checkbox=$name]";
        }

        foreach (array(
            'sourcetml',
            'editorfile'
        ) as $name) {
            $args->$name = $plugin->data[$name];
            $form.= "[text=$name]";
        }

        $args->formtitle = $lang->formtitle;
        return $this->admin->form($form, $args);
    }

    public function processForm()
    {
        $plugin = Plugin::i();
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
