<?php
// provincias.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 1) Borrar provincia (si viene por POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $mysqli->query("DELETE FROM provincias WHERE id = $id");
    header('Location: provincias.php');
    exit;
}

// 2) Parámetros de búsqueda y paginación
$q     = isset($_GET['q'])    ? $_GET['q']            : '';
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$off   = ($page - 1) * $limit;

// 3) Generar cláusula WHERE
$where = $q !== ''
    ? "WHERE nombre LIKE '%$q%'"
    : "";

// 4) Contar total
$totalRes = $mysqli->query("SELECT COUNT(*) AS c FROM provincias $where");
$total    = $totalRes->fetch_assoc()['c'];

// 5) Traer resultados de esta página
$sql = "
  SELECT id, nombre
  FROM provincias
  $where
  ORDER BY nombre
  LIMIT $limit OFFSET $off
";
$res = $mysqli->query($sql);

// 6) Mostrar
include 'header_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Provincias</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-admin">

  <h1>Provincias</h1>

  <!-- Buscador -->
  <form method="get" action="provincias.php" class="buscador">
    <input type="text" name="q" placeholder="Buscar..." value="<?=$q?>">
    <button class="paginacion">Buscar</button>
  </form>

  <!-- Lista de provincias -->
  <?php if ($res->num_rows == 0): ?>
    <p>No hay provincias para mostrar.</p>
  <?php else: ?>
    <?php while ($row = $res->fetch_assoc()): ?>
      <div class="tipoItem">
        <?=$row['nombre']?>

        <!-- Formulario para eliminar -->
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
      <a href="provincias.php?q=<?=$q?>&page=<?=$page-1?>">Anterior</a>
    <?php endif; ?>
    <?php if ($page * $limit < $total): ?>
      <a href="provincias.php?q=<?=$q?>&page=<?=$page+1?>">Siguiente</a>
    <?php endif; ?>
  </div>

</body>
</html>
