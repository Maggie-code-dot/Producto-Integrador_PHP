<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $users = json_decode(file_get_contents('users.json'), true);

    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {

            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role']; // Guardar el rol en la sesión
            header('Location: dashboard.php');
            exit();
        }
    }

    echo "Usuario o contraseña incorrectos.";
}
?>

<head>
<link rel="stylesheet" href="estilos.css">
</head>

<div class="auth-container">
<h3>Iniciar sesión</h3>
<form method="post">
    Nombre de usuario o correo: <br><input type="text" name="username" required><br><br>
    Contraseña: <input type="password" name="password" required><br>
    <button type="submit">Acceder</button>
</form>


<br><br><br><p>¿Sin cuenta? <a href="register.php">Regístrate aquí</a></p>
</div>