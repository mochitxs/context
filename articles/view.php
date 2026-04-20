<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/**
 * Agrupaciones editoriales de artículos.
 * Usamos el nombre real de la categoría tal como está en la BD.
 */
$articleGroups = [
    [
        'title' => 'arte y cultura',
        'slug' => 'arte-cultura',
        'categories' => ['Arte', 'Cultura']
    ],
    [
        'title' => 'moda y música',
        'slug' => 'moda-musica',
        'categories' => ['Moda', 'Música']
    ],
    [
        'title' => 'sociedad, identidad y opinión',
        'slug' => 'sociedad-identidad-opinion',
        'categories' => ['Sociedad', 'Identidad', 'Opinión']
    ]
];

/**
 * Convierte el nombre de categoría en una clase CSS segura.
 */
function normalizeCategoryClass(string $name): string
{
    $map = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'ñ' => 'n', 'Ñ' => 'n'
    ];

    $name = strtr($name, $map);
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]+/', '-', $name);

    return trim($name, '-');
}

/**
 * Devuelve un resumen corto del contenido.
 */
function getExcerpt(string $text, int $length = 250): string
{
    $text = trim(strip_tags($text));

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . '...';
}

/**
 * Carga hasta 4 artículos publicados para un grupo de categorías.
 */
function fetchArticlesByCategories(PDO $pdo, array $categories): array
{
    if (empty($categories)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($categories), '?'));

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
    $stmt->execute($categories);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($articleGroups as &$group) {
    $group['articles'] = fetchArticlesByCategories($pdo, $group['categories']);
    $group['featured'] = $group['articles'][0] ?? null;
    $group['secondary'] = array_slice($group['articles'], 1);
}
unset($group);

$articlesCountStmt = $pdo->query("
    SELECT COUNT(*) 
    FROM posts
    WHERE status = 'published'
      AND type = 'article'
");
$articlesCount = (int) $articlesCountStmt->fetchColumn();

/**
 * Solo mostramos grupos que tengan contenido publicado.
 * Así evitamos enseñar bloques vacíos cuando todavía hay pocos artículos.
 */
$visibleArticleGroups = array_values(array_filter(
    $articleGroups,
    static fn(array $group): bool => !empty($group['articles'])
));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artículos - CONTEXT</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/article-view.css">
    <link rel="stylesheet" href="../assets/css/section.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <?php require_once '../includes/frontend-deps.php'; ?>
</head>
<body class="articles-view-page">

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="articles-view-layout">
    <div
        data-react-listing-summary
        data-total="<?php echo $articlesCount; ?>"
        data-label="artículos"
        data-description=""
    ></div>

    <section class="articles-view-hero">
        <h1 class="articles-view-title">
            <span>artículos;</span>
        </h1>
    </section>

    <?php if (empty($visibleArticleGroups)): ?>
        <section class="article-section-card">
            <div class="article-section-card__header">
                <h2 class="article-section-card__title">
                    <span>artículos</span>
                </h2>
            </div>

            <p class="article-section-card__empty">
                Todavía no hay artículos publicados. Crea el primero desde el editor para verlo aquí.
            </p>
        </section>
    <?php endif; ?>

    <?php foreach ($visibleArticleGroups as $group): ?>
        <section class="article-section-card" id="<?php echo htmlspecialchars($group['slug']); ?>">

            <div class="article-section-card__header">
                <h2 class="article-section-card__title">
                    <span><?php echo htmlspecialchars($group['title']); ?></span>
                </h2>
            </div>

            <?php if ($group['featured']): ?>
                <article class="article-section-featured">
                    <a href="../articles/detail.php?id=<?php echo (int) $group['featured']['id']; ?>" class="article-section-featured__image-link">
                        <?php if (!empty($group['featured']['cover_image'])): ?>
                            <img
                                src="../<?php echo htmlspecialchars($group['featured']['cover_image']); ?>"
                                alt="<?php echo htmlspecialchars($group['featured']['title']); ?>"
                                class="article-section-featured__image"
                            >
                        <?php else: ?>
                            <div class="article-section-featured__image article-section-featured__image--placeholder"></div>
                        <?php endif; ?>
                    </a>

                    <div class="article-section-featured__content">
                        <span class="tag-pill pill--<?php echo htmlspecialchars(normalizeCategoryClass($group['featured']['category_name'])); ?>">
                            <?php echo htmlspecialchars($group['featured']['category_name']); ?>
                        </span>

                        <h3 class="article-section-featured__headline">
                            <a href="../articles/detail.php?id=<?php echo (int) $group['featured']['id']; ?>">
                                <?php echo htmlspecialchars($group['featured']['title']); ?>
                            </a>
                        </h3>

                        <p class="article-section-featured__excerpt">
                            <?php echo htmlspecialchars(getExcerpt($group['featured']['content'], 270)); ?>
                        </p>

                        <p class="article-section-featured__author">
                            <?php echo htmlspecialchars($group['featured']['author_name']); ?>
                        </p>
                    </div>
                </article>
            <?php else: ?>
                <p class="article-section-card__empty">
                    Todavía no hay artículos publicados en esta sección.
                </p>
            <?php endif; ?>

            <?php if (!empty($group['secondary'])): ?>
                <div class="article-section-grid">
                    <?php foreach ($group['secondary'] as $article): ?>
                        <article class="article-section-grid__card">
                            <a href="../articles/detail.php?id=<?php echo (int) $article['id']; ?>" class="article-section-grid__image-link">
                                <?php if (!empty($article['cover_image'])): ?>
                                    <img
                                        src="../<?php echo htmlspecialchars($article['cover_image']); ?>"
                                        alt="<?php echo htmlspecialchars($article['title']); ?>"
                                        class="article-section-grid__image"
                                    >
                                <?php else: ?>
                                    <div class="article-section-grid__image article-section-grid__image--placeholder"></div>
                                <?php endif; ?>
                            </a>

                            <div class="article-section-grid__body">
                                <span class="tag-pill pill--<?php echo htmlspecialchars(normalizeCategoryClass($article['category_name'])); ?>">
                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                </span>

                                <h4 class="article-section-grid__headline">
                                    <a href="../articles/detail.php?id=<?php echo (int) $article['id']; ?>">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h4>

                                <p class="article-section-grid__author">
                                    <?php echo htmlspecialchars($article['author_name']); ?>
                                </p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </section>
    <?php endforeach; ?>

</main>

<script type="text/babel" src="../assets/js/listing-summary.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
