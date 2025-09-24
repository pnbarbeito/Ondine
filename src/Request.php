<?php

namespace Ondine;

class Request
{
    public $method;
    public $path;
    public $headers = [];
    public $query = [];
    public $body;
    public $parsedBody = null;
    // populated by AuthMiddleware when authenticated
    public $user = null;
    public $token_payload = null;
    // place to store middleware-provided attributes (e.g. cors headers)
    public $attributes = [];

    public static function fromGlobals()
    {
        $req = new self();
        $req->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $req->path = parse_url($uri, PHP_URL_PATH);
        // Try to get headers; getallheaders() isn't available in all environments (e.g. some FPM setups).
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders() ?: [];
        } else {
            // Build headers from $_SERVER keys
            foreach ($_SERVER as $key => $val) {
                if (strpos($key, 'HTTP_') === 0) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                    $headers[$name] = $val;
                }
            }
            // Content-Type and Content-Length are not prefixed with HTTP_
            if (isset($_SERVER['CONTENT_TYPE'])) {
                $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
            }
            if (isset($_SERVER['CONTENT_LENGTH'])) {
                $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
            }
        }
        $req->headers = $headers;
        $req->query = $_GET;
        $req->body = file_get_contents('php://input');

        $contentType = $req->headers['Content-Type'] ?? $req->headers['content-type'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $req->parsedBody = json_decode($req->body, true);
        } elseif (!empty($_POST)) {
            $req->parsedBody = $_POST;
        }

        return $req;
    }
}
