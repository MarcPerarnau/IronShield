<?php
session_start();
if (!isset($_SESSION['usuario_dni'])) {
    header("Location: index.html");
    exit();
}
header("Location: dashboard.php");
exit();
