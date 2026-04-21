<?php
session_start();
require_once '../config/db.php';

$stmt = $pdo->prepare("
    SELECT id, username, profile_image, bio
    FROM users
    WHERE role = 'author'
    ORDER BY username ASC
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
        <?php if (empty($authors)): ?>
            <p class="authors-empty">Todavía no hay autores disponibles.</p>
        <?php else: ?>
            <div class="authors-grid">
                <?php foreach ($authors as $author): ?>
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
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>