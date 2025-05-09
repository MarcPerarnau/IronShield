<?php
session_start();

if (!isset($_SESSION['usuario_dni']) || !isset($_GET['dni'])) {
    header("Location: usuarios.php");
    exit();
}

// 🔧 CONEXIÓN ADAPTADA A DOCKER
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$dni = $_GET['dni'];
$empresa_id = $_SESSION['empresa_id'];

$stmt = $conexion->prepare("DELETE FROM usuarios WHERE dni = ? AND empresa_id = ?");
$stmt->bind_param("ss", $dni, $empresa_id);
$stmt->execute();
$stmt->close();

$conexion->close();

header("Location: usuarios.php");
exit();
