<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST["kullanici_adi"]);
    $email = trim($_POST["email"]);
    $sifre = password_hash($_POST["sifre"], PASSWORD_DEFAULT); // Şifre güvenli olsun

    // Kullanıcı adı veya email kontrolü (isteğe bağlı)
    $kontrol = $db->prepare("SELECT * FROM kullanicilar WHERE email = ? OR kullanici_adi = ?");
    $kontrol->execute([$email, $kullanici_adi]);
    if ($kontrol->rowCount() > 0) {
        $mesaj = "Bu e-posta veya kullanıcı adı zaten kullanılıyor.";
    } else {
        // Kayıt işlemi
        $query = $db->prepare("INSERT INTO kullanicilar (kullanici_adi, email, sifre, yetki) VALUES (?, ?, ?, 'kullanici')");
        $query->execute([$kullanici_adi, $email, $sifre]);
        $mesaj = "Kayıt başarılı! Giriş yapabilirsiniz.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" href="faviconresmi.png" type="image/x-icon" />

    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="text-center mb-4">Kayıt Ol</h3>
                    <?php if (isset($mesaj)) echo "<div class='alert alert-info'>$mesaj</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Kullanıcı Adı</label>
                            <input type="text" name="kullanici_adi" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Şifre</label>
                            <input type="password" name="sifre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>E-posta</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
                        <p class="text-center mt-3">Zaten üye misin? <a href="giris.php">Giriş yap</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>