<?php
session_start();
if (!isset($_SESSION['usuario_dni'])) {
    header("Location: index.html");
    exit();
}

// ✅ Conexión a base de datos adaptada para Docker
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$empresa_id = $_SESSION['empresa_id'];
$entradas = [];

$stmt = $conexion->prepare("SELECT id, tipo, valor, rango, fecha, comentario FROM whitelist WHERE empresa_id = ?");
$stmt->bind_param("s", $empresa_id);
$stmt->execute();
$res = $stmt->get_result();
while ($fila = $res->fetch_assoc()) {
    $entradas[] = $fila;
}
$stmt->close();
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Whitelist</title>
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/variables.css">
</head>
<body>
<?php include 'secciones/sidebar.php'; ?>

<main class="content">
    <div id="notificacion" class="notificacion oculto"></div>
    <h1>Whitelist</h1>
    <button class="btn" onclick="abrirModal()">➕ Añadir</button>

    <?php if (empty($entradas)): ?>
        <p>No hay entradas en la whitelist.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Valor</th>
                    <th>Rango</th>
                    <th>Comentario</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entradas as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['valor']); ?></td>
                        <td><?php echo htmlspecialchars($item['rango']); ?></td>
                        <td><?php echo htmlspecialchars($item['comentario']); ?></td>
                        <td><?php echo htmlspecialchars($item['fecha']); ?></td>
                        <td>
                            <a href="editar_whitelist.php?id=<?php echo $item['id']; ?>" title="Editar" class="accion-boton">📝</a>
                            <a href="eliminar_whitelist.php?id=<?php echo $item['id']; ?>" onclick="return confirm('¿Eliminar esta entrada de la whitelist?')" title="Eliminar" class="accion-boton">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Modal -->
    <div id="modal" class="modal oculto">
        <div class="modal-contenido">
            <span class="cerrar" onclick="cerrarModal()">×</span>
            <h2>Añadir a Whitelist</h2>
            <form action="procesar_whitelist.php" method="POST">
                <label for="valor">Valor:</label>
                <input type="text" name="valor" id="valor" required>

                <label for="rango">Rango:</label>
                <input type="text" name="rango" id="rango" placeholder="Opcional">

                <label for="comentario">Comentario:</label>
                <textarea name="comentario" id="comentario" rows="3" placeholder="Motivo o detalle..."></textarea>

                <button type="submit" class="btn">Guardar</button>
            </form>
        </div>
    </div>
</main>

<script>
function abrirModal() {
    document.getElementById("modal").classList.remove("oculto");
}
function cerrarModal() {
    document.getElementById("modal").classList.add("oculto");
}
function mostrarNotificacion(texto, tipo = 'info') {
    const box = document.getElementById('notificacion');
    box.textContent = texto;
    box.className = `notificacion visible ${tipo}`;
    setTimeout(() => {
        box.classList.remove('visible');
    }, 3500);
}
</script>

<?php if (isset($_GET['msg'])): ?>
<script>
    <?php if ($_GET['msg'] === 'ok'): ?>
        mostrarNotificacion("✅ Entrada añadida correctamente.", "exito");
    <?php elseif ($_GET['msg'] === 'eliminado'): ?>
        mostrarNotificacion("🗑️ Entrada eliminada.", "error");
    <?php elseif ($_GET['msg'] === 'editado'): ?>
        mostrarNotificacion("📝 Entrada editada con éxito.", "info");
    <?php endif; ?>
</script>
<?php endif; ?>
</body>
</html>
