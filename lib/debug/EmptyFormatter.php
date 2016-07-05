<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\debug;

class EmptyFormatter implements \Monolog\Formatter\FormatterInterface
{

    public function format(array $record)
    {
        return '';
    }

    public function formatBatch(array $records)
    {
        return '';
    }
}
