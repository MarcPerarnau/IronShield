<?php
session_start();

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario_dni'])) {
    header("Location: index.html");
    exit();
}

// Conexión adaptada para entorno Docker
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error de conexión: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

// Traer usuarios de la misma empresa
$empresa_id = $_SESSION['empresa_id'];
$usuarios = [];

if (!empty($empresa_id)) {
    $stmt = $conexion->prepare("SELECT nombre, dni, email, rol FROM usuarios WHERE empresa_id = ?");
    $stmt->bind_param("s", $empresa_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
    $stmt->close();
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/variables.css">
</head>
<body>
<?php include 'secciones/sidebar.php'; ?>

<main class="content">
<div id="notificacion" class="notificacion oculto"></div>
    <h1>Usuarios de la Empresa</h1>
    <button class="btn" onclick="abrirModal()">➕ Añadir Usuario</button>

    <?php if (empty($usuarios)): ?>
        <p>No hay usuarios registrados en esta empresa.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['dni']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                        <td>
                            <a href="editar_usuario.php?dni=<?php echo urlencode($usuario['dni']); ?>">📝</a>
                            <a href="eliminar_usuario.php?dni=<?php echo urlencode($usuario['dni']); ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">🗑️</a>
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
            <h2>Nuevo Usuario</h2>
            <form action="procesar_usuario.php" method="POST">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>

                <label>DNI:</label>
                <input type="text" name="dni" required>

                <label>Correo:</label>
                <input type="email" name="correo" required>

                <label>Contraseña:</label>
                <input type="password" name="contrasena" required>

                <label>Rol:</label>
                <select name="rol" required>
                    <option value="admin">Admin</option>
                    <option value="usuario">Usuario</option>
                </select>

                <label>CIF:</label>
                <input type="text" value="<?php echo htmlspecialchars($empresa_id); ?>" disabled>
                <input type="hidden" name="cif" value="<?php echo htmlspecialchars($empresa_id); ?>">

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
        mostrarNotificacion("✅ Acción completada correctamente.", "exito");
    <?php elseif ($_GET['msg'] === 'eliminado'): ?>
        mostrarNotificacion("🗑️ Entrada eliminada.", "error");
    <?php elseif ($_GET['msg'] === 'editado'): ?>
        mostrarNotificacion("✏️ Entrada editada con éxito.", "info");
    <?php endif; ?>
</script>
<?php endif; ?>

</body>
</html>
