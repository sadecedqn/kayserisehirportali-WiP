<?php
session_start();
ob_start();
session_start();
ob_start();
date_default_timezone_set('Europe/Istanbul');

$host = 'localhost';
$dbname = '...';
$username = '...';
$password = '...';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log('Veritabanı bağlantı hatası: ' . $e->getMessage());
    http_response_code(500);
    die('Şu anda hizmet veremiyoruz. Lütfen daha sonra tekrar deneyin.');
}
?>