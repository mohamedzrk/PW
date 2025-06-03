<?php
// tipoActividad.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 1) Borrar tipo de actividad si viene por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $mysqli->query("DELETE FROM tipo_actividad WHERE id = $id");
    header('Location: tipoActividad.php');
    exit;
}

// 2) Parámetros de búsqueda y paginación
$q     = isset($_GET['q'])    ? $_GET['q']            : '';
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$off   = ($page - 1) * $limit;

// 3) Cláusula WHERE para búsqueda
$where = $q !== ''
    ? "WHERE nombre LIKE '%$q%'"
    : "";

// 4) Contar total de tipos de actividad
$totalRes = $mysqli->query("SELECT COUNT(*) AS c FROM tipo_actividad $where");
$total    = $totalRes->fetch_assoc()['c'];

// 5) Traer tipos de actividad de esta página
$sql = "
  SELECT id, nombre
  FROM tipo_actividad
  $where
  ORDER BY nombre
  LIMIT $limit OFFSET $off
";
$res = $mysqli->query($sql);

// 6) Mostrar con header admin
include 'header_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Tipos de Actividad</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-admin">

  <h1>Tipos de Actividad</h1>

  <!-- Buscador -->
  <form method="get" action="tipoActividad.php" class="buscador">
    <input type="text" name="q" placeholder="Buscar tipo de actividad" value="<?=$q?>">
    <button class="paginacion">Buscar</button>
  </form>

  <!-- Lista de tipos de actividad -->
  <?php if ($res->num_rows == 0): ?>
    <p>No hay tipos de actividad para mostrar.</p>
  <?php else: ?>
    <?php while ($row = $res->fetch_assoc()): ?>
      <div class="tipoItem">
        <?=$row['nombre']?>

        <!-- Eliminar -->
        <form method="post">
          <input type="hidden" name="delete_id" value="<?=$row['id']?>">
          <button class="logout">Eliminar</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="tipoActividad.php?q=<?=$q?>&page=<?=$page-1?>">Anterior</a>
    <?php endif; ?>
    <?php if ($page * $limit < $total): ?>
      <a href="tipoActividad.php?q=<?=$q?>&page=<?=$page+1?>">Siguiente</a>
    <?php endif; ?>
  </div>

</body>
</html>
