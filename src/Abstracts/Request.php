<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Abstracts;

use AndyDefer\PhpClient\Contracts\RequestInterface;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\OptionsVO;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

abstract class Request implements RequestInterface
{
    protected HttpMethod $method;

    protected UrlVO $url;

    protected RequestBodyVO $body;

    protected HeadersVO $headers;

    protected OptionsVO $options;

    public function __construct()
    {
        $this->headers = new HeadersVO;
        $this->options = new OptionsVO;

        $this->method = $this->setMethod();
        $this->url = $this->setUrl();
        $this->body = $this->setBody();
    }

    abstract protected function setMethod(): HttpMethod;

    abstract protected function setUrl(): UrlVO;

    abstract protected function setBody(): RequestBodyVO;

    final public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    final public function getUrl(): UrlVO
    {
        return $this->url;
    }

    final public function getBody(): RequestBodyVO
    {
        // ✅ Reconstruit le body à chaque appel
        $this->body = $this->setBody();

        return $this->body;
    }

    final public function getHeaders(): HeadersVO
    {
        return $this->headers;
    }

    final public function getOptions(): OptionsVO
    {
        return $this->options;
    }
}
