<?php

namespace Core\Routing\Objects;

use Core\Contracts\Arrayable;
use Core\Routing\Enums\Method;

class Request implements Arrayable
{
    /**
     * Hostname (domain) of the request.
     *
     * @var string
     */
    public string $host;

    /**
     * Everything after the hostname, with query parameters stripped.
     *
     * @var string
     */
    public string $uri;

    /**
     * Which HTTP verb this request used
     *
     * @var Method
     */
    public Method $method;

    /**
     * The request's user-agent string.
     *
     * @var string|null
     */
    public ?string $userAgent;

    /**
     * List of accepted response types, such as `application/json` in order of most to least preferred.
     *
     * @var array<string>|null
     */
    public ?array $accepts;

    /**
     * The request's headers.
     *
     * @var array<string, string>
     */
    public array $headers;

    /**
     * The Request's parameters.
     *
     * @var array<string,string>
     */
    public array $params;

    /**
     * The Request's cookies.
     *
     * @var array
     */
    public array $cookies;


    /**
     * Initializes a bare Request object without populating any data.
     */
    public function __construct() {}

    /**
     * Builds the request object and populates it with data from PHP's superglobals.
     *
     * @return static
     */
    public static function make(): static
    {
        $request = new static();

        $request->host = $_SERVER['HTTP_HOST'];
        $request->uri = $_SERVER['REQUEST_URI'];
        $request->method = Method::tryFrom(strtolower($_SERVER['REQUEST_METHOD'])) ?? Method::UNSUPPORTED;
        $request->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $request->accepts = explode(',', $_SERVER['HTTP_ACCEPT'] ?? '') ?: null;
        $request->headers = self::getHeaders();
        $request->params = $_GET;
        $request->cookies = $_COOKIE;
        // todo: this currently doesn't capture anything from $_POST because I haven't tinkered with it yet

        return $request;
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

    protected static function getHeaders(): array
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

    protected static function normalizeHeaderKey(string $key, bool $stripHtmlPrefix): string
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