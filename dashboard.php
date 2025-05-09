<?php
session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_dni'])) {
    header("Location: index.html"); // o login.html si usas otro archivo
    exit();
}

if (empty($_SESSION['empresa_id'])) {
    echo "<p style='padding: 20px; color: var(--error); font-weight: bold;'>⚠️ Acceso denegado. Esta sección está reservada para usuarios asociados a una empresa.</p>";
    exit();
}

// ⚙️ ADAPTADO A DOCKER
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
$conexion->set_charset("utf8mb4");

$empresa_id = $_SESSION['empresa_id'];

$total_usuarios = $conexion->query("SELECT COUNT(*) FROM usuarios WHERE empresa_id = '$empresa_id'")->fetch_row()[0];
$total_whitelist = $conexion->query("SELECT COUNT(*) FROM whitelist WHERE empresa_id = '$empresa_id'")->fetch_row()[0];
$total_blacklist = $conexion->query("SELECT COUNT(*) FROM blacklist WHERE empresa_id = '$empresa_id'")->fetch_row()[0];

$res = $conexion->query("SELECT evento FROM logs_sniffer WHERE empresa_id = '$empresa_id' AND severidad = 'crítica' ORDER BY fecha DESC LIMIT 1");
$ultima_alerta = $res->num_rows ? $res->fetch_assoc()['evento'] : null;

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/variables.css">
</head>
<body>
<?php include 'secciones/sidebar.php'; ?>

<main class="content">
    <h1>Dashboard</h1>
    <p>Bienvenido al panel de administración.</p>

    <div class="cards">
        <div class="card resumen">
            <span class="icono">👥</span>
            <h2>Usuarios</h2>
            <p class="dato"><?php echo $total_usuarios ?? 0; ?></p>
        </div>

        <div class="card whitelist">
            <span class="icono">✅</span>
            <h2>Whitelist</h2>
            <p class="dato"><?php echo $total_whitelist ?? 0; ?></p>
        </div>

        <div class="card blacklist">
            <span class="icono">🚫</span>
            <h2>Blacklist</h2>
            <p class="dato"><?php echo $total_blacklist ?? 0; ?></p>
        </div>

        <div class="card critica">
            <span class="icono">⚠️</span>
            <h2>Última alerta crítica</h2>
            <p class="dato"><?php echo $ultima_alerta ?? 'Sin registros'; ?></p>
        </div>
    </div>
</main>
</body>
</html>
