<?php

namespace Ondine;

class Response
{
    protected $status;
    protected $data;
    protected $headers = [];
    // keep last status set via setStatusCode for testability
    protected static $lastStatus = 200;

    public function __construct(int $status = 200, $data = null, array $headers = [])
    {
        $this->status = $status;
        $this->data = $data;
        $this->headers = $headers;
    }

    public static function setStatusCode(int $code)
    {
        // Avoid headers sent warning in CLI/test env
        if (!headers_sent()) {
            http_response_code($code);
        }
        // always store last status for tests/inspection
        self::$lastStatus = $code;
    }

    public function setHeader(string $key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    // Public getters for tests and middleware
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the last status code set via setStatusCode().
     */
    public static function getLastStatus(): int
    {
        return (int)self::$lastStatus;
    }

    public function send()
    {
        http_response_code($this->status);
        header('Content-Type: application/json');

        foreach ($this->headers as $k => $v) {
            header("$k: $v");
        }

        echo json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }
}
