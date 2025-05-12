<?php

session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
$userDir = 'uploads/' . $username;

// Recuperar el rol del usuario de la sesión
$userRole = $_SESSION['role'] ?? 'usuario';

if (!is_dir($userDir)) {
    mkdir($userDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']))
{
    $fileName = basename($_FILES['image']['name']);
    $targetFile = $userDir . '/' . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'],
$targetFile)) {
        echo "<div
class='dashboard-content'><p class='success-message'>✅
Imagen subida exitosamente.</p></div>";
    } else {
        echo "<div
class='dashboard-content'><p class='error-message'>❌
Error al subir la imagen.</p></div>";
    }
}

// Inicializar $images con un array vacío
$images = [];

// Intentar leer el directorio y asignar las imágenes
if (is_dir($userDir)) {
    $images = array_diff(scandir($userDir), ['.', '..']);


}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <h2>Bienvenido, <?php echo htmlspecialchars($username); ?> 👋</h2>
            <a href="logout.php" class="logout-link">Cerrar sesión</a>

            <?php if ($userRole === 'administrador'): ?>
                <p><a href="admin.php">Panel de Administración</a></p>
            <?php endif; ?>

            <div class="upload-section">
                <h3>Subir una nueva imagen</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="image" accept="image/*" required><br><br>
                    <button type="submit">Subir</button>
                </form>
            </div>

            <div class="images-section">
                <h3>Mis imágenes</h3>
                <?php if (count($images) > 0): ?>
                    <ul>
                        <?php foreach ($images as $image): ?>
                            <li>
                                <img src="<?php echo $userDir . '/' . $image; ?>" alt="Imagen">
                                <span><?php echo htmlspecialchars($image); ?></span><br>
                                <a href="download.php?file=<?php echo urlencode($image); ?>">📥 Descargar</a> |
                                <a href="delete.php?file=<?php echo urlencode($image); ?>">🗑️ Borrar</a>
                            </li><br>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-images">No has subido imágenes todavía.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>