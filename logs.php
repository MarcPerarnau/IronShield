<?php
session_start();

// Bloquear si no está autenticado
if (!isset($_SESSION['usuario_dni'])) {
    header("Location: index.html");
    exit();
}

// Bloquear si no pertenece a una empresa
if (empty($_SESSION['empresa_id'])) {
    echo "<p style='padding: 20px; color: red; font-weight: bold;'>⚠️ Acceso denegado. Esta sección es solo para usuarios asociados a una empresa.</p>";
    exit();
}

$empresa_id = $_SESSION['empresa_id'];

// 🔧 ADAPTACIÓN A DOCKER
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error de conexión: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$logs = [];

$stmt = $conexion->prepare("SELECT fecha, evento, ip_origen, ip_destino, severidad FROM logs_sniffer WHERE empresa_id = ? ORDER BY id DESC LIMIT 100");
$stmt->bind_param("s", $empresa_id);
$stmt->execute();
$resultado = $stmt->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $logs[] = $fila;
}
$stmt->close();
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Logs del sistema</title>
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/variables.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: var(--seccion);
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
<?php include 'secciones/sidebar.php'; ?>

<main class="content">
    <h1>Logs del Sniffer</h1>

    <?php if (empty($logs)): ?>
        <p>No hay registros aún.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Evento</th>
                    <th>IP Origen</th>
                    <th>IP Destino</th>
                    <th>Severidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($log['evento']); ?></td>
                        <td><?php echo htmlspecialchars($log['ip_origen']); ?></td>
                        <td><?php echo htmlspecialchars($log['ip_destino']); ?></td>
                        <td><?php echo htmlspecialchars($log['severidad']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
