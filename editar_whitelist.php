<?php
session_start();
if (!isset($_SESSION['usuario_dni']) || !isset($_GET['id'])) {
    header("Location: whitelist.php");
    exit();
}

$id = intval($_GET['id']);
$empresa_id = $_SESSION['empresa_id'];

// 🔧 CONEXIÓN ADAPTADA A DOCKER
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$stmt = $conexion->prepare("
    SELECT id, tipo, valor, rango, comentario
    FROM whitelist
    WHERE id = ? AND empresa_id = ?
");
$stmt->bind_param("is", $id, $empresa_id);
$stmt->execute();
$resultado = $stmt->get_result();
$entrada = $resultado->fetch_assoc();
$stmt->close();
$conexion->close();

if (!$entrada) {
    echo "❌ Entrada no encontrada o acceso no autorizado.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Whitelist</title>
    <link rel="stylesheet" href="css/variables.css">
    <style>
    /* ... estilo original intacto ... */
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Editar entrada Whitelist</h1>
        <form action="procesar_edicion_whitelist.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($entrada['id']); ?>">

            <label>Tipo:</label>
            <select name="tipo" required>
                <option value="IP" <?php if ($entrada['tipo'] === 'IP') echo 'selected'; ?>>IP</option>
                <option value="DNI" <?php if ($entrada['tipo'] === 'DNI') echo 'selected'; ?>>DNI</option>
                <option value="Email" <?php if ($entrada['tipo'] === 'Email') echo 'selected'; ?>>Email</option>
            </select>

            <label>Valor:</label>
            <input type="text" name="valor" value="<?php echo htmlspecialchars($entrada['valor']); ?>" required>

            <label>Rango:</label>
            <input type="text" name="rango" value="<?php echo htmlspecialchars($entrada['rango']); ?>">

            <label>Comentario:</label>
            <textarea name="comentario" rows="3"><?php echo htmlspecialchars($entrada['comentario']); ?></textarea>

            <button type="submit">Guardar cambios</button>
        </form>
    </div>
</body>
</html>
