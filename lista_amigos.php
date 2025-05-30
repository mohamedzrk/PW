<?php
// lista_amigos.php
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

// 2) Sólo amigos (o tú mismo)
$stmt = $mysqli->prepare("
  SELECT 1 FROM amistad a
   WHERE (a.usuario_id=? AND a.amigo_id=?)
      OR (a.usuario_id=? AND a.amigo_id=?)
");
$stmt->bind_param('iiii', $me, $user_id, $user_id, $me);
$stmt->execute();
$es_amigo = (bool)$stmt->get_result()->fetch_assoc();
$stmt->close();


// 3) Traer amigos
$sql = "
  SELECT u.id, u.nombre, u.apellidos, u.foto
  FROM amistad a
  JOIN usuario u 
    ON (a.usuario_id = u.id AND a.amigo_id = ?)
    OR (a.amigo_id = u.id  AND a.usuario_id = ?)
  WHERE a.estado = 'aceptado'
  ORDER BY u.nombre, u.apellidos
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$friends = $stmt->get_result();
$stmt->close();

include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Amigos de Usuario</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">


  <?php if ($friends->num_rows === 0): ?>
    <p>Este usuario no tiene amigos todavía.</p>
  <?php endif; ?>

  <?php while ($f = $friends->fetch_assoc()): ?>
    <div class="g_usuario">
      <?php $foto = $f['foto'] ?: 'uploads/default.png'; ?>
      <img src="<?= htmlspecialchars($foto) ?>" class="avatar">
      <h3><?= htmlspecialchars("{$f['nombre']} {$f['apellidos']}") ?></h3>
      <a href="ver_perfilAmigo.php?id=<?= $f['id'] ?>" class="btn">Ver Perfil</a>
    </div>
  <?php endwhile; ?>

  
  <a href="ver_perfilAmigo.php?id=<?= $user_id ?>" class="paginacion">⬅ Volver al perfil</a>
</body>
</html>
