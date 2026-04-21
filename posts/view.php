<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/**
 * Cargamos todos los posts publicados.
 */
$sql = "
    SELECT 
        posts.id,
        posts.title,
        posts.cover_image,
        posts.created_at,
        categories.name AS category_name,
        users.username AS author_name,
        users.profile_image
    FROM posts
    INNER JOIN categories ON posts.category_id = categories.id
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.status = 'published'
      AND posts.type = 'post'
    ORDER BY posts.created_at DESC
";

$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$postsCount = count($posts);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts - CONTEXT</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/post-view.css?v=<?php echo time(); ?>">    
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <?php require_once '../includes/frontend-deps.php'; ?>
</head>
<body class="posts-view-page">

<?php require_once '../includes/header.php'; ?>

<main class="posts-view-layout">
    <div
        data-react-listing-summary
        data-total="<?php echo (int) $postsCount; ?>"
        data-label="posts"
        data-description=""
    ></div>

    <section class="posts-view-grid">
        <?php foreach ($posts as $post): ?>
            <?php $placeholderColor = ctx_pick_editorial_color_variable((int) $post['id']); ?>
            <article class="post-card">

                <a href="detail.php?id=<?php echo $post['id']; ?>" class="post-card__image-link">
                    <?php if (!empty($post['cover_image'])): ?>
                        <img
                            src="../<?php echo htmlspecialchars($post['cover_image']); ?>"
                            alt="<?php echo htmlspecialchars($post['title']); ?>"
                            class="post-card__image"
                        >
                    <?php else: ?>
                        <div
                            class="post-card__image post-card__image--placeholder"
                            style="--post-placeholder-color: var(<?php echo htmlspecialchars($placeholderColor); ?>);"
                            aria-hidden="true"
                        ></div>
                    <?php endif; ?>
                </a>

                <div class="post-card__body">
                    <span class="tag-pill pill--<?php echo htmlspecialchars(ctx_normalize_category_class($post['category_name'])); ?>">
                        <?php echo htmlspecialchars($post['category_name']); ?>
                    </span>

                    <h2 class="post-card__title">
                        <a href="detail.php?id=<?php echo $post['id']; ?>">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </h2>

                    <div class="content-author">
                        <div class="content-author__avatar">
                            <?php if (!empty($post['profile_image'])): ?>
                                <img
                                    src="../<?php echo htmlspecialchars($post['profile_image']); ?>"
                                    alt="Foto de perfil de <?php echo htmlspecialchars($post['author_name']); ?>"
                                >
                            <?php else: ?>
                                <span>
                                    <?php echo strtoupper(mb_substr($post['author_name'], 0, 1)); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <span class="content-author__name">
                            <?php echo htmlspecialchars($post['author_name']); ?>
                        </span>
                    </div>
                </div>

            </article>
        <?php endforeach; ?>
    </section>
</main>

<script type="text/babel" src="../assets/js/listing-summary.js"></script>
<?php require_once '../includes/footer.php'; ?>

</body>
</html>
