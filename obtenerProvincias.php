<?php
// obtenerProvincias.php
// Devuelve en JSON todas las provincias de un país (recibido por GET)

// 1) Conexión a la BD y arranque de sesión
include 'db.php';

// 2) Validar que haya llegado un parámetro pais_id
if (!isset($_GET['pais_id'])) {
    echo json_encode([]);
    exit;
}

$pais_id = (int) $_GET['pais_id'];

// 3) Consultar las provincias que pertenezcan a ese país
$sql = "
  SELECT id, nombre
    FROM provincias
   WHERE pais_id = $pais_id
   ORDER BY nombre
";
$result = $mysqli->query($sql);

// 4) Construir el array de salida
$provincias = [];
while ($row = $result->fetch_assoc()) {
    $provincias[] = [
        'id'     => (int)$row['id'],
        'nombre' => $row['nombre']
    ];
}

// 5) Devolver JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($provincias);
