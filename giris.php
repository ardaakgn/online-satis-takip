<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    $query = $db->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ?");
    $query->execute([$kullanici_adi]);
    $kullanici = $query->fetch(PDO::FETCH_ASSOC);

    if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {
        $_SESSION['kullanici_id'] = $kullanici['id'];
        $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
        header("Location: index.php");
        exit;
    } else {
        $mesaj = "Hatalı kullanıcı adı veya şifre.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" href="faviconresmi.png" type="image/x-icon" />

    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="text-center mb-4">Giriş Yap</h3>
                    <?php if (isset($mesaj)) echo "<div class='alert alert-danger'>$mesaj</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Kullanıcı Adı</label>
                            <input type="text" name="kullanici_adi" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Şifre</label>
                            <input type="password" name="sifre" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Giriş Yap</button>
                        <p class="text-center mt-3">Hesabın yok mu? <a href="kayit.php">Kayıt ol</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>