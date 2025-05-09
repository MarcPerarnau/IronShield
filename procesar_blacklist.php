<?php
session_start();

if (!isset($_SESSION['usuario_dni']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: blacklist.php");
    exit();
}

$tipo       = trim($_POST['tipo']);
$valor      = trim($_POST['valor']);
$rango      = trim($_POST['rango']);
$comentario = trim($_POST['comentario']);
$empresa_id = $_SESSION['empresa_id'];
$usuario_id = $_SESSION['usuario_dni'];

// ⚙️ ADAPTACIÓN PARA DOCKER: usa "db" si tu contenedor MySQL se llama así
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

// Inserta en la base de datos (como antes)
$stmt = $conexion->prepare("
    INSERT INTO blacklist (tipo, valor, rango, comentario, usuario_id, empresa_id)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssssss", $tipo, $valor, $rango, $comentario, $usuario_id, $empresa_id);
$stmt->execute();
$stmt->close();
$conexion->close();

// 🔒 LLAMADA AL BACKEND PYTHON PARA BLOQUEAR IP/DOMINIO
$payload = json_encode(['ip' => $valor]);

$context = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/json',
        'content' => $payload
    ]
]);

// ⚠️ Asegúrate de que el contenedor Flask esté accesible en "backend" o cambia el host si lo necesitas
file_get_contents("http://backend:5000/block", false, $context);

header("Location: blacklist.php?msg=ok");
exit();
