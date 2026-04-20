<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

/**
 * Comprobamos que exista un id válido en la URL.
 */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Post no encontrado.');
}

$postId = (int) $_GET['id'];
$commentMessage = '';
$commentMessageType = 'info';
$commentsEnabled = true;

/**
 * Intentamos asegurar la tabla de comentarios.
 * Si falla la creación o el acceso, no rompemos la vista del post.
 */
try {
    ctx_ensure_comments_table($pdo);
} catch (Throwable $exception) {
    $commentsEnabled = false;
    $commentMessage = 'Los comentarios no están disponibles temporalmente.';
    $commentMessageType = 'error';
}

/**
 * Procesamiento del formulario de comentarios.
 */
if (
    $commentsEnabled &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['form_type'] ?? '') === 'post_comment'
) {
    if (!isset($_SESSION['user_id'])) {
        $commentMessage = 'Necesitas iniciar sesión para comentar.';
        $commentMessageType = 'error';
    } else {
        $content = trim($_POST['comment_content'] ?? '');
        $parentCommentId = null;

        if (isset($_POST['parent_comment_id']) && $_POST['parent_comment_id'] !== '') {
            $parentCommentId = (int) $_POST['parent_comment_id'];
        }

        if ($content === '') {
            $commentMessage = 'Escribe un comentario antes de enviarlo.';
            $commentMessageType = 'error';
        } elseif (mb_strlen($content) > 1200) {
            $commentMessage = 'El comentario es demasiado largo.';
            $commentMessageType = 'error';
        } else {
            if ($parentCommentId !== null) {
                $parentQuery = "
                    SELECT id, parent_comment_id
                    FROM comments
                    WHERE id = ? AND post_id = ?
                    LIMIT 1
                ";

                $parentStmt = $pdo->prepare($parentQuery);
                $parentStmt->execute([$parentCommentId, $postId]);
                $parentComment = $parentStmt->fetch(PDO::FETCH_ASSOC);

                if (!$parentComment) {
                    $commentMessage = 'El comentario al que intentas responder ya no existe.';
                    $commentMessageType = 'error';
                } elseif ($parentComment['parent_comment_id'] !== null) {
                    $commentMessage = 'Solo se permiten respuestas de un nivel.';
                    $commentMessageType = 'error';
                }
            }

            if ($commentMessage === '') {
                $postOwnerStmt = $pdo->prepare("SELECT user_id, title FROM posts WHERE id = ? LIMIT 1");
                $postOwnerStmt->execute([$postId]);
                $postOwner = $postOwnerStmt->fetch(PDO::FETCH_ASSOC);

                $insertQuery = "
                    INSERT INTO comments (
                        post_id,
                        user_id,
                        parent_comment_id,
                        content
                    ) VALUES (?, ?, ?, ?)
                ";

                $insertStmt = $pdo->prepare($insertQuery);
                $insertStmt->execute([
                    $postId,
                    (int) $_SESSION['user_id'],
                    $parentCommentId,
                    $content
                ]);

                $newCommentId = (int) $pdo->lastInsertId();

                if ($postOwner && (int) $postOwner['user_id'] !== (int) $_SESSION['user_id']) {
                    ctx_create_notification(
                        $pdo,
                        (int) $postOwner['user_id'],
                        (int) $_SESSION['user_id'],
                        $postId,
                        $newCommentId,
                        'comment',
                        $_SESSION['username'] . ' ha comentado en tu post "' . $postOwner['title'] . '".'
                    );
                }

                header('Location: detail.php?id=' . $postId . '#comments');
                exit;
            }
        }
    }
}

/**
 * Cargamos el post con su autor y categoría.
 * Solo mostramos publicaciones publicadas y de tipo post.
 */
$sql = "
    SELECT 
        posts.id,
        posts.title,
        posts.content,
        posts.cover_image,
        posts.created_at,
        posts.type,
        categories.name AS category_name,
        users.username AS author_name
    FROM posts
    INNER JOIN categories ON posts.category_id = categories.id
    INNER JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
      AND posts.status = 'published'
      AND posts.type = 'post'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die('Post no encontrado.');
}

$formattedDate = date('d/m/Y', strtotime($post['created_at']));
$comments = [];
$commentSetupHint = '';

