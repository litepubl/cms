<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\view;

class DateFormater
{
    public $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function __get($name)
    {
        return Lang::translate(date($name, $this->date), 'datetime');
    }
}
