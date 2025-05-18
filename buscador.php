<?php
// buscador.php — Búsqueda de usuarios mostrando foto de perfil
include 'db.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

$me = (int) $_SESSION['usuario_id'];
$q  = isset($_GET['q']) ? $mysqli->real_escape_string(trim($_GET['q'])) : '';
$usuarios = [];

if ($q !== '') {
    $sql = "
      SELECT u.id, u.nombre, u.apellidos, u.foto,
        EXISTS(
          SELECT 1 FROM amistad a
          WHERE (a.usuario_id=$me AND a.amigo_id=u.id)
             OR (a.usuario_id=u.id AND a.amigo_id=$me)
        ) AS es_amigo
      FROM usuario u
      WHERE u.nombre LIKE '%$q%' OR u.apellidos LIKE '%$q%'
      ORDER BY u.nombre, u.apellidos
      LIMIT 50
    ";
    $res = $mysqli->query($sql);
    while ($row = $res->fetch_assoc()) {
        $usuarios[] = $row;
    }
}
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Buscador de Usuarios</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">
  <div class="buscador">
    <form method="get" action="buscador.php">
      <input type="text" name="q" placeholder="Buscar usuario"
             value="<?= htmlspecialchars($q) ?>">
      <button class="paginacion">Buscar</button>
    </form>
  </div>

  <?php if ($q !== '' && empty($usuarios)): ?>
    <p>No se encontraron usuarios para “<?= htmlspecialchars($q) ?>”.</p>
  <?php endif; ?>

  <?php foreach ($usuarios as $u): ?>
    <div class="<?= $u['es_amigo'] ? 'usuarioAmigo' : 'usuarioNoAmigo' ?>">
      <img src="<?= htmlspecialchars($u['foto'] ?: 'img/default.png') ?>"
           alt="Avatar" class="avatar">
      <h3><?= htmlspecialchars($u['nombre'].' '.$u['apellidos']) ?></h3>

      <a href="ver_perfilAmigo.php?id=<?= $u['id'] ?>" class="btn">
        Ver Perfil
      </a>

      <?php if (!$u['es_amigo'] && $u['id'] !== $me): ?>
        <a href="amistad.php?action=enviar&id=<?= $u['id'] ?>&q=<?= urlencode($q) ?>"
           class="btn">
          Enviar Solicitud
        </a>
      <?php elseif ($u['es_amigo']): ?>
        <a href="amistad.php?action=anular&id=<?= $u['id'] ?>&q=<?= urlencode($q) ?>"
           class="btn logout">
          Anular Amistad
        </a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</body>
</html>
