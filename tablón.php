<?php
// tablón.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 2) Manejar aplauso vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aplaudir'])) {
    $aid = (int)$_POST['aplaudir'];
    $mysqli->query("UPDATE actividad SET aplausos = aplausos + 1 WHERE id = $aid");
    
}

// 3) Paginación
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit  = 5;
$offset = ($page - 1) * $limit;

// 4) Traer actividades con su GPX y tipo
$sql = "
  SELECT 
    a.id,
    a.titulo,
    a.fecha,
    a.aplausos,
    ta.nombre AS tipo,
    r.archivo AS gpx
  FROM actividad a
  JOIN tipo_actividad ta ON a.tipo_actividad_id = ta.id
  LEFT JOIN rutas r ON r.actividad_id = a.id
  ORDER BY a.fecha DESC
  LIMIT $offset, $limit
";
$acts = $mysqli->query($sql);

// Incluimos el header de usuarios
include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Tablón de Actividades</title>
  <link rel="stylesheet" href="styles.css">
  <!-- Cargar Leaflet desde carpeta local "leaflet" -->
  <link rel="stylesheet" href="leaflet/leaflet.css">
  <script src="leaflet/leaflet.js"></script>
  <script src="leaflet/gpx/gpx.js"></script>

</head>
<body class="bg-index">
  <h1>Tablón de Actividades</h1>

  <?php while ($act = $acts->fetch_assoc()): ?>
    <div class="actividad">
      <h2><?php echo $act['titulo']; ?></h2>
      <p>Tipo: <?php echo $act['tipo']; ?></p>
      <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($act['fecha'])); ?></p>
      <p>Aplausos: <?php echo $act['aplausos']; ?></p>

      <!-- Formulario de aplauso -->
      <form method="post">
        <input type="hidden" name="aplaudir" value="<?php echo $act['id']; ?>">
        <button class="btn" type="submit">Aplaudir</button>
      </form>

      <!-- Mapa GPX -->
      <div id="map-<?php echo $act['id']; ?>" class="mapa-actividad"></div>
      <script>
        (function(){
          var map = L.map('map-<?php echo $act['id']; ?>');
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);
          new L.GPX('<?php echo $act['gpx']; ?>', { async: true })
            .on('loaded', function(e) { map.fitBounds(e.target.getBounds()); })
            .addTo(map);
        })();
      </script>

      <!-- Participantes -->
      <?php
        $resC = $mysqli->query("
          SELECT u.nombre, u.apellidos
          FROM compania c
          JOIN usuario u ON c.usuario_id = u.id
          WHERE c.actividad_id = {$act['id']}
        ");
        $parts = [];
        while ($urow = $resC->fetch_assoc()) {
            $parts[] = $urow['nombre'] . ' ' . $urow['apellidos'];
        }
      ?>
      <p>Participantes: <?php echo implode(', ', $parts); ?></p>

      <!-- Imágenes -->
      <div class="imagenes-actividad">
        <?php
          $resI = $mysqli->query("SELECT ruta FROM imagenes WHERE actividad_id = {$act['id']}");
          while ($img = $resI->fetch_assoc()):
        ?>
          <img src="<?php echo $img['ruta']; ?>" alt="Foto actividad">
        <?php endwhile; ?>
      </div>
    </div>
  <?php endwhile; ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>" class="paginacion">Anterior</a>
    <?php endif; ?>
    <a href="?page=<?php echo $page + 1; ?>" class="paginacion">Siguiente</a>
  </div>
</body>
</html>

