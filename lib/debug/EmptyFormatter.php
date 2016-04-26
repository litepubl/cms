<?php

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