if ($commentsEnabled) {
    try {
        $comments = ctx_fetch_post_comments($pdo, $postId);
    } catch (Throwable $exception) {
        $comments = [];
        $commentsEnabled = false;
        $commentMessage = 'Los comentarios aún no están listos en la base de datos.';
        $commentMessageType = 'error';
        $commentSetupHint = $exception->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - CONTEXT</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/post-view.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>
<body class="post-view-page">

    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <main class="post-view-layout">
        <article class="post-view-card">

            <div class="post-view-card__top">
                <a href="../posts/view.php" class="post-view-card__back">
                    ← Volver a posts
                </a>

                <span class="tag-pill pill--<?php echo htmlspecialchars(ctx_normalize_category_class($post['category_name'])); ?>">
                    <?php echo htmlspecialchars($post['category_name']); ?>
                </span>
            </div>

            <header class="post-view-card__header">
                <h1 class="post-view-card__title">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>

                <p class="post-view-card__meta">
                    Por <?php echo htmlspecialchars($post['author_name']); ?> · <?php echo htmlspecialchars($formattedDate); ?>
                </p>
            </header>

            <?php if (!empty($post['cover_image'])): ?>
                <div class="post-view-card__image-wrapper">
                    <img
                        src="../<?php echo htmlspecialchars($post['cover_image']); ?>"
                        alt="<?php echo htmlspecialchars($post['title']); ?>"
                        class="post-view-card__image"
                    >
                </div>
            <?php endif; ?>

            <section class="post-view-card__content">
                <?php echo ctx_render_paragraphs($post['content']); ?>
            </section>

            <section id="comments" class="post-comments">
                <div class="post-comments__header">
                    <h2 class="post-comments__title">
                        <span>comentarios;</span>
                    </h2>
                    <p class="post-comments__count">
                        <?php echo count($comments); ?> comentario<?php echo count($comments) === 1 ? '' : 's'; ?>
                    </p>
                </div>

                <?php if ($commentMessage !== ''): ?>
                    <p class="post-comments__message post-comments__message--<?php echo htmlspecialchars($commentMessageType); ?>">
                        <?php echo htmlspecialchars($commentMessage); ?>
                    </p>
                <?php endif; ?>

                <?php if ($commentSetupHint !== ''): ?>
                    <p class="post-comments__message post-comments__message--hint">
                        Detalle técnico: <?php echo htmlspecialchars($commentSetupHint); ?>
                    </p>
                <?php endif; ?>

                <?php if ($commentsEnabled && isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="" class="post-comments__form">
                        <input type="hidden" name="form_type" value="post_comment">
                        <textarea
                            name="comment_content"
                            rows="4"
                            maxlength="1200"
                            placeholder="Escribe tu comentario..."
                            required
                        ></textarea>
                        <button type="submit" class="post-comments__submit">Publicar comentario</button>
                    </form>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <p class="post-comments__hint">
                        <a href="../auth/login.php">Inicia sesión</a> para comentar o responder.
                    </p>
                <?php endif; ?>

                <?php if (!empty($comments)): ?>
                    <div class="post-comments__list">
                        <?php foreach ($comments as $comment): ?>
                            <article class="post-comment">
                                <header class="post-comment__header">
                                    <div>
                                        <p class="post-comment__author">
                                            <?php echo htmlspecialchars($comment['username']); ?>
                                        </p>
                                        <p class="post-comment__date">
                                            <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($comment['created_at']))); ?>
                                        </p>
                                    </div>
                                </header>

                                <div class="post-comment__body">
                                    <?php echo ctx_render_paragraphs($comment['content']); ?>
                                </div>

                                <?php if ($commentsEnabled && isset($_SESSION['user_id'])): ?>
                                    <form method="POST" action="" class="post-comment__reply-form">
                                        <input type="hidden" name="form_type" value="post_comment">
                                        <input type="hidden" name="parent_comment_id" value="<?php echo (int) $comment['id']; ?>">
                                        <textarea
                                            name="comment_content"
                                            rows="2"
                                            maxlength="1200"
                                            placeholder="Responder a este comentario..."
                                            required
                                        ></textarea>
                                        <button type="submit" class="post-comments__reply-button">Responder</button>
                                    </form>
                                <?php endif; ?>

                                <?php if (!empty($comment['replies'])): ?>
                                    <div class="post-comment__replies">
                                        <?php foreach ($comment['replies'] as $reply): ?>
                                            <article class="post-comment post-comment--reply">
                                                <header class="post-comment__header">
                                                    <div>
                                                        <p class="post-comment__author">
                                                            <?php echo htmlspecialchars($reply['username']); ?>
                                                        </p>
                                                        <p class="post-comment__date">
                                                            <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($reply['created_at']))); ?>
                                                        </p>
                                                    </div>
                                                </header>

                                                <div class="post-comment__body">
                                                    <?php echo ctx_render_paragraphs($reply['content']); ?>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($commentsEnabled): ?>
                    <p class="post-comments__empty">
                        Todavía no hay comentarios. Sé la primera persona en participar.
                    </p>
                <?php endif; ?>
            </section>

        </article>
    </main>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
