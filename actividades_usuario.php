<?php
// actividades_usuario.php
include 'db.php';

// 1) Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    die('Usuario inválido');
}

// 2) Traer todas las actividades del usuario
$sqlActivities = "
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
  WHERE a.usuario_id = $user_id
  ORDER BY a.fecha DESC
";
$acts = $mysqli->query($sqlActivities);

include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Actividades de Usuario</title>
  <link rel="stylesheet" href="styles.css">
  <!-- Cargar Leaflet desde carpeta local "leaflet" -->
  <link rel="stylesheet" href="leaflet/leaflet.css">
  <script src="leaflet/leaflet.js"></script>
  <script src="leaflet/gpx/gpx.js"></script>
</head>
<body class="bg-index">

  <?php if (!$acts || $acts->num_rows === 0): ?>
    <p>No hay actividades para mostrar.</p>
  <?php endif; ?>

  <?php while ($act = $acts->fetch_assoc()): ?>
    <div class="actividad">
      <h2><?php echo $act['titulo']; ?></h2>
      <p>Tipo: <?php echo $act['tipo']; ?></p>
      <p>Fecha: <?php echo date('d/m/Y H:i', strtotime($act['fecha'])); ?></p>
      <p>Aplausos: <?php echo $act['aplausos']; ?></p>

      <!-- Mapa GPX (si existe) -->
      <?php if ($act['gpx']): ?>
        <div id="map-<?php echo $act['id']; ?>" class="mapa-actividad"></div>
        <script>
          (function(){
            var map = L.map('map-<?php echo $act['id']; ?>');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              maxZoom: 18
            }).addTo(map);
            new L.GPX('<?php echo $act['gpx']; ?>', {
              async: true
            }).on('loaded', function(e) {
              map.fitBounds(e.target.getBounds());
            }).addTo(map);
          })();
        </script>
      <?php endif; ?>

      <!-- Mostrar imágenes asociadas -->
      <?php
        $sqlImgs = "
          SELECT ruta 
          FROM imagenes 
          WHERE actividad_id = {$act['id']}
        ";
        $imgs = $mysqli->query($sqlImgs);
      ?>
      <?php if ($imgs && $imgs->num_rows): ?>
        <div class="imagenes-actividad">
          <?php while ($img = $imgs->fetch_assoc()): ?>
            <img src="<?php echo $img['ruta']; ?>" alt="Foto actividad">
          <?php endwhile; ?>
        </div>
        <?php $imgs->close(); ?>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>

  <a href="ver_perfilAmigo.php?id=<?php echo $user_id; ?>" class="paginacion">⬅ Volver al perfil</a>
</body>
</html>
