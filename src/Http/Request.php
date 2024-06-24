<?php

namespace Sentgine\Ray\Http;

class Request
{
    /** @var array The query parameters. */
    protected array $query;

    /** @var array The request parameters. */
    protected array $request;

    /** @var array The server parameters. */
    protected array $server;

    /** @var array The uploaded files. */
    protected array $files;

    /** @var array The JSON decoded request body. */
    protected array $json;

    /**
     * Request constructor.
     *
     * @param array $query The query parameters. Defaults to $_GET.
     * @param array $request The request parameters. Defaults to $_POST.
     * @param array $server The server parameters. Defaults to $_SERVER.
     * @param array $files The uploaded files. Defaults to $_FILES.
     */
    public function __construct(array $query = [], array $request = [], array $server = [], array $files = [])
    {
        $this->query = $query ?: $_GET;
        $this->request = $request ?: $_POST;
        $this->server = $server ?: $_SERVER;
        $this->files = $files ?: $_FILES;
        $this->json = json_decode(file_get_contents('php://input'), true) ?: [];
    }

    /**
     * Creates a new request instance from the global variables.
     *
     * @return static
     */
    public static function createFromGlobals(): static
    {
        return new static($_GET, $_POST, $_SERVER, $_FILES);
    }

    /**
     * Gets the request URI.
     *
     * @return string
     */
    public function requestUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Gets the request method.
     *
     * @return string
     */
    public function requestMethod(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Gets the value of a request parameter or JSON body, or returns a default value if not found.
     *
     * @param string $key The parameter key.
     * @param mixed $default The default value to return if the parameter is not found.
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $this->json[$key] ?? $default;
    }

    /**
     * Sets a value in the request parameters.
     *
     * @param string $key The parameter key.
     * @param mixed $value The value to set.
     * @return void
     */
    public function setInput(string $key, mixed $value): void
    {
        $this->request[$key] = $value;
    }

    /**
     * Gets the value of a query parameter, or returns a default value if not found.
     *
     * @param string $key The parameter key.
     * @param mixed $default The default value to return if the parameter is not found.
     * @return mixed
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Unsets a request parameter.
     *
     * @param string $key The parameter key.
     * @return void
     */
    public function unsetInput(string $key): void
    {
        unset($this->request[$key]);
    }

    /**
     * Gets all request and JSON body parameters.
     *
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->request, $this->json);
    }

    /**
     * Gets an uploaded file.
     *
     * @param string $key The file key.
     * @return array|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Checks if the request content type is JSON.
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return isset($this->server['CONTENT_TYPE']) && strpos($this->server['CONTENT_TYPE'], 'application/json') === 0;
    }

    /**
     * Sanitizes a request parameter value.
     *
     * @param string $key The parameter key.
     * @param mixed $default The default value to return if the parameter is not found.
     * @return mixed
     */
    public function sanitizeInput(string $key, mixed $default = null): mixed
    {
        $value = $this->input($key, $default);
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    /**
     * Validates a request parameter value against a given type.
     *
     * @param string $key The parameter key.
     * @param string $type The type to validate against. Can be 'int', 'email', or 'url'.
     * @return bool
     */
    public function validateInput(string $key, string $type): bool
    {
        $value = $this->input($key);
        return match ($type) {
            'int' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            default => false,
        };
    }
}
