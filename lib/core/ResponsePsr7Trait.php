<?php

namespace litepubl\core;
trait ResponsePsr7Trait
{

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function getHeaders()
    {
$result = [];
foreach ($this->headers as $k => $v) {
$result[$k] = [$v];
}

return $result;
    }

    public function hasHeader($header)
    {
$header = strtolower($header);
foreach ($this->headers as $k => $v) {
if ($header == strtlower($k)) {
return true;
}

return false;
    }

    public function getHeader($header)
        {
        $header = strtolower($header);
foreach ($this->headers as $k => $v) {
if ($header == strtlower($k)) {
return [$v];
}

return [];
    }

    public function getHeaderLine($name)
    {
        $value = $this->getHeader($name);
        if (empty($value)) {
            return '';
        }

        return implode(',', $value);
    }

    abstract public function getBody();
}