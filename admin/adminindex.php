<?php
session_start();
require '../db.php';

// Giriş yapmamışsa yönlendir
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: ../kayit.php");
    exit;
}

// Yetki kontrolü
$kullanici = $db->prepare("SELECT yetki FROM kullanicilar WHERE id = ?");
$kullanici->execute([$_SESSION['kullanici_id']]);
$yetki = $kullanici->fetchColumn();

if ($yetki !== 'admin') {
    echo "Bu sayfaya erişim yetkiniz yok.";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Yönetim Paneli</title>
    <link rel="icon" href="../faviconresmi.png" type="image/x-icon" />

</head>
<body>
    <?php include 'header.php'; ?>
    <?php
    include 'urun_yonetimi.php';?>
    <hr>
    <?php
    include 'stok_loglari.php';?>
    
    
    
</body>
</html>