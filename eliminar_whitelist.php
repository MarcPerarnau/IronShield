<?php
session_start();

if (!isset($_SESSION['usuario_dni']) || !isset($_GET['id'])) {
    header("Location: whitelist.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];
$id = intval($_GET['id']);

$conexion = new mysqli("localhost", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

// Borramos solo si pertenece a la misma empresa
$stmt = $conexion->prepare("DELETE FROM whitelist WHERE id = ? AND empresa_id = ?");
$stmt->bind_param("is", $id, $empresa_id);
$stmt->execute();
$stmt->close();
$conexion->close();

header("Location: whitelist.php?msg=eliminado");
exit();
<?php
session_start();

if (!isset($_SESSION['usuario_dni']) || !isset($_GET['id'])) {
    header("Location: whitelist.php");
    exit();
}

$empresa_id = $_SESSION['empresa_id'];
$id = intval($_GET['id']);

// 🔧 CONEXIÓN DOCKERIZADA
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

// Borrar solo si pertenece a la misma empresa
$stmt = $conexion->prepare("DELETE FROM whitelist WHERE id = ? AND empresa_id = ?");
$stmt->bind_param("is", $id, $empresa_id);
$stmt->execute();
$stmt->close();
$conexion->close();

header("Location: whitelist.php?msg=eliminado");
exit();
