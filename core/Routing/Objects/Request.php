<?php

namespace Core\Routing\Objects;

use Core\Contracts\Arrayable;
use Core\Routing\Enums\Method;

final class Request implements Arrayable
{
    /**
     * @param string $host Hostname (domain) of the request.
     * @param string $uri Everything after the hostname, with query parameters stripped.
     * @param Method $method Which HTTP verb this request used.
     * @param string|null $userAgent The request's user-agent string.
     * @param array<string>|null $accepts List of accepted response types, such as `application/json`, in order of most to least preferred.
     * @param array<string, string> $headers The request's headers.
     * @param array<string, string> $params The request's parameters.
     * @param array $cookies The request's cookies.
     */
    public function __construct(
        public string $host = 'localhost',
        public string $uri = '/',
        public Method $method = Method::GET,
        public ?string $userAgent = null,
        public ?array $accepts = null,
        public array $headers = [],
        public array $params = [],
        public array $cookies = [],
    ) {}

    /**
     * Builds the request object and populates it with data from PHP's superglobals.
     *
     * @return self
     */
    public static function make(): self
    {
        // todo: this currently doesn't capture anything from $_POST because I haven't tinkered with it yet
        return new self(
            host: $_SERVER['HTTP_HOST'],
            uri: parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            method: Method::tryFrom(strtolower($_SERVER['REQUEST_METHOD'])) ?? Method::UNSUPPORTED,
            userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null,
            accepts: explode(',', $_SERVER['HTTP_ACCEPT'] ?? '') ?: null,
            headers: self::getHeaders(),
            params: $_GET,
            cookies: $_COOKIE,
        );
    }

    /**
     * Finds the Request's preferred content type from a list of options.
     *
     * @param array $options Which options to look for.
     * @return string|null The most preffered option, or null if none matched.
     */
    public function preferredContentType(array $options): ?string
    {
        foreach($this->accepts as $option) {
            if(in_array($option, $options)) {
                return $option;
            }
        }

        return null;
    }

    private static function getHeaders(): array
    {
        $headers = [];

        foreach($_SERVER as $key => $value) {
            if(! str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $headers[self::normalizeHeaderKey($key, stripHtmlPrefix: true)] = $value;
        }

        // These aren't prefixed with HTTP_
        foreach (['CONTENT_TYPE', 'CONTENT_LENGTH'] as $key) {
            if (isset($_SERVER[$key])) {
                $headers[self::normalizeHeaderKey($key, stripHtmlPrefix: false)] = $_SERVER[$key];
            }
        }

        return $headers;
    }

    private static function normalizeHeaderKey(string $key, bool $stripHtmlPrefix): string
    {
        $offset = $stripHtmlPrefix ? 5 : 0;
        return strtolower(str_replace('_', '-', substr($key, $offset)));
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'uri' => $this->uri,
            'method' => $this->method->value,
            'userAgent' => $this->userAgent,
            'accepts' => $this->accepts,
            'headers' => $this->headers,
            'params' => $this->params,
            'cookies' => $this->cookies
        ];
    }
}