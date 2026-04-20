<?php
/**
 * Funciones compartidas de CONTEXT.
 *
 * Se concentran aquí utilidades de rutas, formato editorial y comentarios
 * para que las vistas sean más fáciles de mantener y de explicar.
 */

/**
 * Devuelve la base pública del proyecto.
 *
 * @return string Ruta base del proyecto en el servidor web.
 */
function ctx_base_url(): string
{
    return '/context';
}

/**
 * Construye una URL pública a partir de una ruta relativa del proyecto.
 *
 * @param string $path Ruta relativa dentro del proyecto.
 * @return string URL absoluta desde la raíz pública del proyecto.
 */
function ctx_url(string $path = ''): string
{
    $normalizedPath = ltrim($path, '/');

    if ($normalizedPath === '') {
        return ctx_base_url();
    }

    return ctx_base_url() . '/' . $normalizedPath;
}

/**
 * Convierte el nombre de una categoría en una clase CSS segura.
 *
 * @param string $name Nombre original de la categoría.
 * @return string Nombre normalizado para usarlo como sufijo CSS.
 */
function ctx_normalize_category_class(string $name): string
{
    $map = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'ñ' => 'n', 'Ñ' => 'n'
    ];

    $name = strtr($name, $map);
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]+/', '-', $name);

    return trim((string) $name, '-');
}

/**
 * Genera un extracto breve de un texto largo.
 *
 * @param string $text Texto original.
 * @param int $length Longitud máxima del extracto.
 * @return string Texto resumido.
 */
function ctx_excerpt(string $text, int $length = 250): string
{
    $text = trim(strip_tags($text));

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . '...';
}

/**
 * Convierte bloques de texto en párrafos HTML seguros.
 *
 * @param string $text Texto original.
 * @return string HTML generado con párrafos y saltos de línea.
 */
function ctx_render_paragraphs(string $text): string
{
    $paragraphs = preg_split("/\R{2,}/", trim($text));
    $html = '';

    foreach ($paragraphs as $paragraph) {
        $cleanParagraph = trim((string) $paragraph);

        if ($cleanParagraph === '') {
            continue;
        }

        $html .= '<p>' . nl2br(htmlspecialchars($cleanParagraph)) . '</p>';
    }

    return $html;
}

/**
 * Asegura la tabla de comentarios de posts.
 *
 * El sistema permite comentarios principales y respuestas de un solo nivel
 * usando el campo parent_comment_id.
 *
 * @param PDO $pdo Conexión activa.
 * @return void
 */
function ctx_ensure_comments_table(PDO $pdo): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            parent_comment_id INT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_comments_post_id (post_id),
            INDEX idx_comments_parent_id (parent_comment_id),
            INDEX idx_comments_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    $pdo->exec($sql);

    /**
     * Si la tabla ya existía de antes, nos aseguramos de que tenga la
     * estructura necesaria para respuestas de un solo nivel.
     */
    $existingColumns = [];
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM comments");

    foreach ($columnsStmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $existingColumns[] = $column['Field'];
    }

    if (!in_array('parent_comment_id', $existingColumns, true)) {
        $pdo->exec("ALTER TABLE comments ADD COLUMN parent_comment_id INT NULL AFTER user_id");
    }

    if (!in_array('created_at', $existingColumns, true)) {
        $pdo->exec("ALTER TABLE comments ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER content");
    }

    $indexesStmt = $pdo->query("SHOW INDEX FROM comments");
    $existingIndexes = [];

    foreach ($indexesStmt->fetchAll(PDO::FETCH_ASSOC) as $index) {
        $existingIndexes[] = $index['Key_name'];
    }

    if (!in_array('idx_comments_post_id', $existingIndexes, true)) {
        $pdo->exec("ALTER TABLE comments ADD INDEX idx_comments_post_id (post_id)");
    }

    if (!in_array('idx_comments_parent_id', $existingIndexes, true)) {
        $pdo->exec("ALTER TABLE comments ADD INDEX idx_comments_parent_id (parent_comment_id)");
    }

    if (!in_array('idx_comments_user_id', $existingIndexes, true)) {
        $pdo->exec("ALTER TABLE comments ADD INDEX idx_comments_user_id (user_id)");
    }
}

/**
 * Recupera comentarios de un post y los agrupa con respuestas de un nivel.
 *
 * @param PDO $pdo Conexión activa.
 * @param int $postId ID del post.
 * @return array<int, array<string, mixed>> Lista de comentarios con replies.
 */
function ctx_fetch_post_comments(PDO $pdo, int $postId): array
{
    $sql = "
        SELECT
            comments.id,
            comments.post_id,
            comments.user_id,
            comments.parent_comment_id,
            comments.content,
            comments.created_at,
            users.username,
            users.profile_image
        FROM comments
        INNER JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = ?
        ORDER BY comments.created_at ASC, comments.id ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$postId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $topLevel = [];
    $repliesByParent = [];

    foreach ($rows as $row) {
        $row['replies'] = [];

        if ($row['parent_comment_id'] === null) {
            $topLevel[(int) $row['id']] = $row;
            continue;
        }

        $repliesByParent[(int) $row['parent_comment_id']][] = $row;
    }

    foreach ($repliesByParent as $parentId => $replies) {
        if (isset($topLevel[$parentId])) {
            $topLevel[$parentId]['replies'] = $replies;
        }
    }

    return array_values($topLevel);
}

/**
 * Asegura la columna de foto de perfil en usuarios.
 *
 * @param PDO $pdo Conexión activa.
 * @return void
 */
function ctx_ensure_user_profile_column(PDO $pdo): void
{
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = array_column($columnsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    if (!in_array('profile_image', $columns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER role");
    }
}

/**
 * Asegura la tabla de notificaciones.
 *
 * @param PDO $pdo Conexión activa.
 * @return void
 */
function ctx_ensure_notifications_table(PDO $pdo): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            actor_user_id INT NULL,
            post_id INT NULL,
            comment_id INT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'comment',
            message VARCHAR(255) NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_user_id (user_id),
            INDEX idx_notifications_is_read (is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ";

    $pdo->exec($sql);
}

/**
 * Devuelve el número de notificaciones no leídas de un usuario.
 *
 * @param PDO $pdo Conexión activa.
 * @param int $userId Usuario destino.
 * @return int Número de notificaciones sin leer.
 */
function ctx_get_unread_notifications_count(PDO $pdo, int $userId): int
{
    ctx_ensure_notifications_table($pdo);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM notifications
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$userId]);

    return (int) $stmt->fetchColumn();
}

/**
 * Genera una notificación simple.
 *
 * @param PDO $pdo Conexión activa.
 * @param int $userId Destinatario.
 * @param int|null $actorUserId Usuario que provoca la acción.
 * @param int|null $postId Post relacionado.
 * @param int|null $commentId Comentario relacionado.
 * @param string $type Tipo de notificación.
 * @param string $message Texto visible de la notificación.
 * @return void
 */
function ctx_create_notification(
    PDO $pdo,
    int $userId,
    ?int $actorUserId,
    ?int $postId,
    ?int $commentId,
    string $type,
    string $message
): void {
    ctx_ensure_notifications_table($pdo);

    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            user_id,
            actor_user_id,
            post_id,
            comment_id,
            type,
            message
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $actorUserId,
        $postId,
        $commentId,
        $type,
        $message
    ]);
}
