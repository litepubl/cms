<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\plugins\livejournalposter;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
        if ($plugin->template == '') {
            $plugin->template = file_get_contents($dir . 'livejournalposter.tml');
        }
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->host = $plugin->host;
        $args->login = $plugin->login;
        $args->password = $plugin->password;
        $args->community = $plugin->community;
        $args->template = $plugin->template;
        $args->privacy  =
        $plugin->privacy = $privacy;

        $args->public = 'public' == $plugin->privacy;
        $args->private = 'private' == $plugin->privacy;
        $args->friends = 'friends' == $plugin->privacy;

        $args->formtitle = $lang->name;
        return $this->admin->form(
            '
[text=host]
 [text=login]
 [password=password]
 [text=community]
    <p><strong>$lang.privacy</strong>
    <label><input name="privacy" type="radio" value="public" $public/>$lang.public</label>
    <label><input name="privacy" type="radio" value="private" $private />$lang.private</label>
    <label><input name="privacy" type="radio" value="frinds" $friends/>$lang.friends</label>
    </p>
    
    [editor=template]
', $args
        );
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $plugin = Plugin::i();
        $plugin->lock();
        $plugin->host = $host;
        $plugin->login = $login;
        $plugin->password = $password;
        $plugin->community = $community;
        $plugin->privacy = $privacy;
        $plugin->template = $template;
        $plugin->unlock();
        return '';
    }
}
