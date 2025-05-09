<?php
session_start();

if (!isset($_SESSION['usuario_dni']) || !isset($_GET['dni'])) {
    header("Location: usuarios.php");
    exit();
}

// 🔧 CONEXIÓN PARA DOCKER
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$dni = $_GET['dni'];
$empresa_id = $_SESSION['empresa_id'];

$stmt = $conexion->prepare("
    SELECT u.nombre, u.email, u.rol, u.dni, u.empresa_id, e.nombre AS empresa_nombre
    FROM usuarios u
    LEFT JOIN empresas e ON u.empresa_id = e.cif
    WHERE u.dni = ? AND u.empresa_id = ?
");
$stmt->bind_param("ss", $dni, $empresa_id);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();
$stmt->close();
$conexion->close();

if (!$usuario) {
    echo "Usuario no encontrado o no autorizado.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/variables.css">
<style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: var(--fuente);
        background-color: var(--fondo);
        color: var(--texto);
        margin: 0;
        padding: 0;
        display: flex;
    }

    .form-container {
        background-color: var(--seccion);
        padding: 40px;
        border-radius: 12px;
        width: 100%;
        max-width: 600px;
        margin: 60px auto;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    h1 {
        font-size: 26px;
        margin-bottom: 25px;
        color: var(--texto-secundario);
        text-align: center;
    }

    form label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="password"],
    form select {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        background-color: white;
    }

    form input:disabled {
        background-color: #e5e7eb;
        color: #6b7280;
    }

    button[type="submit"] {
        width: 100%;
        background-color: var(--boton);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button[type="submit"]:hover {
        background-color: var(--boton-hover);
    }

    @media (max-width: 768px) {
        .form-container {
            margin: 40px 20px;
        }
    }
</style>

</head>
<body>
<div class="form-container">
        <h1>Editar Usuario: <?php echo htmlspecialchars($usuario['dni']); ?></h1>

        <form action="procesar_edicion.php" method="POST">
            <input type="hidden" name="dni" value="<?php echo htmlspecialchars($usuario['dni']); ?>">

            <label>DNI:</label>
            <input type="text" value="<?php echo htmlspecialchars($usuario['dni']); ?>" disabled>

            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

            <label>Correo:</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>

            <label>Contraseña (dejar vacío para no cambiar):</label>
            <input type="password" name="contrasena" placeholder="Nueva contraseña (opcional)">

            <label>Rol:</label>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <select name="rol" required>
                    <option value="admin" <?php if ($usuario['rol'] === 'admin') echo 'selected'; ?>>Admin</option>
                    <option value="usuario" <?php if ($usuario['rol'] === 'usuario') echo 'selected'; ?>>Usuario</option>
                </select>
            <?php else: ?>
                <input type="text" value="<?php echo htmlspecialchars($usuario['rol']); ?>" disabled>
                <input type="hidden" name="rol" value="<?php echo htmlspecialchars($usuario['rol']); ?>">
            <?php endif; ?>

            <label>Empresa:</label>
            <input type="text" value="<?php echo htmlspecialchars($usuario['empresa_nombre']); ?>" disabled>

            <label>CIF:</label>
            <input type="text" value="<?php echo htmlspecialchars($usuario['empresa_id']); ?>" disabled>

            <button type="submit">Guardar cambios</button>
        </form>
    </div>
</body>
</html>