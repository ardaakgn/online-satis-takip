<?php
require '../db.php';

// Giriş ve yetki kontrolü
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: ../login.php");
    exit;
}

$yetki = $db->prepare("SELECT yetki FROM kullanicilar WHERE id = ?");
$yetki->execute([$_SESSION['kullanici_id']]);
if ($yetki->fetchColumn() !== 'admin') {
    echo "Erişim izniniz yok.";
    exit;
}

// Stok loglarını çek
$loglar = $db->query("
    SELECT sl.*, u.urun_adi 
    FROM stok_loglari sl 
    JOIN urunler u ON sl.urun_id = u.id 
    ORDER BY sl.tarih DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Stok Logları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>📊 Stok Hareketleri Logları</h2>

    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Ürün</th>
                    <th>Hareket</th>
                    <th>Miktar</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($loglar) > 0): ?>
                    <?php foreach ($loglar as $index => $log): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($log['urun_adi']) ?></td>
                            <td>
                                <span class="badge bg-<?= $log['hareket'] === 'giris' ? 'success' : 'danger' ?>">
                                    <?= strtoupper($log['hareket']) ?>
                                </span>
                            </td>
                            <td><?= $log['miktar'] ?></td>
                            <td><?= date("d.m.Y H:i", strtotime($log['tarih'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Henüz log bulunmamaktadır.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>