<?php
/**
 * Zona de usuario / ajustes.
 *
 * Esta vista reúne perfil, publicaciones propias, comentarios y
 * notificaciones en un único espacio sencillo de defender en la entrega.
 */
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

ctx_ensure_user_profile_column($pdo);
ctx_ensure_comments_table($pdo);
ctx_ensure_notifications_table($pdo);

$userId = (int) $_SESSION['user_id'];
$activeView = $_GET['view'] ?? 'posts';
$allowedViews = ['posts', 'articles', 'profile', 'comments', 'notifications'];

if (!in_array($activeView, $allowedViews, true)) {
    $activeView = 'posts';
}

$message = '';
$messageType = 'info';

/**
 * Carga datos básicos del usuario autenticado.
 */
$userStmt = $pdo->prepare("
    SELECT id, username, email, role, profile_image, bio
    FROM users
    WHERE id = ?
    LIMIT 1
");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit;
}

/**
 * Procesamiento de acciones del panel.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_profile_image') {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['profile_image']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $allowedExtensions, true)) {
                $message = 'La foto de perfil debe ser JPG, PNG o WEBP.';
                $messageType = 'error';
            } else {
                $uploadDir = __DIR__ . '/../assets/uploads/profiles/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = uniqid('profile_', true) . '.' . $extension;
                $destination = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $destination)) {
                    $relativePath = 'assets/uploads/profiles/' . $fileName;

                    $updateStmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $updateStmt->execute([$relativePath, $userId]);

                    $user['profile_image'] = $relativePath;
                    $message = 'Foto de perfil actualizada.';
                    $messageType = 'success';
                } else {
                    $message = 'No se pudo subir la foto de perfil.';
                    $messageType = 'error';
                }
            }
        } else {
            $message = 'Selecciona una imagen antes de guardar.';
            $messageType = 'error';
        }

        $activeView = 'profile';
    }

    if ($action === 'update_profile_info') {
        $bio = trim($_POST['bio'] ?? '');

        $updateStmt = $pdo->prepare("
            UPDATE users
            SET bio = ?
            WHERE id = ?
        ");
        $updateStmt->execute([$bio, $userId]);

        $user['bio'] = $bio;
        $message = 'Descripción actualizada.';
        $messageType = 'success';
        $activeView = 'profile';
    }

    if ($action === 'delete_post') {
        $targetId = (int) ($_POST['post_id'] ?? 0);

        if ($targetId > 0) {
            $deleteStmt = $pdo->prepare("
                DELETE FROM posts
                WHERE id = ?
                  AND user_id = ?
                  AND type = 'post'
            ");
            $deleteStmt->execute([$targetId, $userId]);
            $message = 'Post eliminado.';
            $messageType = 'success';
        }
        $activeView = 'posts';
    }

    if ($action === 'delete_article') {
        $targetId = (int) ($_POST['post_id'] ?? 0);

        if ($targetId > 0) {
            $deleteStmt = $pdo->prepare("
                DELETE FROM posts
                WHERE id = ?
                  AND user_id = ?
                  AND type = 'article'
            ");
            $deleteStmt->execute([$targetId, $userId]);
            $message = 'Artículo eliminado.';
            $messageType = 'success';
        }
        $activeView = 'articles';
    }
}

if ($activeView === 'notifications') {
    $markReadStmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $markReadStmt->execute([$userId]);
}

/**
 * Listados del panel.
 */
$postsStmt = $pdo->prepare("
    SELECT id, title, content, created_at
    FROM posts
    WHERE user_id = ? AND type = 'post'
    ORDER BY created_at DESC
");
$postsStmt->execute([$userId]);
$userPosts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);

$articlesStmt = $pdo->prepare("
    SELECT id, title, content, created_at
    FROM posts
    WHERE user_id = ? AND type = 'article'
    ORDER BY created_at DESC
");
$articlesStmt->execute([$userId]);
$userArticles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);

