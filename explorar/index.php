<?php
session_start();
require_once '../includes/functions.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar - CONTEXT</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/explore.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <?php require_once '../includes/frontend-deps.php'; ?>

</head>

<body class="explore-page">

<?php require_once '../includes/header.php'; ?>


    <!-- MAIN -->
    <main class="explore-layout">

        <section class="explore-hero">
            <h1 class="explore-title">
                <span>explora;</span>
            </h1>
            <p class="explore-subtitle">
                ¿qué quieres ver hoy?
            </p>
        </section>

        <section class="explore-options">

            <!-- POSTS -->
            <a href="../posts/view.php" class="explore-card explore-card--posts">
                <div class="explore-card__inner">
                    <h2>posts</h2>
                    <p>ideas rápidas, pensamientos, multimedia.</p>
                </div>
            </a>

            <!-- ARTÍCULOS -->
            <a href="../articles/view.php" class="explore-card explore-card--articles">
                <div class="explore-card__inner">
                    <h2>artículos</h2>
                    <p>piezas editoriales, opinión y cultura.</p>
                </div>
            </a>

        </section>

    </main>
<?php require_once '../includes/footer.php'; ?>
</body>
</html>
