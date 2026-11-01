<?php

namespace Core\Routing\Objects;

class Response
{
    protected int $status;

    protected View|string|null $body = null;

    protected array $headers = [];

    protected array $cookies = [];

    public static function make(): static
    {
        return new static();
    }

    public function status(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function view(View $view): static
    {
        $this->body = $view;
        return $this;
    }

    public function addHeader(string $header, string $value): static
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function setCookie(string $name, string $value): static
    {
        $this->cookies[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        if(! $this->body) {
            return;
        }

        if($this->body instanceof View) {
            $this->body->render();
            return;
        }

        echo $this->body;
    }
}