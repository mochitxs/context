<?php
/**
 * Archivo: logout.php
 *
 * Este archivo destruye la sesión del usuario
 * y lo redirige a la página de login.
 */

session_start();

// Eliminamos todas las variables de sesión
session_unset();

// Destruimos la sesión
session_destroy();

// Redirigimos al login
header("Location: login.php");
exit;
?>