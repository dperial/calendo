<?php
function base_url(): string {
    // set this in your env if your local URL differs
    return getenv('BASE_URL') ?: 'http://localhost/calendo';
}

function http_get(string $url, array $headers = []): array {
    $ch = curl_init($url);
    $default = ['Accept: application/json'];
    // Ensure test env header exists unless caller provides one
    $allHeaders = array_merge($default, $headers);
    $hasEnv     = false;
    foreach ($allHeaders as $h) {
        if (stripos($h, 'X-Test-Env:') === 0) {
            $hasEnv = true;
            break;
        }
    }
    if (!$hasEnv) {
        $allHeaders[] = 'X-Test-Env: 1';
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER     => $allHeaders,
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
    curl_close($ch);
    return [$code, $body, $err];
}


function http_post_json(string $url, array $payload, array $headers = []): array {
    $ch = curl_init($url);
    $default = ['Content-Type: application/json'];
    $allHeaders = array_merge($default, $headers);
    $hasEnv     = false;
    foreach ($allHeaders as $h) {
        if (stripos($h, 'X-Test-Env:') === 0) {
            $hasEnv = true;
            break;
        }
    }
    if (!$hasEnv) {
        $allHeaders[] = 'X-Test-Env: 1';
    }
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => $allHeaders,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return [$code, $body, $err];
}
