<?php
/**
 * Conexión PDO con fallback.
 *
 * Se prueban varias configuraciones habituales de XAMPP/local para evitar
 * que la app dependa exclusivamente del socket de `localhost`.
 */
$dbname = 'context_db';
$username = 'root';
$password = '';

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
