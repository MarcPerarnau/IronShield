<?php
session_start();

if (!isset($_SESSION['usuario_dni'])) {
    header("Location: index.html");
    exit();
}

// 🔧 CONEXIÓN ADAPTADA PARA DOCKER
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error de conexión: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

try {
    $nombre     = trim($_POST['nombre']);
    $dni        = trim($_POST['dni']);
    $correo     = trim($_POST['correo']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $rol        = $_POST['rol'];
    $cif        = trim($_POST['cif']);

    $conexion->begin_transaction();

    // Verificar si el CIF ya existe
    $stmt = $conexion->prepare("SELECT cif FROM empresas WHERE cif = ?");
    $stmt->bind_param("s", $cif);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $nombre_empresa = "Empresa no definida";
        $stmt_insert = $conexion->prepare("INSERT INTO empresas (cif, nombre) VALUES (?, ?)");
        $stmt_insert->bind_param("ss", $cif, $nombre_empresa);
        if (!$stmt_insert->execute()) {
            throw new Exception("Error al insertar empresa: " . $stmt_insert->error);
        }
        $stmt_insert->close();
    }
    $stmt->close();

    // Insertar nuevo usuario
    $stmt_user = $conexion->prepare("INSERT INTO usuarios (dni, nombre, email, contrasena, rol, empresa_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_user->bind_param("ssssss", $dni, $nombre, $correo, $contrasena, $rol, $cif);
    if (!$stmt_user->execute()) {
        throw new Exception("Error al insertar usuario: " . $stmt_user->error);
    }
    $stmt_user->close();

    $conexion->commit();
    header("Location: usuarios.php");
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    echo "❌ " . $e->getMessage();
}

$conexion->close();
?>
