<?php
session_start();

if (!isset($_SESSION['usuario_dni']) || !isset($_GET['id'])) {
    header("Location: blacklist.php");
    exit();
}

$id = intval($_GET['id']);
$empresa_id = $_SESSION['empresa_id'];

// 🔧 CONEXIÓN DOCKERIZADA
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$stmt = $conexion->prepare("DELETE FROM blacklist WHERE id = ? AND empresa_id = ?");
$stmt->bind_param("is", $id, $empresa_id);
$stmt->execute();
$stmt->close();
$conexion->close();

header("Location: blacklist.php?msg=eliminado");
exit();
