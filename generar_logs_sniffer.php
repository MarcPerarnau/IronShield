<?php
$conexion = new mysqli("localhost", "superiron", "]zIiZHz-Hq8eHR2h", "ironshield");
if ($conexion->connect_error) die("Error: " . $conexion->connect_error);
$conexion->set_charset("utf8mb4");

$eventos_por_severidad = [
    'baja' => [
        'Consulta DNS común',
        'Tráfico HTTP permitido',
        'Ping ICMP desde red interna',
        'Escaneo leve detectado',
        'Acceso autorizado registrado'
    ],
    'media' => [
        'Acceso a recurso sin autorización',
        'Petición sospechosa a puerto 8080',
        'Escaneo de puertos TCP',
        'Actividad anómala HTTP',
        'Archivo descargado con posible firma'
    ],
    'alta' => [
        'Intento de acceso a panel de administración',
        'Inyección SQL detectada',
        'Autenticación fallida repetida',
        'Tráfico anómalo a servidor SMTP',
        'Descarga de ejecutable sospechoso'
    ],
    'crítica' => [
        'Exploit remoto detectado',
        'Acceso root remoto bloqueado',
        'Transferencia de datos a IP en lista negra',
        'Ataque DDoS en curso',
        'Shell reversa detectada'
    ]
];

// Severidades con pesos
$severidades = array_merge(
    array_fill(0, 60, 'baja'),
    array_fill(0, 20, 'media'),
    array_fill(0, 15, 'alta'),
    array_fill(0, 5, 'crítica')
);

function generarIP() {
    return rand(10, 200) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254);
}

function fechaAleatoria() {
    $dias = rand(0, 6);         // hasta 6 días atrás
    $horas = rand(0, 23);
    $minutos = rand(0, 59);
    $segundos = rand(0, 59);
    return date('Y-m-d H:i:s', strtotime("-$dias days -$horas hours -$minutos minutes -$segundos seconds"));
}

for ($i = 0; $i < 10; $i++) {
    $severidad = $severidades[array_rand($severidades)];
    $evento = $eventos_por_severidad[$severidad][array_rand($eventos_por_severidad[$severidad])];
    $ip_origen = generarIP();
    $ip_destino = generarIP();
    $fecha = fechaAleatoria();
    $usuario_id = '23867227N';
    $empresa_id = 'A86346368';

    $stmt = $conexion->prepare("
        INSERT INTO logs_sniffer (evento, severidad, fecha, usuario_id, empresa_id, ip_origen, ip_destino)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssss", $evento, $severidad, $fecha, $usuario_id, $empresa_id, $ip_origen, $ip_destino);
    $stmt->execute();
    $stmt->close();
}

$conexion->close();
echo "✅ 10 logs generados con fechas variadas en los últimos 7 días.";
