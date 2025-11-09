<?php
// Database connection - DO NOT MODIFY
$database_url = getenv('DATABASE_URL');

if ($database_url) {
    $url_parts = parse_url($database_url);
    
    $host = $url_parts['host'];
    $port = $url_parts['port'] ?? 5432;
    $dbname = ltrim($url_parts['path'], '/');
    $user = $url_parts['user'];
    $password = $url_parts['pass'];
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    if (isset($url_parts['query'])) {
        parse_str($url_parts['query'], $query_params);
        if (isset($query_params['sslmode'])) {
            $dsn .= ";sslmode={$query_params['sslmode']}";
        }
    }
    
    try {
        $pdo = new PDO(
            $dsn,
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
} else {
    die("DATABASE_URL environment variable not set");
}
