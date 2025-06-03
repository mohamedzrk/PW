<?php

include 'db.php';

// Si no hay sesión, devolvemos JSON vacío
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

// Sacamos todos los amigos aceptados
$sql = "
  SELECT 
    u.id, 
    CONCAT(u.nombre, ' ', u.apellidos) AS nombre_completo
  FROM usuario u
  JOIN amistad a
    ON (
         (a.usuario_id = $usuario_id AND a.amigo_id = u.id)
         OR
         (a.amigo_id  = $usuario_id AND a.usuario_id = u.id)
       )
  WHERE a.estado = 'aceptado'
  ORDER BY u.nombre, u.apellidos
";

$result = $mysqli->query($sql);
$amigos = [];

while ($row = $result->fetch_assoc()) {
    $amigos[] = [
        'id'             => (int)$row['id'],
        'nombre_completo'=> $row['nombre_completo']
    ];
}

// Devolver JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($amigos);
