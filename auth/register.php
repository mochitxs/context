<?php
/**
 * Archivo: register.php
 *
 * Este archivo gestiona el registro de nuevos usuarios en CONTEXT.
 *
 * Funciones principales:
 * - Mostrar el formulario de registro
 * - Validar los datos introducidos
 * - Comprobar si el email ya existe en la base de datos
 * - Generar un hash seguro de la contraseña
 * - Insertar el nuevo usuario en la tabla users
 * - Iniciar sesión automáticamente tras el registro correcto
 */

// Activamos la visualización de errores durante el desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluimos la conexión a la base de datos
require_once '../config/db.php';

// Iniciamos la sesión para poder dejar al usuario conectado tras registrarse.
session_start();

// Variable para guardar mensajes de error o éxito
$message = "";

/**
 * Si el usuario ya tiene sesión iniciada,
 * no tiene sentido mostrarle el formulario de registro.
 */
if (isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

/**
 * Comprobamos si el formulario ha sido enviado.
 * Solo procesamos los datos cuando el método de envío es POST.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recogemos y limpiamos los datos del formulario
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    /**
     * Validaciones del formulario:
     * 1. Que ningún campo esté vacío
     * 2. Que el email tenga un formato válido
     * 3. Que ambas contraseñas coincidan
     */
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Todos los campos son obligatorios.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "El email no es válido.";

    } elseif ($password !== $confirm_password) {
        $message = "Las contraseñas no coinciden.";

    } else {
        /**
         * Comprobamos si ya existe un usuario con ese email.
         * Usamos una consulta preparada para evitar inyección SQL.
         */
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = "Ya existe una cuenta con ese email.";
            if (isset($_GET["success"])) {
                $message = "Usuario registrado correctamente.";
            }
        } else {
            /**
             * Generamos un hash seguro de la contraseña.
             * No se guarda nunca la contraseña en texto plano.
             */
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            /**
             * Insertamos el nuevo usuario en la base de datos.
             * Por defecto, el rol asignado será 'user'.
             */
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role)
                VALUES (?, ?, ?, 'user')
            ");
            $stmt->execute([$username, $email, $password_hash]);

            /**
             * Tras registrar el usuario, creamos la sesión con los mismos datos
             * que usa login.php. Así la experiencia es más fluida: la persona
             * entra directamente en CONTEXT sin tener que iniciar sesión otra vez.
             */
            $_SESSION["user_id"] = (int) $pdo->lastInsertId();
            $_SESSION["username"] = $username;
            $_SESSION["role"] = "user";

            header("Location: ../index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <!-- Hace que la página se adapte correctamente en móvil -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registro - CONTEXT</title>

    <!-- Enlace al archivo CSS general -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">

</head>
<body class="auth-page">

    <!-- Botón superior para ir a la página de login -->
    <a href="login.php" class="top-login-link">Ya tengo una cuenta</a>

    <main class="auth-wrapper">
        <section class="auth-card">

            <!-- Encabezado principal del formulario -->
            <div class="auth-heading">
                <h1 class="auth-title">
                    <span>regístrate</span>
                </h1>
                <p class="auth-subtitle">y forma parte de context;</p>
            </div>

            <!-- Mostramos mensaje de error o éxito si existe -->
            <?php if (!empty($message)): ?>
                <p class="auth-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <!-- Formulario de registro -->
            <form method="POST" action="" class="auth-form">

                <label for="username">Nombre de usuario</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                >

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
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">
                            Ver
                        </button>
                    </div>

                    <label for="confirm_password">Confirmar contraseña</label>
                    <div class="password-field">
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                        >
                        <!-- Botón que alterna visibilidad de la contraseña -->
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">
                            Ver
                        </button>
                    </div>

                <!-- Botón para enviar el formulario -->
                <button type="submit" class="auth-button">Unirse</button>
            </form>
        </section>
    </main>

    <script>
    /**
     * Función para alternar la visibilidad de la contraseña.
     * 
     * @param {string} inputId - ID del input de contraseña
     * @param {HTMLElement} button - Botón que dispara la acción
     * 
     * Funcionamiento:
     * - Si el input es tipo "password" → lo cambia a "text"
     * - Si es "text" → vuelve a "password"
     * - Cambia el texto del botón (Ver / Ocultar)
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
