<?php
session_start();

// Verificar que hay sesión y se recibió el DNI
if (!isset($_SESSION['usuario_dni']) || !isset($_POST['dni'])) {
    header("Location: usuarios.php");
    exit();
}

// Conexión a la base de datos (adaptada a Docker)
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error de conexión: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

// Sanitizar datos recibidos del formulario
$dni     = trim($_POST['dni']);
$nombre  = trim($_POST['nombre']);
$correo  = trim($_POST['correo']);
$rol     = trim($_POST['rol']);
$empresa_id = $_SESSION['empresa_id'];

// Validar rol
if (!in_array($rol, ['admin', 'usuario'])) {
    $rol = 'usuario'; // fallback seguro
}

// Actualizar el usuario
$stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE dni = ? AND empresa_id = ?");
$stmt->bind_param("sssss", $nombre, $correo, $rol, $dni, $empresa_id);
$stmt->execute();
$stmt->close();

$conexion->close();

// Redirigir de vuelta al listado de usuarios
header("Location: usuarios.php");
exit();
