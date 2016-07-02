<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\core;

class Getter
{
    public $get;
    public $set;

    public function __construct($get = null, $set = null)
    {
        $this->get = $get;
        $this->set = $set;
    }

    public function __get($name)
    {
        return call_user_func_array(
            $this->get, array(
            $name
            )
        );
    }

    public function __set($name, $value)
    {
        call_user_func_array(
            $this->set, array(
            $name,
            $value
            )
        );
    }
}
