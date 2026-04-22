<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

$followsEnabled = true;

try {
    ctx_ensure_user_follows_table($pdo);
} catch (Throwable $exception) {
    $followsEnabled = false;
}

/**
 * Comprobamos que exista un id válido en la URL.
 */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Artículo no encontrado.');
}

$articleId = (int) $_GET['id'];
$followMessage = '';

if (
    $followsEnabled &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['form_type'] ?? '') === 'toggle_author_follow'
) {
    if (!isset($_SESSION['user_id'])) {
        $followMessage = 'Necesitas iniciar sesión para seguir a autores.';
    } else {
        $followerUserId = (int) $_SESSION['user_id'];
        $followedUserId = (int) ($_POST['followed_user_id'] ?? 0);

        if ($followedUserId > 0 && $followedUserId !== $followerUserId) {
            if (ctx_user_follows_author($pdo, $followerUserId, $followedUserId)) {
                $deleteFollowStmt = $pdo->prepare("
                    DELETE FROM follows
                    WHERE follower_user_id = ? AND followed_user_id = ?
                ");
                $deleteFollowStmt->execute([$followerUserId, $followedUserId]);
            } else {
                $insertFollowStmt = $pdo->prepare("
                    INSERT INTO follows (follower_user_id, followed_user_id)
                    VALUES (?, ?)
                ");
                $insertFollowStmt->execute([$followerUserId, $followedUserId]);

                ctx_create_notification(
                    $pdo,
                    $followedUserId,
                    $followerUserId,
                    null,
                    null,
                    'follow',
                    $_SESSION['username'] . ' ha empezado a seguirte.'
                );
            }
        }

        header('Location: detail.php?id=' . $articleId . '#article-author');
        exit;
    }
}

/**
 * Cargamos el artículo con su autor y categoría.
 * Solo mostramos publicaciones publicadas y de tipo article.
 */
$sql = "
    SELECT 
        posts.id,
        posts.title,
        posts.content,
        posts.cover_image,
        posts.created_at,
        posts.type,
        posts.user_id,
        categories.name AS category_name,
        users.username AS author_name,
        users.profile_image
    FROM posts
    INNER JOIN categories ON posts.category_id = categories.id
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
      AND posts.status = 'published'
      AND posts.type = 'article'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$articleId]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    die('Artículo no encontrado.');
}

/**
 * Formatea la fecha para mostrarla mejor.
 */
$formattedDate = date('d/m/Y', strtotime($article['created_at']));
$authorFollowersCount = $followsEnabled ? ctx_get_author_followers_count($pdo, (int) $article['user_id']) : 0;
$viewerCanFollowAuthor = $followsEnabled && isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] !== (int) $article['user_id'];
$viewerFollowsAuthor = $viewerCanFollowAuthor
    ? ctx_user_follows_author($pdo, (int) $_SESSION['user_id'], (int) $article['user_id'])
    : false;

/**
 * Convierte saltos de línea en párrafos sencillos.
 */
function renderContent(string $text): string
{
    $paragraphs = preg_split("/\R{2,}/", trim($text));
    $html = '';

    foreach ($paragraphs as $paragraph) {
        $html .= '<p>' . nl2br(htmlspecialchars(trim($paragraph))) . '</p>';
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - CONTEXT</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/article-view.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body class="article-view-page">

    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="article-view-layout">
        <article class="article-view-card">

            <div class="article-view-card__top">
                <a href="../articles/view.php" class="article-view-card__back">
                    ← Volver
                </a>

                <span class="tag-pill pill--<?php echo htmlspecialchars(ctx_normalize_category_class($article['category_name'])); ?>">
                    <?php echo htmlspecialchars($article['category_name']); ?>
                </span>
            </div>

            <header class="article-view-card__header">
                <h1 class="article-view-card__title">
                    <?php echo htmlspecialchars($article['title']); ?>
                </h1>

                <div id="article-author" class="article-view-card__meta">
                    <div class="content-author">
                        <div class="content-author__avatar">
                            <?php if (!empty($article['profile_image'])): ?>
                                <img
                                    src="../<?php echo htmlspecialchars($article['profile_image']); ?>"
                                    alt="Foto de perfil de <?php echo htmlspecialchars($article['author_name']); ?>"
                                >
                            <?php else: ?>
                                <span>
                                    <?php echo strtoupper(mb_substr($article['author_name'], 0, 1)); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <span class="content-author__name">
                            <?php echo htmlspecialchars($article['author_name']); ?>
                        </span>
                    </div>

                    <span class="article-view-card__followers">
                        <?php echo $authorFollowersCount; ?> seguidor<?php echo $authorFollowersCount === 1 ? '' : 'es'; ?>
                    </span>

                    <?php if ($viewerCanFollowAuthor): ?>
                        <form method="POST" action="" class="article-view-card__follow-form">
                            <input type="hidden" name="form_type" value="toggle_author_follow">
                            <input type="hidden" name="followed_user_id" value="<?php echo (int) $article['user_id']; ?>">
                            <button
                                type="submit"
                                class="article-view-card__follow-button <?php echo $viewerFollowsAuthor ? 'is-active' : ''; ?>"
                            >
                                <?php echo $viewerFollowsAuthor ? 'Siguiendo' : 'Seguir'; ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <span class="article-view-card__separator">·</span>

                    <span class="article-view-card__date">
                        <?php echo htmlspecialchars($formattedDate); ?>
                    </span>
                </div>

                <?php if ($followMessage !== ''): ?>
                    <p class="article-view-card__follow-message">
                        <?php echo htmlspecialchars($followMessage); ?>
                    </p>
                <?php endif; ?>
            </header>

            <?php if (!empty($article['cover_image'])): ?>
                <div class="article-view-card__image-wrapper">
                    <img
                        src="../<?php echo htmlspecialchars($article['cover_image']); ?>"
                        alt="<?php echo htmlspecialchars($article['title']); ?>"
                        class="article-view-card__image"
                    >
                </div>
            <?php endif; ?>

            <section class="article-view-card__content">
                <?php echo $article["content"]; ?>
            </section>

        </article>
    </main>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
