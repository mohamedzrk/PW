<?php
// paises.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}


// 1) Borrar país
if (isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $mysqli->query("DELETE FROM paises WHERE id = $id");
    header('Location: paises.php');
    exit;
}

// 2) Añadir país
if (isset($_POST['add_name'])) {
    $name = $_POST['add_name'];
    $mysqli->query("INSERT INTO paises (nombre) VALUES ('$name')");
    header('Location: paises.php');
    exit;
}

// 3) Búsqueda y paginación
$q     = isset($_GET['q'])    ? $_GET['q']    : '';
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit  = 5;
$offset = ($page - 1) * $limit;

// 4) Preparar filtro
$where = $q !== '' ? "WHERE nombre LIKE '%$q%'" : '';

// 5) Contar total
$totalRes = $mysqli->query("SELECT COUNT(*) AS c FROM paises $where");
$total    = $totalRes->fetch_assoc()['c'];

// 6) Traer esta página
$res = $mysqli->query("
  SELECT id, nombre
  FROM paises
  $where
  ORDER BY nombre
  LIMIT $limit OFFSET $offset
");
?>
<?php include 'header_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Países</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-admin">

  <h1>Gestión de Países</h1>

  <!-- Buscador -->
  <form method="get" action="paises.php" class="buscador">
    <input type="text" name="q" placeholder="Buscar país" value="<?php echo $q ?>">
    <button class="paginacion">Buscar</button>
  </form>

  <!-- Lista -->
  <?php while ($row = $res->fetch_assoc()): ?>
    <div class="tipoItem">
      <span><?php echo $row['nombre'] ?></span>

      <!-- Eliminar -->
      <form method="post" >
        <input type="hidden" name="delete_id" value="<?php echo $row['id'] ?>">
        <button class="logout">Eliminar</button>
      </form>

    </div>
  <?php endwhile; ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="paises.php?q=<?php echo $q ?>&page=<?php echo $page - 1 ?>">Anterior</a>
    <?php endif ?>
    <?php if ($page * $limit < $total): ?>
      <a href="paises.php?q=<?php echo $q ?>&page=<?php echo $page + 1 ?>">Siguiente</a>
    <?php endif ?>
  </div>

</body>
</html>
