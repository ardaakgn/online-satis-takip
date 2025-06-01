<?php
require '../db.php';

// Yetki kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: ../kayit.php");
    exit;
}

$yetkiSorgu = $db->prepare("SELECT yetki FROM kullanicilar WHERE id = ?");
$yetkiSorgu->execute([$_SESSION['kullanici_id']]);
if ($yetkiSorgu->fetchColumn() !== 'admin') {
    echo "Erişim izniniz yok.";
    exit;
}

// Yeni ürün ekleme
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['urun_ekle'])) {
    $ad = $_POST['urun_adi'];
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $stok = $_POST['stok'];

    $ekle = $db->prepare("INSERT INTO urunler (urun_adi, aciklama, fiyat, stok) VALUES (?, ?, ?, ?)");
    $ekle->execute([$ad, $aciklama, $fiyat, $stok]);

    // Stok log ekle (ilk giriş)
    $urun_id = $db->lastInsertId();
    $log = $db->prepare("INSERT INTO stok_loglari (urun_id, hareket, miktar) VALUES (?, 'giris', ?)");
    $log->execute([$urun_id, $stok]);

    $mesaj = "Yeni ürün başarıyla eklendi!";
}



// Ürün güncelleme
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['urun_guncelle'])) {
    $id = $_POST['id'];
    $yeni_ad = $_POST['urun_adi'];
    $yeni_aciklama = $_POST['aciklama'];
    $yeni_fiyat = $_POST['fiyat'];
    $yeni_stok = $_POST['stok'];

    // Eski stok miktarını al
    $eski = $db->prepare("SELECT stok FROM urunler WHERE id = ?");
    $eski->execute([$id]);
    $eski_stok = $eski->fetchColumn();

    $fark = $yeni_stok - $eski_stok;
    $hareket = $fark > 0 ? 'giris' : 'cikis';

    $guncelle = $db->prepare("UPDATE urunler SET urun_adi=?, aciklama=?, fiyat=?, stok=? WHERE id=?");
    $guncelle->execute([$yeni_ad, $yeni_aciklama, $yeni_fiyat, $yeni_stok, $id]);

    // Stok logu sadece değişiklik varsa yaz
    if ($fark != 0) {
        $log = $db->prepare("INSERT INTO stok_loglari (urun_id, hareket, miktar) VALUES (?, ?, ?)");
        $log->execute([$id, $hareket, abs($fark)]);
    }

    $mesaj = "Ürün güncellendi!";
}

// Ürün silme işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['urun_sil'])) {
    $sil_id = $_POST['id'];

    // Önce ürünün stok bilgisini alalım (log için)
    $stok_sorgu = $db->prepare("SELECT stok FROM urunler WHERE id = ?");
    $stok_sorgu->execute([$sil_id]);
    $stok_miktari = $stok_sorgu->fetchColumn();

    // Ürünü sil
    $sil = $db->prepare("DELETE FROM urunler WHERE id = ?");
    $sil->execute([$sil_id]);

    // Log kaydı (stoktan çıkış gibi düşebiliriz)
    if ($stok_miktari > 0) {
        $log = $db->prepare("INSERT INTO stok_loglari (urun_id, hareket, miktar) VALUES (?, 'cikis', ?)");
        $log->execute([$sil_id, $stok_miktari]);
    }

    $mesaj = "Ürün başarıyla silindi!";
}

// Tüm ürünleri al
$urunler = $db->query("SELECT * FROM urunler")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Ürün Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="faviconresmi.png" type="image/x-icon" />

</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>🛠️ Ürün Yönetimi</h2>

    <?php if (isset($mesaj)): ?>
        <div class="alert alert-info"><?= $mesaj ?></div>
    <?php endif; ?>

    <!-- Yeni Ürün Ekle -->
    <div class="card mb-4">
        <div class="card-header">Yeni Ürün Ekle</div>
        <div class="card-body">
            <form method="post">
                <div class="mb-2">
                    <input type="text" name="urun_adi" class="form-control" placeholder="Ürün Adı" required>
                </div>
                <div class="mb-2">
                    <textarea name="aciklama" class="form-control" placeholder="Açıklama" required></textarea>
                </div>
                <div class="mb-2">
                    <input type="number" step="0.01" name="fiyat" class="form-control" placeholder="Fiyat (TL)" required>
                </div>
                <div class="mb-2">
                    <input type="number" name="stok" class="form-control" placeholder="Stok Miktarı" required>
                </div>
                <button type="submit" name="urun_ekle" class="btn btn-success w-100">Ekle</button>
            </form>
        </div>
    </div>

    <!-- Ürünleri Listele ve Güncelle -->
    <h4 class="mb-3">📦 Mevcut Ürünler</h4>
    <?php foreach ($urunler as $urun): ?>
        <form method="post" class="card mb-3">
            <div class="card-body">
                <input type="hidden" name="id" value="<?= $urun['id'] ?>">
                <div class="row g-2">
                    <div class="col-md-3">
                        <input type="text" name="urun_adi" class="form-control" value="<?= htmlspecialchars($urun['urun_adi']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="aciklama" class="form-control" value="<?= htmlspecialchars($urun['aciklama']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="fiyat" class="form-control" value="<?= $urun['fiyat'] ?>" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="stok" class="form-control" value="<?= $urun['stok'] ?>" required>
                    </div>
                    <div class="col-md-2">
                         <button type="submit" name="urun_guncelle" class="btn btn-primary flex-grow-1">Güncelle</button>
                        <button type="submit" name="urun_sil" onclick="return confirm('Bu ürünü silmek istediğine emin misin?');" class="btn btn-danger flex-grow-1">Sil</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endforeach; ?>
</div>
</body>
</html>