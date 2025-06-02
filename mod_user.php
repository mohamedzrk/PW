<?php
// mod_user.php
include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// Obtener el ID del usuario a modificar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('ID de usuario inválido');
}

// 3) Si se envió el formulario, procesar la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = $_POST['nombre'];
    $apellidos   = $_POST['apellidos'];
    $fecha_nac   = $_POST['fecha_nacimiento'];
    $pais_id     = (int)$_POST['pais'];
    $provincia_id= (int)$_POST['provincia'];
    $localidad_id= (int)$_POST['localidad'];
    $tipo_act_id = (int)$_POST['tipoActividad'];

    $sql = "
      UPDATE usuario
      SET
        nombre            = '$nombre',
        apellidos         = '$apellidos',
        fecha_nacimiento  = '$fecha_nac',
        pais_id           = $pais_id,
        provincia_id      = $provincia_id,
        localidad_id      = $localidad_id,
        tipo_actividad_id = $tipo_act_id
      WHERE id = $id
    ";
    $mysqli->query($sql) or die("Error al actualizar: " . $mysqli->error);

    header('Location: g_usuarios.php');
    exit;
}

// 4) Leer los datos actuales del usuario
$res = $mysqli->query("SELECT nombre, apellidos, fecha_nacimiento, pais_id, provincia_id, localidad_id, tipo_actividad_id FROM usuario WHERE id = $id");
if ($res->num_rows === 0) {
    die('Usuario no encontrado');
}
$user = $res->fetch_assoc();

// 5) Listas para selects
$paises      = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
$provincias  = $mysqli->query("SELECT id, nombre FROM provincias ORDER BY nombre");
$localidades = $mysqli->query("SELECT id, nombre FROM localidades ORDER BY nombre");
$actividades = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");

include 'header_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Modificar Usuario</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-admin">
  <div class="contenedor-registro">
    <h1>Modificar Usuario</h1>
    <form method="post" action="mod_user.php?id=<?=$id?>">
      <div class="campo">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?=$user['nombre']?>" required>
      </div>
      <div class="campo">
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="<?=$user['apellidos']?>" required>
      </div>
      <div class="campo">
        <label>Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" 
               value="<?=$user['fecha_nacimiento']?>" required>
      </div>
      <div class="campo">
        <label>País</label>
        <select name="pais" required>
          <?php while ($p = $paises->fetch_assoc()): ?>
            <option value="<?=$p['id']?>" <?= $p['id']==$user['pais_id'] ? 'selected' : '' ?>>
              <?=$p['nombre']?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="campo">
        <label>Provincia</label>
        <select name="provincia" required>
          <?php while ($pr = $provincias->fetch_assoc()): ?>
            <option value="<?=$pr['id']?>" <?= $pr['id']==$user['provincia_id'] ? 'selected' : '' ?>>
              <?=$pr['nombre']?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="campo">
        <label>Localidad</label>
        <select name="localidad" required>
          <?php while ($l = $localidades->fetch_assoc()): ?>
            <option value="<?=$l['id']?>" <?= $l['id']==$user['localidad_id'] ? 'selected' : '' ?>>
              <?=$l['nombre']?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="campo">
        <label>Actividad favorita</label>
        <select name="tipoActividad" required>
          <?php while ($t = $actividades->fetch_assoc()): ?>
            <option value="<?=$t['id']?>" <?= $t['id']==$user['tipo_actividad_id'] ? 'selected' : '' ?>>
              <?=$t['nombre']?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <button class="btn" type="submit">Guardar cambios</button>
      <a href="g_usuarios.php" class="btn">Cancelar</a>
    </form>
  </div>
</body>
</html>
