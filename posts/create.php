<?php
/**
 * Archivo: create.php
 *
 * Permite crear una nueva publicación en CONTEXT.
 *
 * Funcionalidades:
 * - Restringe el acceso a usuarios con sesión iniciada
 * - Carga categorías reales desde la base de datos
 * - Muestra el tipo de publicación como botones
 * - Permite subir una imagen de portada desde archivo
 * - Solo los autores pueden crear artículos
 * - Guarda la publicación en la tabla posts
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/db.php';

/**
 * Solo usuarios autenticados pueden acceder
 */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";
$userId = $_SESSION["user_id"];
$userRole = $_SESSION["role"];

/**
 * Tipos permitidos según rol
 * - user: post
 * - author: post y article
 */
$allowedTypes = ["post"];

if ($userRole === "author") {
    $allowedTypes[] = "article";
}

/**
 * Obtenemos categorías reales desde la BD
 */
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Procesamiento del formulario
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = trim($_POST["type"] ?? "");
    $title = trim($_POST["title"] ?? "");
    $categoryId = trim($_POST["category_id"] ?? "");
    $content = trim($_POST["content"] ?? "");

    $coverImagePath = null;

    /**
     * Validaciones básicas
     */
    if (empty($type) || empty($title) || empty($categoryId) || empty($content)) {
        $message = "Completa todos los campos obligatorios.";
    } elseif (!in_array($type, $allowedTypes, true)) {
        $message = "No tienes permiso para crear este tipo de contenido.";
    } else {
        /**
         * Procesamiento de imagen subida por el usuario
         */
        if (isset($_FILES["cover_image"]) && $_FILES["cover_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES["cover_image"]["error"] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES["cover_image"]["tmp_name"];
                $originalName = basename($_FILES["cover_image"]["name"]);
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $allowedExtensions = ["jpg", "jpeg", "png", "webp"];

                if (!in_array($extension, $allowedExtensions, true)) {
                    $message = "La imagen debe ser JPG, PNG o WEBP.";
                } else {
                    $newFileName = uniqid("cover_", true) . "." . $extension;
                    $uploadDir =__DIR__. "/../assets/uploads/";
                    $destination = $uploadDir . $newFileName;

                    /**
                     * Si la carpeta no existe, la creamos
                     */
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    /**
                     * Movemos el archivo al destino final
                     */
                    if (move_uploaded_file($tmpName, $destination)) {
                        $coverImagePath = "assets/uploads/" . $newFileName;
                    } else {
                        $message = "No se pudo subir la imagen.";
                    }
                }
            } else {
                $message = "Hubo un error al subir la imagen.";
            }
        }

        /**
         * Si no hay errores, insertamos la publicación
         */
        if (empty($message)) {
            $stmt = $pdo->prepare("
                INSERT INTO posts (
                    user_id,
                    category_id,
                    title,
                    content,
                    type,
                    cover_image,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, 'published')
            ");

            $stmt->execute([
                $userId,
                $categoryId,
                $title,
                $content,
                $type,
                $coverImagePath
            ]);

            $newPostId = $pdo->lastInsertId();

            if ($type === "article") {
                header("Location: ../articles/detail.php?id=" . $newPostId);
            } else {
                header("Location: ../posts/detail.php?id=" . $newPostId);
            }
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear publicación - CONTEXT</title>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/create.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
</head>

<body class="create-page">

<?php require_once '../includes/header.php'; ?>

    <main class="create-layout">
        <section class="create-hero">
            <h1 class="create-title">
                <span>crea;</span>
            </h1>

            <p class="create-subtitle">
                comparte una idea o una pieza editorial en contexto.
            </p>
        </section>

        <section class="create-card">
            <?php if (!empty($message)): ?>
                <p class="create-message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form method="POST" action="" class="create-form" enctype="multipart/form-data">

                <div class="create-form__row">
                    <div class="create-form__group">
                        <label>Tipo de publicación</label>

                        <div class="type-toggle">
                            <label class="type-toggle__option">
                                <input type="radio" name="type" value="post" required>
                                <span>Post</span>
                            </label>

                            <?php if ($userRole === "author"): ?>
                                <label class="type-toggle__option">
                                    <input type="radio" name="type" value="article">
                                    <span>Artículo</span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="create-form__group">
                        <label for="category_id">Categoría</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">Selecciona una categoría</option>

                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category["id"]); ?>">
                                    <?php echo htmlspecialchars($category["name"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="create-form__group">
                    <label for="title">Título</label>
                    <input type="text" name="title" id="title" required>
                </div>

                <div class="create-form__group">
                    <label for="content">Contenido</label>
                    <textarea name="content" id="content" rows="12" required></textarea>
                </div>

                <div class="create-form__optional">
                    <div class="create-form__group">
                        <label for="cover_image">Imagen de portada (opcional)</label>

                        <div class="file-upload">
                            <input 
                                type="file" 
                                name="cover_image" 
                                id="cover_image"
                                class="file-upload__input"
                                accept="image/*"
                            >

                            <label for="cover_image" class="file-upload__button">
                                Subir imagen
                            </label>

                            <span class="file-upload__text" id="file-upload-text">
                                Ningún archivo seleccionado
                            </span>
                        </div>
                    </div>
                </div>

                <div class="create-form__actions">
                    <a href="../index.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="create-submit">Publicar</button>
                </div>
            </form>
        </section>
    </main>
    <script>
    const fileInput = document.getElementById("cover_image");
    const fileText = document.getElementById("file-upload-text");

    fileInput.addEventListener("change", function () {
        if (this.files.length > 0) {
            fileText.textContent = this.files[0].name;
        } else {
            fileText.textContent = "Ningún archivo seleccionado";
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>
