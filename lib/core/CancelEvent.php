<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\core;

class CancelEvent extends \Exception
{
    public $result;

    public function __construct($message, $code = 0)
    {
        $this->result = $message;
        parent::__construct('', 0);
    }
}
