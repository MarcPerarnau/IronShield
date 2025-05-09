<?php
session_start();

if (!isset($_SESSION['usuario_dni']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: whitelist.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];
$id         = intval($_POST['id']);
$tipo       = trim($_POST['tipo']);
$valor      = trim($_POST['valor']);
$rango      = trim($_POST['rango']);
$comentario = trim($_POST['comentario']);

// 🔧 CONEXIÓN DOCKERIZADA
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error de conexión: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

// Solo se permite editar entradas que pertenezcan a la empresa del usuario
$stmt = $conexion->prepare("
    UPDATE whitelist 
    SET tipo = ?, valor = ?, rango = ?, comentario = ?
    WHERE id = ? AND empresa_id = ?
");

$stmt->bind_param("ssssis", $tipo, $valor, $rango, $comentario, $id, $empresa_id);
$stmt->execute();
$stmt->close();
$conexion->close();

header("Location: whitelist.php?msg=editado");
exit();
