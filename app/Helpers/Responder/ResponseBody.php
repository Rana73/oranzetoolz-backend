<?php

namespace App\Helpers\Responder;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResponseBody implements Arrayable, Jsonable
{
    // response status
    private $status;
    // response code
    private $code;
    // response message
    private $message;
    // request data
    private $data;
    // response meta
    private $meta;
    // response headers
    private $headers;

    public function __construct($message = '', $data = '', $status = 'success', int $code = 200, $meta = [], $headers = [])
    {
        // set response message
        $this->setMessage($message);
        // set response data
        $this->setData($data);
        // set response status
        $this->setStatus($status);
        // set response code
        $this->setStatus($code);
        // set response meta
        $this->setMeta($meta);
        // set response header
        $this->setHeaders($headers);
    }

    // get response message
    public function getMessage(): string
    {
        return $this->message;
    }

    // set response message
    public function setMessage(string $message): ResponseBody
    {
        $this->message = $message;
        return $this;
    }

    // get response data
    public function getData()
    {
        return $this->data;
    }

    // set response data
    public function setData($data): ResponseBody
    {
        $this->data = $data;
        return $this;
    }

    // get response status
    public function getStatus()
    {
        return $this->status;
    }

    // set response status
    public function setStatus($status): ResponseBody
    {
        $this->status = $status;
        return $this;
    }

    // get response code
    public function getCode(): int
    {
        return $this->code;
    }

    // set response code
    public function setCode(int $code): ResponseBody
    {
        $this->code = $code;
        return $this;
    }

    // get response meta
    public function getMeta(): array
    {
        return $this->meta;
    }

    // set response meta
    public function setMeta($meta): ResponseBody
    {
        $this->meta = $meta;
        return $this;
    }

    // get response headers
    public function getHeaders(): array
    {
        return $this->headers;
    }

    // set response headers
    public function setHeaders($headers): ResponseBody
    {
        $this->headers = $headers;
        return $this;
    }

    // convert response to json
    public function toJson($options = 0): string
    {
        $json = json_encode($this->toArray(), $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonEncodingException;
        }
        return $json;
    }

    // convert response to array
    public function toArray(): array
    {
        if (!empty($this->getData()) && $this->getData() instanceof LengthAwarePaginator) {
            // paginated data
            $data = $this->getData()->toArray();
            // append data into response array
            $response['data'] = $data['data'];
            // append status into response array
            $response['status'] = $this->getStatus();
            // append code into response array
            $response['code'] = $this->getCode();
            // append links into response array
            $response['links'] = [
                'first' => $data['first_page_url'],
                'last' => $data['last_page_url'],
                'prev' => $data['prev_page_url'],
                'next' => $data['next_page_url']
            ];
            // append meta into response array
            $response['meta'] = [
                'current_page' => $data['current_page'],
                'from' => $data['from'],
                'last_page' => $data['last_page'],
                'path' => $data['path'],
                'per_page' => $data['per_page'],
                'to' => $data['to'],
                'total' => $data['total']
            ];
            // append message in response array
            if (!empty($this->getMessage())) {
                $response['message'] = $this->getMessage();
            }
            // append meta in response array
            if (!empty($this->getMeta())) {
                $response['meta'] = array_merge($response['meta'], $this->getMeta());
            }
        } else if (!empty($this->getData()) && $this->getData() instanceof AnonymousResourceCollection) {
            // paginated data
            $data = $this->getData()->resource->toArray();
            if (array_key_exists('data', $data)) {
                // append data into response array
                $response['data'] = $data['data'];
                // append links into response array
                $response['links'] = [
                    'first' => $data['first_page_url'],
                    'last' => $data['last_page_url'],
                    'prev' => $data['prev_page_url'],
                    'next' => $data['next_page_url']
                ];
                // append meta into response array
                $response['meta'] = [
                    'current_page' => $data['current_page'],
                    'from' => $data['from'],
                    'last_page' => $data['last_page'],
                    'path' => $data['path'],
                    'per_page' => $data['per_page'],
                    'to' => $data['to'],
                    'total' => $data['total']
                ];
            } else {
                // append data into response array
                $response['data'] = $data;
            }
            // append status into response array
            $response['status'] = $this->getStatus();
            // append code into response array
            $response['code'] = $this->getCode();
            // append message in response array
            if (!empty($this->getMessage())) {
                $response['message'] = $this->getMessage();
            }
            // append meta in response array
            if (!empty($this->getMeta())) {
                $response['meta'] = array_merge($response['meta'], $this->getMeta());
            }
        } else {
            // response array
            $response['status'] = $this->getStatus();
            // append code into response array
            $response['code'] = $this->getCode();
            // append message in response array
            if (!empty($this->getMessage())) {
                $response['message'] = $this->getMessage();
            }
            // append data in response array
            if (!empty($this->getData())) {
                $response['data'] = $this->getData();
            }
            // append meta in response array
            if (!empty($this->getMeta())) {
                $response['meta'] = $this->getMeta();
            }
        }
        // returning response
        return $response;
    }
}
