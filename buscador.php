<?php
// buscador.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

$me = (int) $_SESSION['usuario_id'];

// 2) Procesar añadir/quitar amistad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // añadir amigo
    if (isset($_POST['add_friend'])) {
        $aid = (int)$_POST['add_friend'];
        $mysqli->query(
          "INSERT INTO amistad (usuario_id, amigo_id, estado)
           VALUES ($me, $aid, 'aceptado')
           ON DUPLICATE KEY UPDATE estado='aceptado'"
        );
    }
    // quitar amigo
    if (isset($_POST['remove_friend'])) {
        $rid = (int)$_POST['remove_friend'];
        $mysqli->query(
          "DELETE FROM amistad
           WHERE (usuario_id=$me AND amigo_id=$rid)
              OR (usuario_id=$rid AND amigo_id=$me)"
        );
    }
    // recargar misma búsqueda
    $q    = $_POST['q']    ?? '';
    $page = (int)($_POST['page'] ?? 1);
    header("Location: buscador.php?q=$q&page=$page");
    exit;
}

// 3) Leer parámetros de búsqueda y paginación
$q     = $_GET['q']    ?? '';
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$off   = ($page - 1) * $limit;

// 4) Contar resultados totales
$total = 0;
if ($q !== '') {
    $res  = $mysqli->query(
      "SELECT COUNT(*) AS c
       FROM usuario
       WHERE (nombre LIKE '%$q%' OR apellidos LIKE '%$q%')
         AND id <> $me"
    );
    $total = $res->fetch_assoc()['c'];
}

// 5) Traer página de resultados junto con estado de amistad
$usuarios = [];
if ($q !== '') {
    $sql = "
      SELECT 
        u.id, u.nombre, u.apellidos, u.foto,
        (f.id IS NOT NULL) AS es_amigo
      FROM usuario u
      LEFT JOIN amistad f
        ON (f.usuario_id = $me AND f.amigo_id = u.id)
        OR (f.usuario_id = u.id AND f.amigo_id = $me)
      WHERE (u.nombre LIKE '%$q%' OR u.apellidos LIKE '%$q%')
        AND u.id <> $me
      ORDER BY u.nombre, u.apellidos
      LIMIT $limit OFFSET $off
    ";
    $r = $mysqli->query($sql);
    while ($row = $r->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

// 6) Mostrar resultados
include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Buscador de Usuarios</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">

  <!-- Formulario de búsqueda -->
  <div class="buscador">
    <form method="get" action="buscador.php">
      <input
        type="text"
        name="q"
        placeholder="Buscar usuario"
        value="<?=$q?>"
      >
      <button class="paginacion">Buscar</button>
    </form>
  </div>

  <!-- Mensaje si no hay resultados -->
  <?php if ($q !== '' && empty($usuarios)): ?>
    <p>No se encontraron usuarios para "<?=$q?>".</p>
  <?php endif; ?>

  <!-- Listado de usuarios -->
  <?php foreach ($usuarios as $u): ?>
    <div class="<?=$u['es_amigo']?'usuarioAmigo':'usuarioNoAmigo'?>">
      <?php $foto = $u['foto'] ?: 'uploads/default.png'; ?>
      <img src="<?=$foto?>" class="avatar">
      <h3><?=$u['nombre']?> <?=$u['apellidos']?></h3>

      <!-- Si es amigo, mostrar Ver Perfil y Anular -->
      <?php if ($u['es_amigo']): ?>
        <a href="ver_perfilAmigo.php?id=<?=$u['id']?>" class="btn">Ver Perfil</a>
        <form method="post" >
          
          <input type="hidden" name="remove_friend" 
          value="<?=$u['id']?>">

          <input type="hidden" name="q"             
          value="<?=$q?>">

          <input type="hidden" name="page"          
          value="<?=$page?>">
          <button class="btn logout" type="submit">Anular Amistad</button>
        </form>

      <!-- Si no, mostrar Enviar Solicitud -->
      <?php else: ?>
        <form method="post" >
          <input type="hidden" name="add_friend" 
          value="<?=$u['id']?>">

          <input type="hidden" name="q"
          value="<?=$q?>">

          <input type="hidden" name="page"       
          value="<?=$page?>">

          <button class="btn" type="submit">Enviar Solicitud</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <!-- Paginación -->
  <div class="paginacion">
    <?php if ($page > 1): ?>
      <a href="buscador.php?q=<?=$q?>&page=<?=$page-1?>" class="paginacion">Anterior</a>
    <?php endif; ?>
    <?php if ($page * $limit < $total): ?>
      <a href="buscador.php?q=<?=$q?>&page=<?=$page+1?>" class="paginacion">Siguiente</a>
    <?php endif; ?>
  </div>

</body>
</html>
