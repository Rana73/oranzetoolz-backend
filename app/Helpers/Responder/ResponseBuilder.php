<?php

namespace App\Helpers\Responder;

use Illuminate\Contracts\Support\Responsable;

class ResponseBuilder implements Responsable
{
    // response body
    protected $body;
    // response header
    protected $headers;
    
    public function __construct()
    {
        $this->fresh();
    }

    // fresh response builder
    public function fresh(): ResponseBuilder
    {
        $this->body = new ResponseBody;
        $this->headers = [];
        return $this;
    }

    // response body
    public function body()
    {
        return $this->body;
    }

    // response status
    public function status($status): ResponseBuilder
    {
        $this->body->setStatus($status);
        return $this;
    }

    // response code
    public function code(int $code): ResponseBuilder
    {
        $this->body->setCode($code);
        return $this;
    }

    // response message
    public function message(string $message): ResponseBuilder
    {
        $this->body->setMessage($message);
        return $this;
    }

    // response data
    public function data($data): ResponseBuilder
    {
        $this->body->setData($data);
        return $this;
    }

    // response meta
    public function meta(array $meta): ResponseBuilder
    {
        $this->body->setMeta($meta);
        return $this;
    }

    // response headers
    public function headers(array $headers): ResponseBuilder
    {
        $this->body->setHeaders($headers);
        return $this;
    }

    // sending response
    public function respond()
    {
        return response()->json($this->body->toArray(), $this->body->getCode(), $this->body->getHeaders());
    }

    // to response
    public function toResponse($request)
    {
        return $this->respond();
    }
}
