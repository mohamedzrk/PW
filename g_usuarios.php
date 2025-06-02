<?php
// g_usuarios.php
include 'db.php';


// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 1) Borrar usuario si viene por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $mysqli->query("DELETE FROM usuario WHERE id = $id");
    header('Location: g_usuarios.php');
    exit;
}

// 2) Leer parámetros de búsqueda y paginación
$q     = isset($_GET['q'])    ? $_GET['q']            : '';
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$off   = ($page - 1) * $limit;

// 3) Cláusula WHERE para búsqueda
$where = $q !== ''
    ? "WHERE nombre LIKE '%$q%' OR apellidos LIKE '%$q%'"
    : "";

// 4) Contar total de usuarios
$totalRes = $mysqli->query("SELECT COUNT(*) AS c FROM usuario $where");
$total    = $totalRes->fetch_assoc()['c'];

// 5) Traer usuarios de esta página
$sql = "
  SELECT id, nombre, apellidos, foto
  FROM usuario
  $where
  ORDER BY nombre, apellidos
  LIMIT $limit OFFSET $off
";
$res = $mysqli->query($sql);

// 6) Mostrar página con header admin
include 'header_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Usuarios</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-admin">

  <h1>Usuarios</h1>

  <!-- Buscador -->
  <form method="get" action="g_usuarios.php" class="buscador">
    <input type="text" name="q" placeholder="Buscar usuario" value="<?=$q?>">
    <button class="paginacion">Buscar</button>
  </form>

  <!-- Lista de usuarios -->
  <?php if ($res->num_rows == 0): ?>
    <p>No se encontraron usuarios.</p>
  <?php else: ?>
    <?php while ($u = $res->fetch_assoc()): ?>
      <div class="g_usuario">
        <?php $foto = $u['foto'] ?: 'uploads/default.png'; ?>
        <img src="<?=$foto?>" class="avatar">
        <div>
          <h3><?=$u['nombre']?> <?=$u['apellidos']?></h3>
        </div>
        <a href="ver_perfilAmigo.php?id=<?=$u['id']?>" class="paginacion">Ver Perfil</a>
        <a href="mod_user.php?id=<?=$u['id']?>" class="paginacion">Modificar</a>
        <form method="post" style="display:inline">
          <input type="hidden" name="delete_id" value="<?=$u['id']?>">
          <button class="logout">Eliminar</button>
        </form>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="g_usuarios.php?q=<?=$q?>&page=<?=$page-1?>">Anterior</a>
    <?php endif; ?>
    <?php if ($page * $limit < $total): ?>
      <a href="g_usuarios.php?q=<?=$q?>&page=<?=$page+1?>">Siguiente</a>
    <?php endif; ?>
  </div>

</body>
</html>
