<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

$unreadNotificationsCount = 0;

if (isset($_SESSION['user_id'], $pdo) && $pdo instanceof PDO) {
    try {
        $unreadNotificationsCount = ctx_get_unread_notifications_count($pdo, (int) $_SESSION['user_id']);
    } catch (Throwable $exception) {
        $unreadNotificationsCount = 0;
    }
}
?>

<header class="main-header">
    <div class="main-header__logo">
        <a href="<?php echo htmlspecialchars(ctx_url('index.php')); ?>">cntxt;</a>
    </div>

    <nav class="main-header__nav">
        <a href="<?php echo htmlspecialchars(ctx_url('index.php')); ?>">home</a>
        <a href="<?php echo htmlspecialchars(ctx_url('explorar/index.php')); ?>">explorar</a>
        <a href="<?php echo htmlspecialchars(ctx_url('autores/index.php')); ?>">autores</a>
    </nav>

    <div class="main-header__actions">
        <?php if (isset($_SESSION["user_id"])): ?>
            <span class="user-greeting">
                Hola, <?php echo htmlspecialchars($_SESSION["username"]); ?>
            </span>

            <a href="<?php echo htmlspecialchars(ctx_url('settings/index.php')); ?>" class="header-user-button" aria-label="Abrir ajustes de usuario">
                <img
                    src="<?php echo htmlspecialchars(ctx_url('assets/images/user.png')); ?>"
                    alt="Usuario"
                    class="header-user-button__icon"
                >
                <?php if ($unreadNotificationsCount > 0): ?>
                    <span class="header-user-button__count"><?php echo $unreadNotificationsCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo htmlspecialchars(ctx_url('posts/create.php')); ?>" class="create-button">+ Crea</a>
            <a href="<?php echo htmlspecialchars(ctx_url('auth/logout.php')); ?>" class="logout-link">Salir</a>

        <?php else: ?>

            <a href="<?php echo htmlspecialchars(ctx_url('auth/register.php')); ?>" class="create-button">Entra</a>

        <?php endif; ?>
    </div>
</header>
