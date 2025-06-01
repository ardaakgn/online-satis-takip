<?php
$host = "localhost";
$dbname = "stokuygulamasi";
$user = "root"; // kendi MySQL kullanıcı adın
$pass = "";     // kendi MySQL şifren

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>