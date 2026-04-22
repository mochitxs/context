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

        header('Location: index.php');
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT DISTINCT
        users.id,
        users.username,
        users.profile_image,
        users.bio
    FROM users
    LEFT JOIN posts
        ON posts.user_id = users.id
       AND posts.type = 'article'
       AND posts.status = 'published'
    WHERE users.role = 'author'
       OR posts.id IS NOT NULL
    ORDER BY users.username ASC
");
$stmt->execute();

$authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autores - CONTEXT</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/authors.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body class="authors-page">

<?php require_once '../includes/header.php'; ?>

<main class="authors-layout">
    <section class="authors-hero">
        <h1 class="authors-title">
            <span>autores;</span>
        </h1>
        <p class="authors-subtitle">
            voces que dan forma a CONTEXT.
        </p>
    </section>

    <section class="authors-card">
        <?php if ($followMessage !== ''): ?>
            <p class="authors-message"><?php echo htmlspecialchars($followMessage); ?></p>
        <?php endif; ?>

        <?php if (empty($authors)): ?>
            <p class="authors-empty">Todavía no hay autores disponibles.</p>
        <?php else: ?>
            <div class="authors-grid">
                <?php foreach ($authors as $author): ?>
                    <?php
                    $followersCount = $followsEnabled ? ctx_get_author_followers_count($pdo, (int) $author['id']) : 0;
                    $viewerCanFollow = $followsEnabled && isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] !== (int) $author['id'];
                    $viewerFollows = $viewerCanFollow
                        ? ctx_user_follows_author($pdo, (int) $_SESSION['user_id'], (int) $author['id'])
                        : false;
                    ?>
                    <article class="author-item">
                        <div class="author-item__avatar">
                            <?php if (!empty($author['profile_image'])): ?>
                                <img
                                    src="../<?php echo htmlspecialchars($author['profile_image']); ?>"
                                    alt="Foto de perfil de <?php echo htmlspecialchars($author['username']); ?>"
                                >
                            <?php else: ?>
                                <span>
                                    <?php echo strtoupper(mb_substr($author['username'], 0, 1)); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h2 class="author-item__name">
                            <?php echo htmlspecialchars($author['username']); ?>
                        </h2>

                        <p class="author-item__bio">
                            <?php echo !empty($author['bio'])
                                ? htmlspecialchars($author['bio'])
                                : 'Este autor todavía no ha añadido una descripción.'; ?>
                        </p>

                        <p class="author-item__followers">
                            <?php echo $followersCount; ?> seguidor<?php echo $followersCount === 1 ? '' : 'es'; ?>
                        </p>

                        <?php if ($viewerCanFollow): ?>
                            <form method="POST" action="" class="author-item__follow-form">
                                <input type="hidden" name="form_type" value="toggle_author_follow">
                                <input type="hidden" name="followed_user_id" value="<?php echo (int) $author['id']; ?>">
                                <button type="submit" class="author-item__follow-button <?php echo $viewerFollows ? 'is-active' : ''; ?>">
                                    <?php echo $viewerFollows ? 'Siguiendo' : 'Seguir'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>
