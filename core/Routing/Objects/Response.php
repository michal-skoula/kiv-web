<?php

namespace Core\Routing\Objects;

class Response
{
    /**
     * HTTP status code, ex. 200, 301, 404.
     *
     * @var int
     */
    protected int $status;

    /**
     * Response headers, ex. `Content-Type: application/json`.
     *
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * Response cookies.
     *
     * @var array<string, array{value: string, options: array}>
     */
    protected array $cookies = [];

    /**
     * Body of the response.
     *
     * @var string|null
     */
    protected ?string $body = null;

    public static function make(int $status = 200): static
    {
        $response = new static();
        $response->status = $status;

        return $response;
    }

    public function status(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function body(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function addHeader(string $header, string $value): static
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function setCookie(string $name, string $value, array $options = []): static
    {
        $this->cookies[$name]['value'] = $value;
        $this->cookies[$name]['options'] = $options;
        return $this;
    }

    public static function redirect(string $url, bool $permanent = false): static
    {
        $response = new static();
        $response->status($permanent ? 301 : 302);
        $response->headers['Location'] = $url ;
        return $response;
    }

    public function send(): never
    {
        foreach ($this->headers as $header => $value) {
            header("{$header}: {$value}");
        }

        foreach ($this->cookies as $cookie => $data) {
            setcookie($cookie, $data['value'], $data['options']);
        }

        http_response_code($this->status);

        echo $this->body;

        exit(0);
    }
}