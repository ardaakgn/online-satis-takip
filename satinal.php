<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['kullanici_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Giriş yapmalısınız']);
    exit;
}

$urun_id = $_POST['urun_id'] ?? null;
$adet = isset($_POST['adet']) ? (int)$_POST['adet'] : 0;

if (!$urun_id || $adet <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz veri']);
    exit;
}

$stmt = $db->prepare("SELECT stok FROM urunler WHERE id = ?");
$stmt->execute([$urun_id]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$urun) {
    http_response_code(404);
    echo json_encode(['error' => 'Ürün bulunamadı']);
    exit;
}

if ($urun['stok'] < $adet) {
    http_response_code(400);
    echo json_encode(['error' => 'Yetersiz stok']);
    exit;
}

$yeni_stok = $urun['stok'] - $adet;
$update = $db->prepare("UPDATE urunler SET stok = ? WHERE id = ?");
$update->execute([$yeni_stok, $urun_id]);

$log = $db->prepare("INSERT INTO stok_loglari (urun_id, hareket, miktar) VALUES (?, 'cikis', ?)");
$log->execute([$urun_id, $adet]);

echo json_encode(['success' => true]);
