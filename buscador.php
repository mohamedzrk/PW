<?php
include 'db.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

$me = (int) $_SESSION['usuario_id'];  // Obtener el ID del usuario actual
$q = isset($_GET['q']) ? trim($_GET['q']) : '';  // Obtener el término de búsqueda

$usuarios = [];  // Array para almacenar los usuarios encontrados

// Si hay un término de búsqueda
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

    $res = $mysqli->query($sql);  // Ejecutar la consulta
    while ($row = $res->fetch_assoc()) {
        $usuarios[] = $row;  // Añadir el usuario encontrado al array
    }
}
?>
<?php include 'header.php'; ?> <!-- Incluir el encabezado -->

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
      <input type="text" name="q" placeholder="Buscar usuario" value="<?= $q ?>">
      <button class="paginacion">Buscar</button>
    </form>
  </div>

  <!-- Mostrar mensaje si no se encuentran usuarios -->
  <?php if ($q !== '' && empty($usuarios)): ?>
    <p>No se encontraron usuarios para "<?= $q ?>".</p>
  <?php endif; ?>

  <!-- Mostrar los usuarios encontrados -->
  <?php foreach ($usuarios as $u): ?>
    <div class="<?= $u['es_amigo'] ? 'usuarioAmigo' : 'usuarioNoAmigo' ?>">
      <!-- Foto de perfil, si no tiene foto, usar predeterminada.webp -->
      <?php 
      $foto = $u['foto'] ?: 'C:\xampp\htdocs\PracticaWeb\uploads\default.png';  // Si no tiene foto, usa la predeterminada
      ?>

      <img src="<?= $foto ?>" alt="C:\xampp\htdocs\PracticaWeb\uploads\default.png" class="avatar">
      <h3><?= $u['nombre'].' '.$u['apellidos'] ?></h3>

      <!-- Enlace para ver el perfil del amigo -->
      <a href="ver_perfilAmigo.php?id=<?= $u['id'] ?>" class="btn">Ver Perfil</a>

      <!-- Opciones para gestionar amistad (dummies por ahora) -->
      <a href="#" class="btn">Enviar Solicitud</a>
      <a href="#" class="btn logout">Anular Amistad</a>
    </div>
  <?php endforeach; ?>
</body>
</html>
