<?php
// actividades_usuario.php
include 'db.php';
session_start();

// 1) Comprobar login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}
$me      = (int) $_SESSION['usuario_id'];
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) die('Usuario inválido');

// 2) Sólo amigos (o tú mismo) pueden ver
$stmt = $mysqli->prepare("
  SELECT 1 FROM amistad a
   WHERE (a.usuario_id=? AND a.amigo_id=?)
      OR (a.usuario_id=? AND a.amigo_id=?)
");
$stmt->bind_param('iiii', $me, $user_id, $user_id, $me);
$stmt->execute();
$es_amigo = (bool)$stmt->get_result()->fetch_assoc();
$stmt->close();


// 3) Traer actividades
$sql = "
  SELECT 
    a.id, a.titulo, a.fecha, a.aplausos,
    ta.nombre AS tipo,
    r.archivo AS gpx
  FROM actividad a
  JOIN tipo_actividad ta ON a.tipo_actividad_id = ta.id
  LEFT JOIN rutas r      ON r.actividad_id = a.id
  WHERE a.usuario_id = ?
  ORDER BY a.fecha DESC
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$acts = $stmt->get_result();
$stmt->close();

include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Actividades de Usuario</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/gpx.min.js"></script>
</head>
<body class="bg-index">


  <?php if ($acts->num_rows === 0): ?>
    <p>No hay actividades para mostrar.</p>
  <?php endif; ?>

  <?php while ($act = $acts->fetch_assoc()): ?>
    <div class="actividad">
      <div>
        <h2><?= htmlspecialchars($act['titulo']) ?></h2>
        <p>Tipo: <?= htmlspecialchars($act['tipo']) ?></p>
        <p>Fecha: <?= date('d/m/Y H:i', strtotime($act['fecha'])) ?></p>
        <p>Aplausos: <?= $act['aplausos'] ?></p>
      </div>

      <!-- Mapa GPX -->
      <?php if ($act['gpx']): ?>
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
      <?php endif; ?>

      <!-- Imágenes -->
      <?php
        $imgs = $mysqli->query("
          SELECT ruta 
          FROM imagenes 
          WHERE actividad_id = {$act['id']}
        ");
      ?>
      <?php if ($imgs->num_rows): ?>
        <div class="imagenes-actividad">
          <?php while ($img = $imgs->fetch_assoc()): ?>
            <img src="<?= htmlspecialchars($img['ruta']) ?>" alt="Foto actividad">
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>

   <a href="ver_perfilAmigo.php?id=<?= $user_id ?>" class="paginacion">⬅ Volver al perfil</a>
</body>
</html>
