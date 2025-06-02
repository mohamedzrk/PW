<?php
// tipoActividad.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 1) Eliminar un tipo de actividad (si viene por POST)
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $mysqli->query("DELETE FROM tipo_actividad WHERE id = $id");
    header('Location: tipoActividad.php');
    exit;
}

// 2) Leer parámetros de búsqueda y paginación
$q     = isset($_GET['q'])    ? $_GET['q']    : '';
$page  = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$limit = 5;
$off   = ($page - 1) * $limit;

// 3) Preparar cláusula WHERE
$where = '';
if ($q !== '') {
    $esc = $mysqli->real_escape_string($q);
    $where = "WHERE nombre LIKE '%$esc%'";
}

// 4) Contar total de resultados
$count = $mysqli->query("SELECT COUNT(*) AS c FROM tipo_actividad $where")
                ->fetch_assoc()['c'];

// 5) Traer página actual
$sql = "
  SELECT id, nombre
  FROM tipo_actividad
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
  <meta charset="UTF-8">
  <title>Tipos de Actividad</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-admin">

  <h1>Gestión de Tipos de Actividad</h1>

  <!-- Buscador -->
  <form method="get" action="tipoActividad.php" class="buscador">
    <input type="text" name="q" placeholder="Buscar..." value="<?php echo $q ?>">
    <button class="paginacion">Buscar</button>
  </form>

  <!-- Lista de tipos -->
  <?php if ($res->num_rows === 0): ?>
    <p>No se encontraron tipos.</p>
  <?php endif ?>

  <?php while ($row = $res->fetch_assoc()): ?>
    <div class="tipoItem">
      <?php echo $row['nombre'] ?>

      <!-- Eliminar -->
      <form method="post">
        <input type="hidden" name="delete_id" value="<?php echo $row['id'] ?>">
        <button class="logout">Eliminar</button>
      </form>
    </div>
  <?php endwhile ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="tipoActividad.php?q=<?php echo urlencode($q) ?>&page=<?php echo $page-1 ?>">Anterior</a>
    <?php endif ?>
    <?php if ($page * $limit < $count): ?>
      <a href="tipoActividad.php?q=<?php echo urlencode($q) ?>&page=<?php echo $page+1 ?>">Siguiente</a>
    <?php endif ?>
  </div>

</body>
</html>
