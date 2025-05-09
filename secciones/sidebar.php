<?php
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$dni = $_SESSION['usuario_dni'];
$empresa_id = $_SESSION['empresa_id'];
$rol = $_SESSION['rol'];

// Obtener nombre del usuario
$stmt = $conexion->prepare("SELECT nombre FROM usuarios WHERE dni = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Obtener datos de empresa si tiene
$empresa = null;
if (!empty($empresa_id)) {
    $stmt = $conexion->prepare("SELECT nombre, cif FROM empresas WHERE cif = ?");
    $stmt->bind_param("s", $empresa_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $empresa = $result->fetch_assoc();
    $stmt->close();
}
?>

<div class="sidebar">
    <h2>Panel Admin</h2>

    <div class="user-info">
        <p><strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong></p>
        <?php if ($empresa): ?>
            <p>Empresa: <?php echo htmlspecialchars($empresa['nombre']); ?></p>
        <?php endif; ?>
        <p>Rol: <?php echo htmlspecialchars($rol); ?></p>
    </div>

    <hr>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="whitelist.php">Whitelist</a></li>
        <li><a href="blacklist.php">Blacklist</a></li>
        <li><a href="logs.php">Logs</a></li>
        <?php if (!empty($_SESSION['empresa_id'])): ?>
            <li><a href="usuarios.php">Usuarios</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Cerrar sesión</a></li>
    </ul>
</div>
