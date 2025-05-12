<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}


if (isset($_GET['file'])) {
    $username = $_SESSION['username'];
    $userDir = 'uploads/' . $username;
    $file = basename($_GET['file']);
    $filePath = $userDir . '/' . $file;

   
    if (file_exists($filePath)) {
        unlink($filePath); 
        echo "Imagen eliminada con éxito";
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "No se especificó el archivo.";
}


header('Location: dashboard.php');
exit();
?>
