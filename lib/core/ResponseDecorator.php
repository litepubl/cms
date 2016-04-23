<?php

namespace litepubl\core;

//bridge from Litepublisher to PSR7 interfaces

class ResponseDecorator implements \Psr\Http\Message\ResponseInterface
{
protected $litepublResponse;
protected $stream;

    public function __construct(Response $response)
{
$this->litepublResponse = $response;
}

public function __call($name)
{
return $this->litepublResponse->$name();
}

public function __clone()
{
$this->litepublResponse = clone $this->litepublResponse;
}

    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;
        $new->litepublResponse->status = $code;
        return $new;
    }

    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->litepublResponse->protocol = $version;
        return $new;
    }

    public function getHeaders()    {
$result = [];
foreach ($this->litepublResponse->headers as $k => $v) {
$result[$k] = [$v];
}

return $result;
    }

    public function hasHeader($header)
    {
$header = strtolower($header);
foreach ($this->litepublResponse->headers as $k => $v) {
if ($header == strtolower($k)) {
return true;
}
}

return false;
    }

    public function getHeader($name)
if ($v = $this->getHeaderLine($name) {
return [$v];
}

return [];
}

    public function getHeaderLine($name)
    {
$name = strtolower($name);
foreach ($this->litepublResponse->headers as $k => $v) {
if ($name == strtolower($k)) {
return $v;
}
}

return '';
    }

    public function withHeader($name, $value)
    {
$value = $this->validateValue($value);
        $name = strtolower($name);
$name[0] = strtoupper($name[0]);
        $new = clone $this;
        $new->litepublResponse->headers[$name]         = $value;
        return $new;
    }

    public function withAddedHeader($name, $value)
    {
$value = $this->validateValue($value);
        if (! $this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $name = strtolower($name);
$name[0] = strtoupper($name[0]);

$old = $this->litepublResponse->headers[$name];
$value = array_merge(explode(',', $old), explode(',', $value));

        $new = clone $this;
        $new->headers[$header] = implode(',', $value);
        return $new;
    }

protected function validateValue($value) {
        if (!is_string($value) || !is_array($value) || ! $this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
'Invalid header value; must be a string or array of strings'
            );
        }

        HeaderSecurity::assertValidName($header);
        self::assertValidHeaderValue($value);

return implode(',', $value);
}

    public function withoutHeader($name)
    {
        if (! $this->hasHeader($name)) {
            return clone $this;
        }

        $name = strtolower($name);
        $new = clone $this;
foreach ($new->litepublResponse->headers as $k => $v) {
if ($name == strtolower($k)) {
        unset($new->litepublResponse->headers[$k);
}
}

        return $new;
    }

    public function getBody()
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($this->litepublResponse->body);
        $body->rewind();
        return $body;
    }

    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->litepublResponse->body = $body->getContents();
        return $new;
    }

}