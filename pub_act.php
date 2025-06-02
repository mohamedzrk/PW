<?php

include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

// 2) Procesar formulario al hacer POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2.1) Datos básicos
    $titulo        = $_POST['titulo'];
    $tipoActividad = (int) $_POST['tipoActividad'];
    $fecha         = date('Y-m-d H:i:s');

    // 2.2) Subir GPX (único archivo)
    if (isset($_FILES['rutaGPX']) && $_FILES['rutaGPX']['error'] === 0) {
        $ext       = pathinfo($_FILES['rutaGPX']['name'], PATHINFO_EXTENSION);
        $nombreGPX = time() . '.' . $ext;
        if (!is_dir('uploads/gpx')) {
            mkdir('uploads/gpx', 0755, true);
        }
        move_uploaded_file($_FILES['rutaGPX']['tmp_name'], "uploads/gpx/$nombreGPX");
        $rutaGpx = "uploads/gpx/$nombreGPX";
    } else {
        die('Error al subir el archivo GPX');
    }

    // 2.3) Insertar actividad en la tabla 'actividad'
    $mysqli->query("
        INSERT INTO actividad (usuario_id, titulo, tipo_actividad_id, fecha)
        VALUES ($usuario_id, '$titulo', $tipoActividad, '$fecha')
    ") or die("Error al guardar actividad: " . $mysqli->error);
    $actividad_id = $mysqli->insert_id;

    // 2.4) Guardar ruta GPX en la tabla 'rutas'
    $mysqli->query("
        INSERT INTO rutas (actividad_id, archivo)
        VALUES ($actividad_id, '$rutaGpx')
    ") or die("Error al guardar ruta GPX: " . $mysqli->error);

    // 2.5) Asociar compañeros (sólo IDs que vienen del formulario)
    if (!empty($_POST['companeros'])) {
        foreach ($_POST['companeros'] as $cid) {
            $cid = (int) $cid;
            $mysqli->query("
                INSERT INTO compania (actividad_id, usuario_id)
                VALUES ($actividad_id, $cid)
            ") or die("Error al asociar compañero: " . $mysqli->error);
        }
    }

    // 2.6) Subir **múltiples** imágenes
    if (!empty($_FILES['imagenes']['name'][0])) {
        // Crear carpeta si no existe
        if (!is_dir('uploads/imagenes')) {
            mkdir('uploads/imagenes', 0755, true);
        }
        // Recorremos cada índice de $_FILES['imagenes']
        foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp) {
            if ($_FILES['imagenes']['error'][$i] === 0) {
                $extImg  = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
                $imgName = time() . "_{$i}." . $extImg;
                $imgPath = "uploads/imagenes/$imgName";
                move_uploaded_file($tmp, $imgPath);
                $mysqli->query("
                    INSERT INTO imagenes (actividad_id, ruta)
                    VALUES ($actividad_id, '$imgPath')
                ") or die("Error al guardar imagen: " . $mysqli->error);
            }
        }
    }

    // 2.7) Redirigir al tablón
    header('Location: tablón.php');
    exit;
}

// 3) Cargar datos para mostrar en el formulario
//    3.1) Tipos de actividad
$tipos = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");

//    3.2) Sólo cargar en “Compañeros” a los que ya son amigos
$usuarios = [];
$sql = "
  SELECT u.id, u.nombre, u.apellidos
  FROM usuario u
  JOIN amistad a
    ON (
         (a.usuario_id = $usuario_id AND a.amigo_id = u.id)
         OR
         (a.amigo_id = $usuario_id AND a.usuario_id = u.id)
       )
  WHERE a.estado = 'aceptado'
  ORDER BY u.nombre, u.apellidos
";
$res = $mysqli->query($sql);
while ($row = $res->fetch_assoc()) {
    $usuarios[] = $row;
}
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicar Actividad</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">

  <div class="contenedor-actividad">
    <h1>Publicar Actividad</h1>
    <form method="post" enctype="multipart/form-data">

      <!-- Título -->
      <div class="campo">
        <label>Título:</label><br>
        <input type="text" name="titulo" required>
      </div>

      <!-- Tipo de actividad -->
      <div class="campo">
        <label>Tipo de Actividad:</label><br>
        <select name="tipoActividad" required>
          <option value="">--</option>
          <?php foreach ($tipos as $t): ?>
            <option value="<?= $t['id'] ?>"><?= $t['nombre'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Archivo GPX -->
      <div class="campo">
        <label>Ruta (archivo GPX):</label><br>
        <input type="file" name="rutaGPX" accept=".gpx" required>
      </div>

      <!-- Compañeros (sólo amigos) -->
      <div class="campo">
        <label>Compañeros (sólo tus amigos):</label><br>
        <select name="companeros[]" multiple>
          <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>">
              <?= $u['nombre'] . ' ' . $u['apellidos'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Múltiples imágenes -->
      <div class="campo">
        <label>Imágenes (puedes seleccionar varias):</label><br>
        <input type="file" name="imagenes[]" accept=".jpg,.jpeg,.png" multiple>
      </div>

      <button class="btn" type="submit">Publicar Actividad</button>
    </form>
  </div>

</body>
</html>
