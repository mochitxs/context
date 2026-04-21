<?php
/**
 * Portada principal de CONTEXT.
 */
session_start();
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$randomFeaturedArticle = null;
$recentArticles = [];

try {
    /**
     * Elegimos un artículo publicado de forma determinista por día.
     *
     * Así el bloque "Artículo del día" se mantiene estable durante toda la
     * jornada, pero cambia automáticamente al día siguiente.
     *
     * En lugar de usar un aleatorio puro, calculamos una rotación diaria
     * sobre todos los artículos publicados. Así el comportamiento es más
     * fácil de comprobar y de explicar en la presentación final.
     */
    $todayInMadrid = new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid'));
    $articlesCountStmt = $pdo->query("
        SELECT COUNT(*)
        FROM posts
        WHERE status = 'published'
          AND type = 'article'
    ");
    $articlesCount = (int) $articlesCountStmt->fetchColumn();

    if ($articlesCount > 0) {
        $dayNumber = (((int) $todayInMadrid->format('Y')) * 366) + ((int) $todayInMadrid->format('z'));
        $dailyOffset = $dayNumber % $articlesCount;

        $featuredArticleStmt = $pdo->prepare("
            SELECT
                posts.id,
                posts.title,
                posts.created_at,
                posts.cover_image,
                categories.name AS category_name,
                users.username AS author_name
            FROM posts
            INNER JOIN categories ON posts.category_id = categories.id
            INNER JOIN users ON posts.user_id = users.id
            WHERE posts.status = 'published'
              AND posts.type = 'article'
            ORDER BY posts.created_at DESC, posts.id DESC
            LIMIT 1 OFFSET :daily_offset
        ");
        $featuredArticleStmt->bindValue(':daily_offset', $dailyOffset, PDO::PARAM_INT);
        $featuredArticleStmt->execute();

        $randomFeaturedArticle = $featuredArticleStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
} catch (Throwable $exception) {
    $randomFeaturedArticle = null;
}

try {
    /**
     * Recupera los tres artículos más recientes para completar la portada
     * con un bloque editorial de lectura rápida.
     */
    $recentArticlesStmt = $pdo->query("
        SELECT
            posts.id,
            posts.title,
            posts.content,
            posts.created_at,
            posts.cover_image,
            categories.name AS category_name,
            users.username AS author_name
        FROM posts
        INNER JOIN categories ON posts.category_id = categories.id
        INNER JOIN users ON posts.user_id = users.id
        WHERE posts.status = 'published'
          AND posts.type = 'article'
        ORDER BY posts.created_at DESC, posts.id DESC
        LIMIT 3
    ");

    $recentArticles = $recentArticlesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $exception) {
    $recentArticles = [];
}
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

    <script>
        window.CONTEXT_HOME_DATA = {
            featuredArticle: <?php echo json_encode(
                $randomFeaturedArticle ? [
                    'headline' => $randomFeaturedArticle['title'],
                    'author' => $randomFeaturedArticle['author_name'],
                    'date' => date('d / m / Y', strtotime($randomFeaturedArticle['created_at'])),
                    'categoryPills' => [$randomFeaturedArticle['category_name']],
                    'imageAlt' => 'Imagen del artículo del día',
                    'imageSrc' => !empty($randomFeaturedArticle['cover_image'])
                        ? $randomFeaturedArticle['cover_image']
                        : 'assets/images/clean_girl.jpeg',
                    'detailUrl' => 'articles/detail.php?id=' . (int) $randomFeaturedArticle['id']
                ] : null,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ); ?>,
            recentArticles: <?php echo json_encode(
                array_map(
                    static fn(array $article): array => [
                        'headline' => $article['title'],
                        'author' => $article['author_name'],
                        'date' => date('d / m / Y', strtotime($article['created_at'])),
                        'category' => $article['category_name'],
                        'excerpt' => ctx_excerpt($article['content'], 120),
                        'imageAlt' => 'Imagen del artículo ' . $article['title'],
                        'imageSrc' => !empty($article['cover_image'])
                            ? $article['cover_image']
                            : 'assets/images/clean_girl.jpeg',
                        'detailUrl' => 'articles/detail.php?id=' . (int) $article['id']
                    ],
                    $recentArticles
                ),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ); ?>
        };
    </script>

    <!-- React App -->
    <script type="text/babel" src="assets/js/home-app.js"></script>

    <?php require_once 'includes/footer.php'; ?>


                
</body>
</html>
