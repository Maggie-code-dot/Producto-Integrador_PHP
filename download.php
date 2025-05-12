<?php
session_start();


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
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    } else {
        echo "❌ El archivo no existe.";
    }
} else {
    echo "❌ No se especificó el archivo.";
}
?>
