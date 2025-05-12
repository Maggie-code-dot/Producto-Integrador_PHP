<?php

session_start();

// Si ya está logueado, lo mandamos al dashboard
if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si la clave 'username' existe en $_POST
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = password_hash(isset($_POST['password']) ? trim($_POST['password']) : '', PASSWORD_DEFAULT);

    // Cargar usuarios actuales
    $users = json_decode(file_get_contents('users.json'), true);

    // Verificar si el usuario ya existe
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            echo "❌ El usuario ya existe. Elige otro nombre.";
            exit();
        }
    }

    // Agregar nuevo usuario
    $users[] = [
        'username' => $username,
        'password' => $password,
        'role' => 'usuario' // Nuevo campo de rol
    ];

    // Guardar usuarios de nuevo
    file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));

    // Crear carpeta para imágenes del usuario
    if (!is_dir('uploads/' . $username)) {
        mkdir('uploads/' . $username, 0777, true);
    }

    echo "<div style='display: flex; justify-content: center; align-items: center; min-height: 30vh; font-size: 1.5em; color: green; font-weight: bold; text-align: center;'>";
    echo "✅ Usuario registrado correctamente. <a href='index.php' style='color: blue; text-decoration: underline;'>Iniciar sesión</a>";
    echo "</div>";
    exit();
}
?>
<head>
<link rel="stylesheet" href="estilos.css">
</head>

<div class="auth-container">
<h2>Registro de usuario</h2>
<form method="post">
    Nombre de usuario o correo: <input type="text" name="username" required><br><br>
    Contraseña: <input type="password" name="password" required><br><br>
    <button type="submit">Registrarme</button>
</form>

<p>¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a></p>
</div>