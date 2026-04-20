<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/**
 * Categorías que pertenecen a esta sección editorial.
 */
$sectionCategories = ['Arte', 'Cultura'];

/**
 * Cargamos artículos publicados que pertenezcan a Arte o Cultura.
 * El primero será el destacado principal.
 */
$placeholders = implode(',', array_fill(0, count($sectionCategories), '?'));

$sql = "
    SELECT 
        posts.id,
        posts.title,
        posts.content,
        posts.cover_image,
        posts.created_at,
        categories.name AS category_name,
        users.username AS author_name
    FROM posts
    INNER JOIN categories ON posts.category_id = categories.id
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.status = 'published'
      AND posts.type = 'article'
      AND categories.name IN ($placeholders)
    ORDER BY posts.created_at DESC
    LIMIT 4
";

$stmt = $pdo->prepare($sql);
$stmt->execute($sectionCategories);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Separamos el artículo principal del resto.
 */
$featuredArticle = $articles[0] ?? null;
$secondaryArticles = array_slice($articles, 1);

/**
 * Devuelve un resumen corto del contenido.
 */
function getExcerpt(string $text, int $length = 220): string
{
    $text = trim(strip_tags($text));

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . '...';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arte y cultura - CONTEXT</title>

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
                    <span>arte y cultura</span>
                </h1>
            </div>

            <?php if ($featuredArticle): ?>
                <article class="section-featured">
                    <a href="../articles/detail.php?id=<?php echo (int) $featuredArticle['id']; ?>" class="section-featured__image-link">
                        <?php if (!empty($featuredArticle['cover_image'])): ?>
                            <img
                                src="../<?php echo htmlspecialchars($featuredArticle['cover_image']); ?>"
                                alt="<?php echo htmlspecialchars($featuredArticle['title']); ?>"
                                class="section-featured__image"
                            >
                        <?php else: ?>
                            <div class="section-featured__image section-featured__image--placeholder"></div>
                        <?php endif; ?>
                    </a>

                    <div class="section-featured__content">
                        <span class="tag-pill pill--<?php echo htmlspecialchars(ctx_normalize_category_class($featuredArticle['category_name'])); ?>">
                            <?php echo htmlspecialchars($featuredArticle['category_name']); ?>
                        </span>

                        <h2 class="section-featured__headline">
                            <a href="../articles/detail.php?id=<?php echo (int) $featuredArticle['id']; ?>">
                                <?php echo htmlspecialchars($featuredArticle['title']); ?>
                            </a>
                        </h2>

                        <p class="section-featured__excerpt">
                            <?php echo htmlspecialchars(ctx_excerpt($featuredArticle['content'], 260)); ?>
                        </p>

                        <p class="section-featured__author">
                            <?php echo htmlspecialchars($featuredArticle['author_name']); ?>
                        </p>
                    </div>
                </article>
            <?php else: ?>
                <p class="section-empty">Todavía no hay artículos publicados en esta sección.</p>
            <?php endif; ?>

            <?php if (!empty($secondaryArticles)): ?>
                <div class="section-grid">
                    <?php foreach ($secondaryArticles as $article): ?>
                        <article class="section-grid__card">
                            <a href="../articles/detail.php?id=<?php echo (int) $article['id']; ?>" class="section-grid__image-link">
                                <?php if (!empty($article['cover_image'])): ?>
                                    <img
                                        src="../<?php echo htmlspecialchars($article['cover_image']); ?>"
                                        alt="<?php echo htmlspecialchars($article['title']); ?>"
                                        class="section-grid__image"
                                    >
                                <?php else: ?>
                                    <div class="section-grid__image section-grid__image--placeholder"></div>
                                <?php endif; ?>
                            </a>

                            <div class="section-grid__body">
                                <span class="tag-pill pill--<?php echo htmlspecialchars(ctx_normalize_category_class($article['category_name'])); ?>">
                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                </span>

                                <h3 class="section-grid__headline">
                                    <a href="../articles/detail.php?id=<?php echo (int) $article['id']; ?>">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h3>

                                <p class="section-grid__author">
                                    <?php echo htmlspecialchars($article['author_name']); ?>
                                </p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </section>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
