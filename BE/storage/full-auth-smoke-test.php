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

function getToken($response) {
    $body = json_decode($response->getContent(), true);

    return data_get($body, 'data.access_token')
        ?? data_get($body, 'data.token')
        ?? data_get($body, 'access_token')
        ?? data_get($body, 'token');
}

$loginLearner = runRequest('POST', '/api/auth/login', [
    'email' => 'learner1@mindhub.test',
    'password' => 'password',
]);

$token = getToken($loginLearner);

if ($token) {
    runRequest('GET', '/api/auth/me', [], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    runRequest('POST', '/api/auth/logout', [], [
        'Authorization' => 'Bearer ' . $token,
    ]);
} else {
    echo PHP_EOL . 'TOKEN_NOT_FOUND_AFTER_LOGIN' . PHP_EOL;
}

runRequest('POST', '/api/auth/login', [
    'email' => 'learner1@mindhub.test',
    'password' => 'wrong-password',
]);

runRequest('POST', '/api/auth/login', [
    'email' => '',
    'password' => '',
]);

$unique = 'testuser_' . time() . '@mindhub.test';

runRequest('POST', '/api/auth/register', [
    'full_name' => 'Test Register Learner',
    'name' => 'Test Register Learner',
    'email' => $unique,
    'phone' => '0987654321',
    'password' => 'password',
    'password_confirmation' => 'password',
]);

runRequest('POST', '/api/auth/register/learner', [
    'full_name' => 'Test Register Learner 2',
    'name' => 'Test Register Learner 2',
    'email' => 'learner_' . time() . '@mindhub.test',
    'phone' => '0987654322',
    'password' => 'password',
    'password_confirmation' => 'password',
]);

runRequest('POST', '/api/auth/register/instructor', [
    'full_name' => 'Test Register Instructor',
    'name' => 'Test Register Instructor',
    'email' => 'instructor_' . time() . '@mindhub.test',
    'phone' => '0987654323',
    'password' => 'password',
    'password_confirmation' => 'password',
    'bio' => 'Test instructor bio',
    'headline' => 'Test instructor headline',
]);

runRequest('POST', '/api/auth/forgot-password', [
    'email' => 'learner1@mindhub.test',
]);

runRequest('POST', '/api/auth/reset-password', [
    'email' => 'learner1@mindhub.test',
    'token' => 'fake-token',
    'password' => 'password',
    'password_confirmation' => 'password',
]);

runRequest('POST', '/api/auth/verify-email/resend', [
    'email' => 'learner1@mindhub.test',
]);

runRequest('GET', '/api/auth/verify-email/999999/wrong-hash');

runRequest('POST', '/api/auth/google', []);

runRequest('POST', '/api/auth/google', [
    'id_token' => 'fake-token',
]);

runRequest('POST', '/api/auth/google', [
    'access_token' => 'fake-token',
]);