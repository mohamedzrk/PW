<?php
// ver_perfilAmigo.php
include 'db.php';

// 1) Si no hay sesión, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}
$me        = (int)$_SESSION['usuario_id'];
$friend_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($friend_id <= 0) {
    die('Perfil inválido');
}

// 2) Cargar datos básicos del usuario amigo usando query en lugar de prepare
$sqlUser = "
    SELECT 
      nombre,
      apellidos,
      email,
      fecha_nacimiento,
      foto,
      (SELECT nombre FROM paises WHERE id = u.pais_id) AS pais,
      (SELECT nombre FROM provincias WHERE id = u.provincia_id) AS provincia,
      (SELECT nombre FROM localidades WHERE id = u.localidad_id) AS localidad,
      (SELECT nombre FROM tipo_actividad WHERE id = u.tipo_actividad_id) AS tipo_actividad
    FROM usuario u
    WHERE u.id = $friend_id
";
$resUser = $mysqli->query($sqlUser);
if (!$resUser || $resUser->num_rows === 0) {
    die('Usuario no encontrado');
}
$user = $resUser->fetch_assoc();

// 3) Traer hasta 8 imágenes recientes de sus actividades con query
$sqlImgs = "
    SELECT i.ruta
    FROM imagenes i
    JOIN actividad a ON i.actividad_id = a.id
    WHERE a.usuario_id = $friend_id
    ORDER BY i.uploaded_at DESC
    LIMIT 8
";
$resImgs = $mysqli->query($sqlImgs);
$imgs = [];
if ($resImgs) {
    while ($row = $resImgs->fetch_assoc()) {
        $imgs[] = $row['ruta'];
    }
}
include 'header.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil de <?php echo $user['nombre'] ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">
  <div class="perfil">
    <h1>Perfil de Usuario</h1>

    <!-- Foto de perfil -->
    <img 
      src="<?php echo ($user['foto'] ? $user['foto'] : 'uploads/default.png') ?>" 
      class="imagenes-actividad" ?>

    <!-- Datos básicos -->
    <p>Email: <?php echo $user['email'] ?></p>
    <p>País: <?php echo $user['pais'] ?></p>
    <p>Provincia: <?php echo $user['provincia'] ?></p>
    <p>Localidad: <?php echo $user['localidad'] ?></p>
    <p>Nombre: <?php echo $user['nombre'] ?></p>
    <p>Apellidos: <?php echo $user['apellidos'] ?></p>
    <p>Actividad favorita: <?php echo $user['tipo_actividad'] ?></p>
    <p>Fecha de nacimiento: <?php echo $user['fecha_nacimiento'] ?></p>

    <!-- Enlaces a otras secciones -->
    <a href="actividades_usuario.php?id=<?php echo $friend_id ?>" class="btn">
      Ver Actividades
    </a>
    <a href="lista_amigos.php?id=<?php echo $friend_id ?>" class="btn">
      Ver Amigos
    </a>

    <!-- Galería de imágenes -->
    <?php if (!empty($imgs)): ?>
      <h2>Imágenes Recientes</h2>
      <div class="imagenes-actividad">
        <?php foreach ($imgs as $ruta): ?>
          <img 
            src="<?php echo $ruta ?>" 
          >
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
