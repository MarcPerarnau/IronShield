<?php
session_start();
if (!isset($_SESSION['usuario_dni']) || !isset($_GET['id'])) {
    header("Location: blacklist.php");
    exit();
}

$id = intval($_GET['id']);
$empresa_id = $_SESSION['empresa_id'];

// 🔧 CONEXIÓN PARA DOCKER
$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$stmt = $conexion->prepare("
    SELECT id, tipo, valor, rango, comentario
    FROM blacklist
    WHERE id = ? AND empresa_id = ?
");
$stmt->bind_param("is", $id, $empresa_id);
$stmt->execute();
$res = $stmt->get_result();
$entrada = $res->fetch_assoc();
$stmt->close();
$conexion->close();

if (!$entrada) {
    echo "❌ Entrada no encontrada o no autorizada.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Blacklist</title>
    <link rel="stylesheet" href="css/variables.css">
    <style>
        body {
            font-family: var(--fuente);
            background-color: var(--fondo);
            color: var(--texto);
            padding: 40px;
        }
        .form-container {
            background-color: var(--seccion);
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: var(--texto-secundario);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: white;
        }
        button {
            width: 100%;
            background-color: var(--boton);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: var(--boton-hover);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Editar entrada de Blacklist</h1>
        <form action="procesar_edicion_blacklist.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $entrada['id']; ?>">

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
