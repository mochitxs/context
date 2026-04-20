<?php
/**
 * Archivo: login.php
 *
 * Este archivo gestiona el inicio de sesión de usuarios en CONTEXT.
 *
 * Funciones principales:
 * - Mostrar el formulario de login
 * - Validar los datos introducidos
 * - Buscar el usuario por email
 * - Verificar la contraseña hasheada
 * - Crear la sesión del usuario
 * - Redirigir a la página principal
 */

// Activamos la visualización de errores durante el desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciamos la sesión para poder guardar datos del usuario logueado
session_start();

// Incluimos la conexión a la base de datos
require_once '../config/db.php';

// Variable para mensajes de error o estado
$message = "";

/**
 * Si el usuario ya tiene sesión iniciada,
 * lo redirigimos directamente al inicio.
 */
if (isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

/**
 * Procesamos el formulario solo si se ha enviado por método POST
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recogemos y limpiamos los datos enviados por el formulario
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    /**
     * Validaciones básicas:
     * - Comprobar que los campos no estén vacíos
     */
    if (empty($email) || empty($password)) {
        $message = "Todos los campos son obligatorios.";
    } else {
        /**
         * Buscamos al usuario en la base de datos a partir del email.
         * Usamos una consulta preparada para evitar inyección SQL.
         */
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        // Recuperamos el usuario como array asociativo
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        /**
         * Verificamos:
         * - que el usuario exista
         * - que la contraseña introducida coincida con el hash guardado
         */
        if ($user && password_verify($password, $user["password_hash"])) {

            /**
             * Guardamos en sesión los datos básicos del usuario.
             * Esto permitirá identificarlo en el resto de páginas.
             */
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            /**
             * Redirigimos a la página principal tras login correcto
             */
            header("Location: ../index.php");
            exit;
        } else {
            $message = "Email o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <!-- Hace que la página se adapte correctamente al ancho de pantalla -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - CONTEXT</title>

    <!-- Enlace al archivo de estilos -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">

</head>
<body class="auth-page">

    <!-- Botón superior para ir a registro -->
    <a href="register.php" class="top-login-link">Crear cuenta</a>

    <main class="auth-wrapper">
        <section class="auth-card">

            <!-- Encabezado principal -->
            <div class="auth-heading">
                <h1 class="auth-title">
                    <span>inicia sesión</span>
                </h1>
                <p class="auth-subtitle">y vuelve a context;</p>
            </div>

            <!-- Mostramos mensaje si existe -->
            <?php if (!empty($message)): ?>
                <p class="auth-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <!-- Formulario de inicio de sesión -->
            <form method="POST" action="" class="auth-form">

                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >

                <label for="password">Contraseña</label>
                <div class="password-field">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword('password', this)"
                    >
                        Ver
                    </button>
                </div>

                <!-- Botón para enviar el formulario -->
                <button type="submit" class="auth-button">Entrar</button>
            </form>
        </section>
    </main>

    <script>
        /**
         * Alterna la visibilidad del campo de contraseña.
         *
         * @param {string} inputId - ID del input de contraseña
         * @param {HTMLElement} button - Botón pulsado
         */
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);

            if (input.type === "password") {
                input.type = "text";
                button.textContent = "Ocultar";
            } else {
                input.type = "password";
                button.textContent = "Ver";
            }
        }
    </script>
</body>
</html>