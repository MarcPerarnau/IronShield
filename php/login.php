<?php
session_start();

$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
$conexion->begin_transaction();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni']);
    $password = trim($_POST['contrasena']);

    $stmt = $conexion->prepare("SELECT dni, contrasena, empresa_id, rol FROM usuarios WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    if ($usuario && password_verify($password, $usuario['contrasena'])) {
        $_SESSION['usuario_dni'] = $usuario['dni'];
        $_SESSION['empresa_id'] = $usuario['empresa_id'];
        $_SESSION['rol'] = $usuario['rol'];

        $conexion->commit();
        header("Location: ../panel.php");
        exit();
    } else {
        $conexion->rollback();
        echo "DNI o contraseña incorrectos.";
    }

    $stmt->close();
} else {
    $conexion->close();
    header("Location: ../index.html");
    exit();
}

$conexion->close();
?>
