<?php
/**
 * Portada principal de CONTEXT.
 */
session_start();
require_once __DIR__ . '/includes/functions.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CONTEXT</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">

    <?php require_once __DIR__ . '/includes/frontend-deps.php'; ?>
</head>



<body class="home-page">

<?php require_once 'includes/header.php'; ?>


    <!-- =========================
         MAIN
    ========================= -->
    <main>

        <!-- HERO -->
        <section class="hero">
            <div class="hero__content">

                <h1 class="hero__title">
                    <span>context;</span>
                </h1>

                <p class="hero__subtitle">la juventud en contexto.</p>

                <?php if (isset($_SESSION["user_id"])): ?>

                    <a href="<?php echo htmlspecialchars(ctx_url('posts/create.php')); ?>" class="hero__button">
                        <span class="hero__dot"></span> Empieza a crear
                    </a>
                    <a href="#articulo-dia" class="hero__scroll-arrow"></a>

                    


                <?php else: ?>

                    <a href="<?php echo htmlspecialchars(ctx_url('auth/register.php')); ?>" class="hero__button">
                        <span class="hero__dot"></span> Empieza a crear
                    </a>
                    <a href="#articulo-dia" class="hero__scroll-arrow"></a>

                <?php endif; ?>

            </div>
        </section>

        <!-- ARTÍCULO DEL DÍA (React) -->
        <section id="articulo-dia" class="featured-article-section">
            <div id="featured-article-root"></div>
        </section>

    </main>

    <!-- React App -->
    <script type="text/babel" src="assets/js/home-app.js"></script>

    <?php require_once 'includes/footer.php'; ?>


                
</body>
</html>
