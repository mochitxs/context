<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';
?>

<header class="main-header">
    <div class="main-header__logo">
        <a href="<?php echo htmlspecialchars(ctx_url('index.php')); ?>">cntxt;</a>
    </div>

    <nav class="main-header__nav">
        <a href="<?php echo htmlspecialchars(ctx_url('index.php')); ?>">home</a>
        <a href="<?php echo htmlspecialchars(ctx_url('explorar/index.php')); ?>">explorar</a>
        <a href="<?php echo htmlspecialchars(ctx_url('posts/view.php')); ?>">posts</a>
        <a href="<?php echo htmlspecialchars(ctx_url('articles/view.php')); ?>">artículos</a>
        <a href="<?php echo htmlspecialchars(ctx_url('authors/index.php')); ?>">autores</a>
    </nav>

    <div class="main-header__actions">
        <?php if (isset($_SESSION["user_id"])): ?>
            <span class="user-greeting">
                Hola, <?php echo htmlspecialchars($_SESSION["username"]); ?>
            </span>

            <a href="<?php echo htmlspecialchars(ctx_url('posts/create.php')); ?>" class="create-button">+ Crea</a>
            <a href="<?php echo htmlspecialchars(ctx_url('auth/logout.php')); ?>" class="logout-link">Salir</a>

        <?php else: ?>

            <a href="<?php echo htmlspecialchars(ctx_url('auth/register.php')); ?>" class="create-button">Entra</a>

        <?php endif; ?>
    </div>
</header>
