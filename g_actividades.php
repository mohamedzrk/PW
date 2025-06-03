<?php
// g_actividades.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 1) Manejar acciones por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aplaudir
    if (isset($_POST['aplaudir'])) {
        $aid = (int)$_POST['aplaudir'];
        $mysqli->query("UPDATE actividad SET aplausos = aplausos + 1 WHERE id = $aid");
    }
    // Eliminar imágenes de la actividad
    if (isset($_POST['delete_images'])) {
        $aid = (int)$_POST['delete_images'];
        // Opcional: borrar archivos del disco antes de eliminar registros
        $res = $mysqli->query("SELECT ruta FROM imagenes WHERE actividad_id = $aid");
        while ($img = $res->fetch_assoc()) {
            @unlink(__DIR__ . '/' . $img['ruta']);
        }
        $mysqli->query("DELETE FROM imagenes WHERE actividad_id = $aid");
    }
    // Recarga a la misma página
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    header("Location: g_actividades.php?page=$page");
    exit;
}

// 2) Parámetros de paginación
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = 5;
$offset = ($page - 1) * $limit;

// 3) Contar total de actividades
$totalRes = $mysqli->query("SELECT COUNT(*) AS c FROM actividad");
$total    = $totalRes->fetch_assoc()['c'];

// 4) Traer página de actividades
$sql = "
  SELECT 
    a.id, a.titulo, a.fecha, a.aplausos,
    ta.nombre AS tipo
  FROM actividad a
  JOIN tipo_actividad ta ON a.tipo_actividad_id = ta.id
  ORDER BY a.fecha DESC
  LIMIT $limit OFFSET $offset
";
$acts = $mysqli->query($sql);

include 'header_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Tablón de Actividades (admin)</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-admin">

  <h1>Tablón de Actividades (admin)</h1>

  <?php if ($acts->num_rows === 0): ?>
    <p>No hay actividades para mostrar.</p>
  <?php endif; ?>

  <?php while ($act = $acts->fetch_assoc()): ?>
    <div class="actividad">
      <div>
        <h2><?= $act['titulo'] ?></h2>
        <p>Tipo: <?= $act['tipo'] ?></p>
        <p>Fecha: <?= date('d/m/Y H:i', strtotime($act['fecha'])) ?></p>
        <p>Aplausos: <?= $act['aplausos'] ?></p>
      </div>

      <!-- Botones de acción -->
      <div>
        <form method="post">
          <input type="hidden" name="aplaudir" value="<?= $act['id'] ?>">
          <button class="btn" type="submit">Aplaudir</button>
        </form>
        <form method="post">
          <input type="hidden" name="delete_images" value="<?= $act['id'] ?>">
          <button class="logout" type="submit">Eliminar imágenes</button>
        </form>
      </div>

      <!-- Participantes -->
      <?php
        $resC = $mysqli->query("
          SELECT u.nombre, u.apellidos
          FROM compania c
          JOIN usuario u ON c.usuario_id = u.id
          WHERE c.actividad_id = {$act['id']}
          ORDER BY u.nombre
        ");
        $parts = [];
        while ($u = $resC->fetch_assoc()) {
            $parts[] = "{$u['nombre']} {$u['apellidos']}";
        }
      ?>
      <p>Participantes: <?= $parts ? implode(', ', $parts) : '—' ?></p>

      <!-- Imágenes -->
      <?php
        $resI = $mysqli->query("SELECT ruta FROM imagenes WHERE actividad_id = {$act['id']}");
      ?>
      <?php if ($resI->num_rows): ?>
        <div class="imagenes-actividad">
          <?php while ($img = $resI->fetch_assoc()): ?>
            <img src="<?= $img['ruta'] ?>" alt="Foto actividad">
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="g_actividades.php?page=<?= $page - 1 ?>" class="paginacion">Anterior</a>
    <?php endif; ?>
    <?php if ($page * $limit < $total): ?>
      <a href="g_actividades.php?page=<?= $page + 1 ?>" class="paginacion">Siguiente</a>
    <?php endif; ?>
  </div>

</body>
</html>
