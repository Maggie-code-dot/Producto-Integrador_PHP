<?php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'administrador') {
    header('Location: index.php');
    exit();
}

$users = json_decode(file_get_contents('users.json'), true);

// Función para borrar un usuario (sin cambios aquí)
if (isset($_GET['delete_user'])) {
    $userToDelete = $_GET['delete_user'];
    $updatedUsers = array_filter($users, function ($user) use ($userToDelete) {
        return $user['username'] !== $userToDelete;
    });
    file_put_contents('users.json', json_encode(array_values($updatedUsers), JSON_PRETTY_PRINT));
    $userDirToDelete = 'uploads/' . $userToDelete;
    if (is_dir($userDirToDelete)) {
        array_map('unlink', glob("$userDirToDelete/*"));
        rmdir($userDirToDelete);
    }
    header('Location: admin.php?message=Usuario+borrado+exitosamente');
    exit();
}

// Función para modificar un usuario (sin cambios aquí)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_username']) && isset($_POST['original_username'])) {
    $originalUsername = $_POST['original_username'];
    $newUsername = trim($_POST['edit_username']);
    $updatedUsers = [];
    $usernameChanged = false;
    foreach ($users as &$user) {
        if ($user['username'] === $originalUsername) {
            $user['username'] = $newUsername;
            $usernameChanged = true;
            $oldDir = 'uploads/' . $originalUsername;
            $newDir = 'uploads/' . $newUsername;
            if (is_dir($oldDir) && !is_dir($newDir)) {
                rename($oldDir, $newDir);
            }
        }
        $updatedUsers[] = $user;
    }
    if ($usernameChanged) {
        file_put_contents('users.json', json_encode($updatedUsers, JSON_PRETTY_PRINT));
        header('Location: admin.php?message=Usuario+modificado+exitosamente');
    } else {
        header('Location: admin.php?error=No+se+encontró+el+usuario+para+editar');
    }
    exit();
}

// Función para borrar una imagen de un usuario (sin cambios aquí)
if (isset($_GET['delete_image']) && isset($_GET['user'])) {
    $imageToDelete = basename($_GET['delete_image']);
    $userDir = 'uploads/' . $_GET['user'];
    $filePath = $userDir . '/' . $imageToDelete;

    if (file_exists($filePath)) {
        unlink($filePath);
        header('Location: admin.php?message=Imagen+' . urlencode($imageToDelete) . '+de+' . urlencode($_GET['user']) . '+borrada+exitosamente');
    } else {
        header('Location: admin.php?error=La+imagen+' . urlencode($imageToDelete) . '+no+existe+para+el+usuario+' . urlencode($_GET['user']));
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .admin-container { /* Estilos existentes */ }
        .admin-container h2 { /* Estilos existentes */ }
        .user-list { /* Estilos existentes */ }
        .user-item { /* Estilos existentes */ }
        .user-actions { /* Estilos existentes */ }
        .user-actions a { /* Estilos existentes */ }
        .user-actions a:hover { /* Estilos existentes */ }
        .error-message-admin { /* Estilos existentes */ }
        .success-message-admin { /* Estilos existentes */ }
        .edit-form { /* Estilos existentes */ }
        .edit-form input[type="text"] { /* Estilos existentes */ }
        .edit-form button { /* Estilos existentes */ }
        .edit-form button:hover { /* Estilos existentes */ }
        .user-images {
            margin-top: 10px;
            padding-left: 20px;
            font-size: 0.9em;
        }
        .user-images ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .user-images li {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .user-images li img {
            width: 50px; /* Ajusta el tamaño de la miniatura según necesites */
            height: auto;
            margin-right: 10px;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .user-images li a {
            color: #dc3545; /* Rojo para indicar eliminación */
            text-decoration: none;
            margin-left: 10px;
        }
        .user-images li a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <h2>Panel de Administración</h2>
            <p><a href="dashboard.php">Volver al Dashboard</a> | <a href="logout.php">Cerrar sesión</a></p>

            <?php if (isset($_GET['message'])): ?>
                <p class="success-message-admin"><?php echo htmlspecialchars($_GET['message']); ?></p>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <p class="error-message-admin"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>

            <h3>Lista de Usuarios</h3>
            <ul class="user-list">
                <?php foreach ($users as $user): ?>
                    <li class="user-item">
                        <span><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</span>
                        <div class="user-actions">
                            <?php if ($user['role'] !== 'administrador'): ?>
                                <a href="admin.php?delete_user=<?php echo urlencode($user['username']); ?>" onclick="return confirm('¿Estás seguro de que quieres borrar este usuario y sus imágenes?')">🗑️ Borrar Usuario</a>
                                <a href="#" onclick="toggleEditForm('<?php echo htmlspecialchars($user['username']); ?>')">✏️ Editar Nombre</a>
                            <?php else: ?>
                                <span style="color: gray;">No se puede editar</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($user['role'] !== 'administrador'): ?>
                            <form method="post" class="edit-form" id="edit-form-<?php echo htmlspecialchars($user['username']); ?>">
                                <input type="hidden" name="original_username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                Nuevo nombre de usuario: <input type="text" name="edit_username" required>
                                <button type="submit">Guardar</button>
                            </form>
                            <script>
                                function toggleEditForm(username) {
                                    var form = document.getElementById('edit-form-' + username);
                                    form.style.display = form.style.display === 'none' ? 'block' : 'none';
                                }
                            </script>
                        <?php endif; ?>

                        <?php
                        $userDir = 'uploads/' . $user['username'];
                        if (is_dir($userDir)) {
                            $userImages = array_diff(scandir($userDir), ['.', '..']);
                            if (count($userImages) > 0):
                        ?>
                            <div class="user-images">
                                <strong>Imágenes:</strong>
                                <ul>
                                    <?php foreach ($userImages as $image): ?>
                                        <li>
                                            <img src="<?php echo $userDir . '/' . $image; ?>" alt="<?php echo htmlspecialchars($image); ?>">
                                            <span><?php echo htmlspecialchars($image); ?></span>
                                            <a href="admin.php?delete_image=<?php echo urlencode($image); ?>&user=<?php echo urlencode($user['username']); ?>" onclick="return confirm('¿Estás seguro de que quieres borrar esta imagen de <?php echo htmlspecialchars($user['username']); ?>?')">❌ Borrar</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php
                            endif;
                        }
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>
