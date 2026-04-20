<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autores - CONTEXT</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/section.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body class="section-page">

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="section-layout">
    <section class="section-card">
        <div class="section-card__header">
            <h1 class="section-card__title">
                <span>autores</span>
            </h1>
        </div>

        <p class="section-empty">
            Esta vista se ha dejado conectada para la entrega, pero el directorio de autores aún está en construcción.
        </p>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