$commentsStmt = $pdo->prepare("
    SELECT comments.id, comments.content, comments.created_at, posts.id AS post_id, posts.title AS post_title
    FROM comments
    INNER JOIN posts ON comments.post_id = posts.id
    WHERE comments.user_id = ?
    ORDER BY comments.created_at DESC
");
$commentsStmt->execute([$userId]);
$userComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

$notificationsStmt = $pdo->prepare("
    SELECT id, message, created_at, post_id, is_read
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$notificationsStmt->execute([$userId]);
$notifications = $notificationsStmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Renderiza tarjetas de contenido propio.
 *
 * @param array<int, array<string, mixed>> $items
 * @param string $type
 * @return void
 */
function renderOwnContentCards(array $items, string $type): void
{
    foreach ($items as $item) {
        $detailUrl = $type === 'article'
            ? '../articles/detail.php?id=' . (int) $item['id']
            : '../posts/detail.php?id=' . (int) $item['id'];

        $deleteAction = $type === 'article' ? 'delete_article' : 'delete_post';
        ?>
        <article class="settings-content-card">
            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
            <p><?php echo htmlspecialchars(ctx_excerpt($item['content'], 220)); ?></p>
            <div class="settings-content-card__actions">
                <form method="POST" action="" onsubmit="return confirm('¿Seguro que quieres borrar este contenido?');">
                    <input type="hidden" name="action" value="<?php echo htmlspecialchars($deleteAction); ?>">
                    <input type="hidden" name="post_id" value="<?php echo (int) $item['id']; ?>">
                    <button type="submit" class="settings-button settings-button--danger">BORRAR</button>
                </form>
                <a href="<?php echo htmlspecialchars($detailUrl); ?>" class="settings-button settings-button--ghost">VER</a>
            </div>
        </article>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajustes - CONTEXT</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/section.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body class="section-page">

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<main class="settings-layout">
    <h1 class="settings-title"><span>ajustes;</span></h1>

    <?php if ($message !== ''): ?>
        <p class="settings-message settings-message--<?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <section class="settings-panel">
        <aside class="settings-sidebar">
            <h2>ver</h2>
            <nav class="settings-nav">
                <a class="<?php echo $activeView === 'posts' ? 'is-active' : ''; ?>" href="?view=posts">posts</a>
                <?php if (($user['role'] ?? '') === 'author'): ?>
                    <a class="<?php echo $activeView === 'articles' ? 'is-active' : ''; ?>" href="?view=articles">artículos</a>
                <?php endif; ?>
                <a class="<?php echo $activeView === 'notifications' ? 'is-active' : ''; ?>" href="?view=notifications">notificaciones</a>
            </nav>

            <h2>usuario</h2>
            <nav class="settings-nav">
                <a class="<?php echo $activeView === 'profile' ? 'is-active' : ''; ?>" href="?view=profile">perfil</a>
                <a class="<?php echo $activeView === 'comments' ? 'is-active' : ''; ?>" href="?view=comments">mis comentarios</a>
            </nav>
        </aside>

        <section class="settings-content">
            <?php if ($activeView === 'posts'): ?>
                <h2 class="settings-section-title"><span>tus posts;</span></h2>
                <?php if ($userPosts): ?>
                    <div class="settings-content-list">
                        <?php renderOwnContentCards($userPosts, 'post'); ?>
                    </div>
                <?php else: ?>
                    <p class="settings-empty">Aún no has publicado posts.</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($activeView === 'articles'): ?>
                <h2 class="settings-section-title"><span>tus artículos;</span></h2>
                <?php if ($userArticles): ?>
                    <div class="settings-content-list">
                        <?php renderOwnContentCards($userArticles, 'article'); ?>
                    </div>
                <?php else: ?>
                    <p class="settings-empty">Aún no has publicado artículos.</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($activeView === 'profile'): ?>
                <h2 class="settings-section-title"><span>tu perfil;</span></h2>
                <div class="settings-profile-card">
                    <div class="settings-profile-card__avatar">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <span><?php echo strtoupper(mb_substr($user['username'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="settings-profile-card__info">
                        <p><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <p>Rol: <?php echo htmlspecialchars($user['role']); ?></p>
                    </div>

                    <form method="POST" action="" enctype="multipart/form-data" class="settings-profile-card__form">
                        <input type="hidden" name="action" value="upload_profile_image">
                        <input type="file" name="profile_image" accept="image/*" required>
                        <button type="submit" class="settings-button">Guardar foto</button>
                    </form>
                </div>
                <form method="POST" action="" class="settings-profile-card__bio-form">
                    <input type="hidden" name="action" value="update_profile_info">

                    <label for="bio" class="settings-profile-card__label">Descripción</label>
                    <textarea
                        name="bio"
                        id="bio"
                        rows="5"
                        maxlength="280"
                        placeholder="Cuéntanos algo sobre ti, tus intereses o tu enfoque editorial..."
                    ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

                    <button type="submit" class="settings-button">Guardar descripción</button>
                </form>
            <?php endif; ?>

            <?php if ($activeView === 'comments'): ?>
                <h2 class="settings-section-title"><span>mis comentarios;</span></h2>
                <?php if ($userComments): ?>
                    <div class="settings-comments-list">
                        <?php foreach ($userComments as $comment): ?>
                            <article class="settings-comment-card">
                                <p class="settings-comment-card__post">
                                    En <a href="../posts/detail.php?id=<?php echo (int) $comment['post_id']; ?>#comments"><?php echo htmlspecialchars($comment['post_title']); ?></a>
                                </p>
                                <div class="settings-comment-card__body">
                                    <?php echo ctx_render_paragraphs($comment['content']); ?>
                                </div>
                                <p class="settings-comment-card__date"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($comment['created_at']))); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="settings-empty">Todavía no has escrito comentarios.</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($activeView === 'notifications'): ?>
                <h2 class="settings-section-title"><span>notificaciones;</span></h2>
                <?php if ($notifications): ?>
                    <div class="settings-notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                            <article class="settings-notification-card">
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <?php if (!empty($notification['post_id'])): ?>
                                    <a href="../posts/detail.php?id=<?php echo (int) $notification['post_id']; ?>#comments" class="settings-button settings-button--ghost">Ir al post</a>
                                <?php endif; ?>
                                <p class="settings-comment-card__date"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($notification['created_at']))); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="settings-empty">No tienes notificaciones por ahora.</p>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
