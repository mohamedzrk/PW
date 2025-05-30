<?php
// tablon.php
include 'db.php';
session_start();

// 1) Sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 2) Manejar aplauso vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aplaudir'])) {
    $aid = (int)$_POST['aplaudir'];
    $mysqli->query("UPDATE actividad SET aplausos = aplausos + 1 WHERE id = $aid");
    // no redirección, recargamos la misma URL con POST (la barra no cambia)
}

// 3) Paginación
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit  = 5;
$offset = ($page - 1) * $limit;

// 4) Traer actividades con GPX y tipo
$sql = "
  SELECT 
    a.id, a.titulo, a.fecha, a.aplausos,
    ta.nombre AS tipo,
    r.archivo AS gpx
  FROM actividad a
  JOIN tipo_actividad ta ON a.tipo_actividad_id = ta.id
  LEFT JOIN rutas r ON r.actividad_id = a.id
  ORDER BY a.fecha DESC
  LIMIT $offset, $limit
";
$acts = $mysqli->query($sql);

include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Tablón de Actividades</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/gpx.min.js"></script>
</head>
<body class="bg-index">
  <h1>Tablón de Actividades</h1>

  <?php while ($act = $acts->fetch_assoc()): ?>
    <div class="actividad">
      <h2><?= $act['titulo'] ?></h2>
      <p>Tipo: <?= $act['tipo'] ?></p>
      <p>Fecha: <?= date('d/m/Y H:i', strtotime($act['fecha'])) ?></p>
      <p>Aplausos: <?= $act['aplausos'] ?></p>

      <!-- Formulario de aplauso -->
      <form method="post" style="display:inline">
        <input type="hidden" name="aplaudir" value="<?= $act['id'] ?>">
        <button class="btn" type="submit">Aplaudir</button>
      </form>

      <!-- Mapa GPX -->
      <div id="map-<?= $act['id'] ?>" class="mapa-actividad"></div>
      <script>
        (function(){
          var map = L.map('map-<?= $act['id'] ?>');
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:18 }).addTo(map);
          new L.GPX('<?= $act['gpx'] ?>',{async:true})
            .on('loaded', function(e){ map.fitBounds(e.target.getBounds()); })
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
        while ($u = $resC->fetch_assoc()) {
          $parts[] = $u['nombre'].' '.$u['apellidos'];
        }
      ?>
      <p>Participantes: <?= implode(', ', $parts) ?></p>

      <!-- Imágenes -->
      <div class="imagenes-actividad">
        <?php
          $resI = $mysqli->query("SELECT ruta FROM imagenes WHERE actividad_id = {$act['id']}");
          while ($img = $resI->fetch_assoc()):
        ?>
          <img src="<?= $img['ruta'] ?>" alt="Foto actividad">
        <?php endwhile; ?>
      </div>
    </div>
  <?php endwhile; ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>" class="paginacion">Anterior</a>
    <?php endif; ?>
    <a href="?page=<?= $page + 1 ?>" class="paginacion">Siguiente</a>
  </div>
</body>
</html>
