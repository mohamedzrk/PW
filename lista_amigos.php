<?php
// lista_amigos.php
include 'db.php';

// 1) Comprobar que hay sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}
$me      = (int)$_SESSION['usuario_id'];
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    die('Usuario inválido');
}

// 2) Procesar añadir/quitar amistad si llega POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // añadir amigo
    if (isset($_POST['add_friend'])) {
        $aid = (int)$_POST['add_friend'];
        $mysqli->query("
            INSERT INTO amistad (usuario_id, amigo_id, estado)
            VALUES ($me, $aid, 'aceptado')
            ON DUPLICATE KEY UPDATE estado='aceptado'
        ");
    }
    // quitar amigo
    if (isset($_POST['remove_friend'])) {
        $rid = (int)$_POST['remove_friend'];
        $mysqli->query("
            DELETE FROM amistad
            WHERE (usuario_id = $me AND amigo_id = $rid)
               OR (usuario_id = $rid AND amigo_id = $me)
        ");
    }
    // recargar la misma página de lista de amigos
    header("Location: lista_amigos.php?id=$user_id");
    exit;
}

// 3) Traer todos los amigos del usuario cuyo perfil estamos viendo
$sql = "
  SELECT u.id, u.nombre, u.apellidos, u.foto
    FROM amistad a
    JOIN usuario u
      ON (a.usuario_id = u.id AND a.amigo_id = $user_id)
      OR (a.amigo_id  = u.id AND a.usuario_id = $user_id)
   WHERE a.estado = 'aceptado'
   ORDER BY u.nombre, u.apellidos
";
$friends = $mysqli->query($sql);

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

  <?php if (!$friends || $friends->num_rows === 0): ?>
    <p>Este usuario no tiene amigos todavía.</p>
  <?php endif; ?>

  <?php while ($f = $friends->fetch_assoc()): ?>
    <div class="g_usuario">
      <?php 
        $foto = $f['foto'] ?: 'uploads/default.png'; 
      ?>
      <img src="<?php echo $foto ?>" >
      <h3><?php echo $f['nombre'] . ' ' . $f['apellidos'] ?></h3>

      <?php
        // Comprobar si ese amigo ($f['id']) ya es amigo del usuario logueado ($me)
        $f_id = (int)$f['id'];
        $resRel = $mysqli->query("
          SELECT COUNT(*) AS c
          FROM amistad
          WHERE (usuario_id = $me AND amigo_id = $f_id)
             OR (usuario_id = $f_id AND amigo_id = $me)
        ");
        $rowRel = $resRel->fetch_assoc();
        $es_amigo_mio = ($rowRel['c'] > 0);
      ?>

      <?php if ($es_amigo_mio): ?>
        <!-- Si ya es amigo mío, mostrar Ver Perfil y Anular Amistad -->
        <a href="ver_perfilAmigo.php?id=<?php echo $f_id ?>" class="btn">Ver Perfil</a>
        <form method="post" >
          <input type="hidden" name="remove_friend" value="<?php echo $f_id ?>">
          <button class="btn" type="submit">Anular Amistad</button>
        </form>
      <?php else: ?>
        <!-- Si no es amigo mío, mostrar Enviar Solicitud -->
        <form method="post">
          <input type="hidden" name="add_friend" value="<?php echo $f_id ?>">
          <button class="btn" type="submit">Enviar Solicitud</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>

  <a href="ver_perfilAmigo.php?id=<?php echo $user_id ?>" class="paginacion">
    Volver al perfil
  </a>
</body>
</html>
