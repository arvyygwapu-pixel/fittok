
<?php

// ==========================================
// FITOK DATABASE CONNECTION
// RENDER + AIVEN MYSQL
// ==========================================

// CORS HEADERS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ==========================================
// DATABASE CREDENTIALS
// ==========================================

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '19820';
$dbname = getenv('DB_NAME') ?: 'defaultdb';
$username = getenv('DB_USER') ?: 'avnadmin';
$password = getenv('DB_PASS');

// Check credentials
if (!$host || !$username || !$password) {
    error_log("FITOK: Missing database credentials.");

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Database configuration is incomplete."
    ]);

    exit();
}

// ==========================================
// SSL CERTIFICATE
// ==========================================

$caFile = __DIR__ . '/ca.pem';

if (!file_exists($caFile)) {
    error_log("FITOK: Aiven CA certificate missing.");

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "SSL certificate not found."
    ]);

    exit();
}

// ==========================================
// PDO CONNECTION
// ==========================================

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {

    $databaseConnection = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            PDO::ATTR_EMULATE_PREPARES => false,

            PDO::ATTR_TIMEOUT => 15,

            PDO::MYSQL_ATTR_SSL_CA => $caFile,

            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
        ]
    );

} catch (PDOException $e) {

    error_log("FITOK DATABASE ERROR: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed."
    ]);

    exit();
}

// No closing PHP tag to prevent whitespace output.
