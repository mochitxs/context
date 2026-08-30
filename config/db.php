<?php
/**
 * Conexión PDO con fallback.
 *
 * Se prueban varias configuraciones habituales de XAMPP/local para evitar
 * que la app dependa exclusivamente del socket de `localhost`.
 *
 * Las credenciales se leen de variables de entorno (definidas en `.env`
 * localmente, o en la configuración del hosting en producción). Si no
 * existen, se usan los valores por defecto de XAMPP para desarrollo local.
 */

// Cargamos el .env si existe (solo en local; en producción el hosting
// suele inyectar las variables de entorno directamente).
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$dbname   = getenv('DB_NAME') ?: 'context_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

$dsnCandidates = [
    "mysql:host=localhost;dbname=$dbname;charset=utf8mb4",
    "mysql:host=127.0.0.1;port=3306;dbname=$dbname;charset=utf8mb4",
    "mysql:unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock;dbname=$dbname;charset=utf8mb4"
];

$pdo = null;
$lastException = null;

foreach ($dsnCandidates as $dsn) {
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        break;
    } catch (PDOException $exception) {
        $lastException = $exception;
    }
}

if (!$pdo) {
    die('Error de conexión ;(( : ' . ($lastException ? $lastException->getMessage() : 'No se pudo conectar a la base de datos.'));
}































