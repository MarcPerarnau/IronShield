<?php
session_start();

$conexion = new mysqli("db", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");
$conexion->begin_transaction();

try {
    $dni        = trim($_POST['dni']);
    $nombre     = trim($_POST['nombre']);
    $correo     = trim($_POST['correo']);
    $rol        = isset($_POST['rol']) ? $_POST['rol'] : 'admin';
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $plan       = $_POST['plan'];
    $tipoPago   = $_POST['tipoPago'];
    $tarjeta    = trim($_POST['tarjeta']);
    $caducidad  = trim($_POST['caducidad']) . "-01";
    $cvv        = trim($_POST['cvv']);

    $empresa_id = null;
    if (!empty($_POST['empresa']) && !empty($_POST['cif'])) {
        $empresa_nombre = trim($_POST['empresa']);
        $empresa_id     = trim($_POST['cif']);

        $stmtBuscar = $conexion->prepare("SELECT cif FROM empresas WHERE cif = ?");
        $stmtBuscar->bind_param("s", $empresa_id);
        $stmtBuscar->execute();
        $res = $stmtBuscar->get_result();
        if ($res->num_rows === 0) {
            $stmtEmpresa = $conexion->prepare("INSERT INTO empresas (cif, nombre) VALUES (?, ?)");
            $stmtEmpresa->bind_param("ss", $empresa_id, $empresa_nombre);
            if (!$stmtEmpresa->execute()) {
                throw new Exception("Error al insertar empresa: " . $stmtEmpresa->error);
            }
            $stmtEmpresa->close();
        }
        $stmtBuscar->close();
    }

    $planes = [
        'basico' => ['id' => 1, 'monto' => 5],
        'pro' => ['id' => 2, 'monto' => 15],
        'empresarial' => ['id' => 3, 'monto' => 30]
    ];

    if (!isset($planes[$plan])) {
        throw new Exception("Plan no válido.");
    }

    $id_plan = $planes[$plan]['id'];
    $monto = $planes[$plan]['monto'];
    if ($tipoPago === 'anual') {
        $monto *= 12 * 0.85;
    }

    if ($empresa_id) {
        $stmtUsuario = $conexion->prepare("INSERT INTO usuarios (dni, nombre, email, contrasena, empresa_id, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtUsuario->bind_param("ssssss", $dni, $nombre, $correo, $contrasena, $empresa_id, $rol);
    } else {
        $stmtUsuario = $conexion->prepare("INSERT INTO usuarios (dni, nombre, email, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
        $stmtUsuario->bind_param("sssss", $dni, $nombre, $correo, $contrasena, $rol);
    }

    if (!$stmtUsuario->execute()) {
        throw new Exception("Error al insertar usuario: " . $stmtUsuario->error);
    }

    $usuario_id = $dni; // Asumiendo que DNI es PK

    $stmtPago = $conexion->prepare("INSERT INTO pagos (usuario_id, empresa_id, plan_id, monto, metodo_pago, estado, referencia, periodicidad, activo)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $metodo_pago = "tarjeta";
    $estado = "pendiente";
    $referencia = uniqid("pago_");
    $activo = 1;

    $stmtPago->bind_param("ssidssssi", $usuario_id, $empresa_id, $id_plan, $monto, $metodo_pago, $estado, $referencia, $tipoPago, $activo);
    if (!$stmtPago->execute()) {
        throw new Exception("Error al registrar el pago: " . $stmtPago->error);
    }

    $_SESSION["dni"] = $dni;
    if ($empresa_id) {
        $_SESSION["empresa_id"] = $empresa_id;
    }

    $conexion->commit();
    header("Location: ../panel.php");
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    echo "❌ " . $e->getMessage();
}

if (isset($stmtUsuario)) $stmtUsuario->close();
if (isset($stmtPago)) $stmtPago->close();
$conexion->close();
?>
