<?php require_once __DIR__ . '/functions.php'; ?>

<footer class="main-footer">
    <div class="main-footer__inner">
        <div class="main-footer__brand-block">
            <h2 class="main-footer__brand">context;</h2>
            <p class="main-footer__description">
                Un espacio editorial donde la juventud comparte cultura,
                opinión e identidad en contexto.
            </p>
        </div>

        <div class="main-footer__nav-grid">
            <div class="main-footer__column">
                <h3>Explorar</h3>
                <a href="<?php echo htmlspecialchars(ctx_url('posts/view.php')); ?>">Posts</a>
                <a href="<?php echo htmlspecialchars(ctx_url('articles/view.php')); ?>">Artículos</a>
            </div>

            <div class="main-footer__column">
                <h3>Categorías</h3>
                <a href="<?php echo htmlspecialchars(ctx_url('sections/moda-musica.php')); ?>">Moda</a>
                <a href="<?php echo htmlspecialchars(ctx_url('sections/arte-cultura.php')); ?>">Cultura</a>
                <a href="<?php echo htmlspecialchars(ctx_url('sections/sociedad-opinion.php')); ?>">Sociedad</a>
            </div>

            <div class="main-footer__column">
                <h3>Cuenta</h3>
                <a href="<?php echo htmlspecialchars(ctx_url('auth/register.php')); ?>">Entra</a>
                <a href="<?php echo htmlspecialchars(ctx_url('auth/login.php')); ?>">Login</a>
                <a href="<?php echo htmlspecialchars(ctx_url('posts/create.php')); ?>">Crear</a>
            </div>
        </div>
    </div>

    <div class="main-footer__bottom">
        <p>© 2026 CONTEXT</p>
    </div>
</footer>

<script src="<?php echo htmlspecialchars(ctx_url('assets/js/reveal.js')); ?>"></script>
