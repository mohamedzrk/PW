<?php
// ver_perfilAmigo.php
include 'db.php';
session_start();

// 1) Comprobar login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}
$me        = (int)$_SESSION['usuario_id'];
$friend_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($friend_id <= 0) {
    die('Perfil inválido');
}

// 2) Gestionar amistad (si viene POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];  // 'add' o 'remove'
    if ($accion === 'add') {
        $mysqli->query("
            INSERT INTO amistad (usuario_id, amigo_id, estado)
            VALUES ($me, $friend_id, 'aceptado')
            ON DUPLICATE KEY UPDATE estado = 'aceptado'
        ");
    } else {
        $mysqli->query("
            DELETE FROM amistad
            WHERE (usuario_id = $me AND amigo_id = $friend_id)
               OR (usuario_id = $friend_id AND amigo_id = $me)
        ");
    }
    header("Location: ver_perfilAmigo.php?id=$friend_id");
    exit;
}

// 3) Cargar datos del usuario amigo
$stmt = $mysqli->prepare("
    SELECT 
      u.nombre, u.apellidos, u.email, u.fecha_nacimiento,
      u.foto,
      p.nombre AS pais,
      pr.nombre AS provincia,
      l.nombre AS localidad,
      ta.nombre AS tipo_actividad
    FROM usuario u
    JOIN paises p            ON u.pais_id = p.id
    JOIN provincias pr       ON u.provincia_id = pr.id
    JOIN localidades l       ON u.localidad_id = l.id
    JOIN tipo_actividad ta   ON u.tipo_actividad_id = ta.id
    WHERE u.id = ?
");
$stmt->bind_param('i', $friend_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    die('Usuario no encontrado');
}

// 4) Saber si ya son amigos
$stmt = $mysqli->prepare("
    SELECT 1 FROM amistad a
    WHERE (a.usuario_id = ? AND a.amigo_id = ?)
       OR (a.usuario_id = ? AND a.amigo_id = ?)
");
$stmt->bind_param('iiii', $me, $friend_id, $friend_id, $me);
$stmt->execute();
$is_friend = (bool)$stmt->get_result()->fetch_assoc();
$stmt->close();

// 5) Traer últimas imágenes de sus actividades
$stmt = $mysqli->prepare("
    SELECT i.ruta
    FROM imagenes i
    JOIN actividad a ON i.actividad_id = a.id
    WHERE a.usuario_id = ?
    ORDER BY i.uploaded_at DESC
    LIMIT 8
");
$stmt->bind_param('i', $friend_id);
$stmt->execute();
$imgs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil de <?= htmlspecialchars($user['nombre']) ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">
  <div class="perfil">
    <h1>Perfil de Usuario</h1>

    <!-- Foto -->
    <img 
      src="<?= htmlspecialchars($user['foto'] ?: 'uploads/default.png') ?>" 
      class="avatar-large" 
      alt="Foto de <?= htmlspecialchars($user['nombre']) ?>"
    >

    <!-- Botón enviar/anular amistad -->
    <form method="post">
      <button 
        type="submit" 
        name="accion" 
        value="<?= $is_friend ? 'remove' : 'add' ?>" 
        class="<?= $is_friend ? 'btn logout' : 'btn' ?>"
      >
        <?= $is_friend ? 'Anular Amistad' : 'Envíar Solicitud' ?>
      </button>
    </form>

    <!-- Datos -->
    <h3>Email: <?= htmlspecialchars($user['email']) ?></h3>
    <p>País: <?= htmlspecialchars($user['pais']) ?></p>
    <p>Provincia: <?= htmlspecialchars($user['provincia']) ?></p>
    <p>Localidad: <?= htmlspecialchars($user['localidad']) ?></p>
    <p>Nombre: <?= htmlspecialchars($user['nombre']) ?></p>
    <p>Apellidos: <?= htmlspecialchars($user['apellidos']) ?></p>
    <p>Actividad favorita: <?= htmlspecialchars($user['tipo_actividad']) ?></p>
    <p>Fecha de nacimiento: <?= htmlspecialchars($user['fecha_nacimiento']) ?></p>

    <!-- Enlaces a más secciones -->
    <a href="actividades_usuario.php?id=<?= $friend_id ?>" class="btn">
      Ver Actividades
    </a>
    <a href="lista_amigos.php?id=<?= $friend_id ?>" class="btn">
      Ver Amigos
    </a>

    <!-- Galería de imágenes -->
    <?php if ($imgs): ?>
      <h2>Imágenes Recientes</h2>
      <div class="imagenes-actividad">
        <?php foreach ($imgs as $img): ?>
          <img 
            src="<?= htmlspecialchars($img['ruta']) ?>" 
            class="galeria-img" 
            alt="Foto actividad"
          >
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
