<?php
include 'db.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

$userId = (int)$_SESSION['usuario_id'];

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = $mysqli->real_escape_string(trim($_POST['nombre']));
    $apellidos = $mysqli->real_escape_string(trim($_POST['apellidos']));
    $fechaNac  = $_POST['fechaNacimiento'];
    $paisId    = (int)$_POST['pais'];
    $provId    = (int)$_POST['provincia'];
    $locId     = (int)$_POST['localidad'];
    $tipoActId = (int)$_POST['tipoActividad'];

    // Subida de foto
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext     = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $dir     = __DIR__ . '/uploads/profile';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file    = "$dir/{$userId}.{$ext}";
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $file)) {
            $rutaFoto = "uploads/profile/{$userId}.{$ext}";
            $mysqli->query("UPDATE usuario SET foto='{$rutaFoto}' WHERE id={$userId}");
        }
    }

    // Actualizar datos
    $sql = "
      UPDATE usuario SET
        nombre='$nombre',
        apellidos='$apellidos',
        tipo_actividad_id=$tipoActId,
        fecha_nacimiento='$fechaNac',
        pais_id=$paisId,
        provincia_id=$provId,
        localidad_id=$locId
      WHERE id=$userId
    ";
    if (!$mysqli->query($sql)) {
        die('Error al actualizar perfil: ' . $mysqli->error);
    }
    header('Location: g_perfil.php');
    exit;
}

// Cargar datos actuales
$res       = $mysqli->query("SELECT * FROM usuario WHERE id={$userId}");
$user      = $res->fetch_assoc();
$foto      = $user['foto'] ?: 'img/default.png';

// Cargar listas **sin filtrar**
$paises      = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
$provincias  = $mysqli->query("SELECT id, nombre FROM provincias ORDER BY nombre");
$localidades = $mysqli->query("SELECT id, nombre FROM localidades ORDER BY nombre");
$actividades = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Perfil</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">
  <div class="contenedor-registro">
    <h1>Gestión de Perfil</h1>
    <form method="post" enctype="multipart/form-data">

      <div class="campo">
        <label>Foto actual:</label><br>
        <img src="<?=htmlspecialchars($foto)?>" alt="Perfil" style="max-width:120px;"><br>
        <input type="file" name="foto" accept="image/*">
      </div>

      <div class="campo">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?=htmlspecialchars($user['nombre'])?>" required>
      </div>

      <div class="campo">
        <label>Apellidos:</label>
        <input type="text" name="apellidos" value="<?=htmlspecialchars($user['apellidos'])?>" required>
      </div>

      <div class="campo">
        <label>Tipo de Actividad:</label>
        <select name="tipoActividad" required>
          <?php while($a = $actividades->fetch_assoc()): ?>
            <option value="<?=$a['id']?>" <?=$a['id']==$user['tipo_actividad_id']?'selected':''?>>
              <?=htmlspecialchars($a['nombre'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="campo">
        <label>Fecha de Nacimiento:</label>
        <input type="date" name="fechaNacimiento" value="<?=$user['fecha_nacimiento']?>" required>
      </div>

      <div class="campo">
        <label>País:</label>
        <select name="pais" required>
          <?php while($p = $paises->fetch_assoc()): ?>
            <option value="<?=$p['id']?>" <?=$p['id']==$user['pais_id']?'selected':''?>>
              <?=htmlspecialchars($p['nombre'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="campo">
        <label>Provincia:</label>
        <select name="provincia" required>
          <?php while($p = $provincias->fetch_assoc()): ?>
            <option value="<?=$p['id']?>" <?=$p['id']==$user['provincia_id']?'selected':''?>>
              <?=htmlspecialchars($p['nombre'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="campo">
        <label>Localidad:</label>
        <select name="localidad" required>
          <?php while($l = $localidades->fetch_assoc()): ?>
            <option value="<?=$l['id']?>" <?=$l['id']==$user['localidad_id']?'selected':''?>>
              <?=htmlspecialchars($l['nombre'])?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <button class="btn" type="submit">Guardar Cambios</button>
      <a href="ini.php" class="btn">Cancelar</a>
    </form>
  </div>
</body>
</html>
