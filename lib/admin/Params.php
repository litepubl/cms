<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin;

trait Params
{

    public function idGet()
    {
        return (int)$this->getparam('id', 0);
    }

    public function getParam($name, $default)
    {
        return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
    }

    public function idParam()
    {
        return (int)$this->getparam('id', 0);
    }

    public function getAction()
    {
        return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
    }

    public function getConfirmed()
    {
        return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
    }

}

