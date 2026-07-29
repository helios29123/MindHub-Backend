<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

function runRequest($method, $uri, $payload = [], $headers = []) {
    global $kernel;

    $server = [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/json',
    ];

    foreach ($headers as $key => $value) {
        $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
    }

    $request = Illuminate\Http\Request::create(
        $uri,
        $method,
        [],
        [],
        [],
        $server,
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    $response = $kernel->handle($request);

    echo PHP_EOL . $method . ' ' . $uri . PHP_EOL;
    echo 'STATUS=' . $response->getStatusCode() . PHP_EOL;
    echo $response->getContent() . PHP_EOL;

    $kernel->terminate($request, $response);

    return $response;
}

$login = runRequest('POST', '/api/auth/login', [
    'email' => 'learner1@mindhub.test',
    'password' => 'password',
]);

$body = json_decode($login->getContent(), true);
$token = data_get($body, 'data.token')
    ?? data_get($body, 'token')
    ?? data_get($body, 'data.access_token')
    ?? data_get($body, 'access_token');

if ($token) {
    runRequest('GET', '/api/auth/me', [], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    runRequest('POST', '/api/auth/logout', [], [
        'Authorization' => 'Bearer ' . $token,
    ]);
} else {
    echo PHP_EOL . 'TOKEN_NOT_FOUND' . PHP_EOL;
}