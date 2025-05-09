<?php
session_start();
if (!isset($_SESSION['usuario_dni'])) {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo       = trim($_POST['tipo']);
    $valor      = trim($_POST['valor']);
    $rango      = trim($_POST['rango']);
    $comentario = trim($_POST['comentario']);
    $empresa_id = $_SESSION['empresa_id'];
    $usuario_id = $_SESSION['usuario_dni'];

    // 🔧 CONEXIÓN ADAPTADA A DOCKER
    $conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
    if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
    $conexion->set_charset("utf8mb4");

    $stmt = $conexion->prepare("
        INSERT INTO whitelist (tipo, valor, rango, comentario, usuario_id, empresa_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $tipo, $valor, $rango, $comentario, $usuario_id, $empresa_id);
    $stmt->execute();
    $stmt->close();
    $conexion->close();

    // ✅ LLAMADA AL BACKEND PYTHON PARA PERMITIR IP/DOMINIO
    $payload = json_encode(['ip' => $valor]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => $payload
        ]
    ]);

    // Cambia "backend" por el nombre real del contenedor Flask en tu docker-compose
    file_get_contents("http://backend:5000/allow", false, $context);

    header("Location: whitelist.php?msg=ok");
    exit();
}
