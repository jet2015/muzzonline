<?php
session_start();
require_once __DIR__ . '/../../core/db_connect.php';
require_once __DIR__ . '/../../core/auth.php';

if (!isAdmin()) { die("Доступ запрещен."); }

$donationId = $_POST['donation_id'] ?? null;
$action = $_POST['action'] ?? null;

$allowedActions = ['approve', 'reject'];
if (empty($donationId) || !in_array($action, $allowedActions)) {
    die("Некорректные данные.");
}

$newStatus = ($action === 'approve') ? 'approved' : 'rejected';

try {
    $sql = "UPDATE donations SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newStatus, $donationId]);

    header("Location: /admin/donations.php");
    exit();
} catch (\PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}