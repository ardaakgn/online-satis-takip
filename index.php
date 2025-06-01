<?php
session_start();
require 'db.php';

// Ürünleri çek
$urunler = $db->query("SELECT * FROM urunler")->fetchAll(PDO::FETCH_ASSOC);

// Satın alma işlemi (sadece giriş yapanlar)
if (isset($_SESSION['kullanici_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $urun_id = $_POST['urun_id'];
    $miktar = 1;
    $kullanici_id = $_SESSION['kullanici_id'];

    $urun = $db->prepare("SELECT * FROM urunler WHERE id = ?");
    $urun->execute([$urun_id]);
    $urun = $urun->fetch(PDO::FETCH_ASSOC);

    if ($urun && $urun['stok'] >= $miktar) {
        $satis = $db->prepare("INSERT INTO satislar (kullanici_id, urun_id, miktar) VALUES (?, ?, ?)");
        $satis->execute([$kullanici_id, $urun_id, $miktar]);

        $stokGuncelle = $db->prepare("UPDATE urunler SET stok = stok - ? WHERE id = ?");
        $stokGuncelle->execute([$miktar, $urun_id]);

        $log = $db->prepare("INSERT INTO stok_loglari (urun_id, hareket, miktar) VALUES (?, 'cikis', ?)");
        $log->execute([$urun_id, $miktar]);

        $mesaj = "Ürün başarıyla satın alındı!";
    } else {
        $mesaj = "Stok yetersiz.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" href="faviconresmi.png" type="image/x-icon" />
    <meta charset="UTF-8">
    <title>Anasayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Mağaza</a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-sm btn-outline-light ">Çıkış Yap</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item me-2">
                        <a href="giris.php" class="btn btn-sm btn-outline-light">Giriş Yap</a>
                    </li>
                    <li class="nav-item">
                        <a href="kayit.php" class="btn btn-sm btn-outline-light">Kayıt Ol</a>
                    </li>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['kullanici_id'])): ?>
                <?php
                $stmt = $db->prepare("SELECT yetki FROM kullanicilar WHERE id = ?");
                $stmt->execute([$_SESSION['kullanici_id']]);
                $yetki = $stmt->fetchColumn();
                ?>
                <?php if ($yetki === 'admin'): ?>
                    <li class="nav-item me-2 ">
                        <a href="admin/index.php" class="btn btn-sm btn-warning">Admin Paneli</a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (isset($mesaj)) echo "<div class='alert alert-info'>$mesaj</div>"; ?>

    <h4 class="mb-3">Ürünler</h4>
    <div class="row">
        <?php foreach ($urunler as $urun): ?>
            <form class="satinalForm card mb-3 p-3" data-urunadi="<?= htmlspecialchars($urun['urun_adi']) ?>">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5><?= htmlspecialchars($urun['urun_adi']) ?></h5>
                        <p><?= htmlspecialchars($urun['aciklama']) ?></p>
                        <p><strong>Fiyat:</strong> <?= number_format($urun['fiyat'], 2) ?> ₺</p>
                        <p><strong>Stok:</strong> <?= $urun['stok'] ?></p>
                    </div>
                    <div class="col-md-3">
                        <label for="adet_<?= $urun['id'] ?>" class="form-label">Adet</label>
                        <input type="number" 
                            id="adet_<?= $urun['id'] ?>" 
                            name="adet" 
                            class="form-control" 
                            value="1" 
                            min="1" 
                            max="<?= $urun['stok'] ?>" 
                            required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">
                        <button type="submit" class="btn btn-success w-100">Satın Al</button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>
    </div>

            <!-- jQuery CDN (eğer yoksa ekle) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <!-- Modal HTML -->
        <div class="modal fade" id="satinalModal" tabindex="-1" aria-labelledby="satinalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="satinalModalLabel">Satın Alma Başarılı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <!-- Mesaj buraya gelecek -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tamam</button>
            </div>
            </div>
        </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        $(document).ready(function(){
            $('.satinalForm').submit(function(e){
                e.preventDefault();

                var form = $(this);
                var urunId = form.find('input[name="urun_id"]').val();
                var adet = form.find('input[name="adet"]').val();
                var urunAdi = form.data('urunadi');

                $.ajax({
                    url: 'satinal.php',
                    type: 'POST',
                    data: { urun_id: urunId, adet: adet },
                    success: function(response){
                        // Başarılıysa modalda mesaj göster
                        $('#modalBodyContent').html("<p><strong>" + urunAdi + "</strong> ürününden <strong>" + adet + "</strong> adet satın aldınız.</p>");
                        var modal = new bootstrap.Modal(document.getElementById('satinalModal'));

                         // Modal açıldıktan sonra tamam butonuna tıklandığında sayfa yenilensin
                        var tamamBtn = document.querySelector('#satinalModal .btn-primary');
                        tamamBtn.onclick = function() {
                            location.reload();
                        };
                        modal.show();
                    },
                    error: function(){
                        alert('Satın alma işlemi sırasında hata oluştu.');
                    }
                });
            });
        });
        </script>
</div>
</body>
</html>