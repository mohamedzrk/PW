<?php
// pub_act.php
include 'db.php';
session_start();

// 1) Comprobar que hay sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}
$usuario_id = (int) $_SESSION['usuario_id'];

// 2) Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2.1) Datos básicos
    $titulo        = $_POST['titulo'];
    $tipoActividad = (int) $_POST['tipoActividad'];
    $fecha         = date('Y-m-d H:i:s');

    // 2.2) Subir GPX
    if (isset($_FILES['rutaGPX']) && $_FILES['rutaGPX']['error'] === 0) {
        $ext      = pathinfo($_FILES['rutaGPX']['name'], PATHINFO_EXTENSION);
        $nombreGPX = time() . '.' . $ext;
        // Asegúrate de que exista uploads/gpx
        if (!is_dir('uploads/gpx')) mkdir('uploads/gpx', 0755, true);
        move_uploaded_file($_FILES['rutaGPX']['tmp_name'], "uploads/gpx/$nombreGPX");
        $rutaGpx = "uploads/gpx/$nombreGPX";
    } else {
        die('Error al subir el archivo GPX');
    }

    // 2.3) Insertar actividad
    $mysqli->query("
        INSERT INTO actividad (usuario_id, titulo, tipo_actividad_id, fecha)
        VALUES ($usuario_id, '$titulo', $tipoActividad, '$fecha')
    ") or die("Error al guardar actividad: " . $mysqli->error);
    $actividad_id = $mysqli->insert_id;

    // 2.4) Guardar GPX en rutas
    $mysqli->query("
        INSERT INTO rutas (actividad_id, archivo)
        VALUES ($actividad_id, '$rutaGpx')
    ") or die("Error al guardar ruta GPX: " . $mysqli->error);

    // 2.5) Asociar compañeros
    if (!empty($_POST['companeros'])) {
        foreach ($_POST['companeros'] as $cid) {
            $cid = (int)$cid;
            $mysqli->query("
                INSERT INTO compania (actividad_id, usuario_id)
                VALUES ($actividad_id, $cid)
            ");
        }
    }

    // 2.6) Subir imágenes
    if (!empty($_FILES['imagenes']['name'][0])) {
        // Asegúrate de que exista uploads/imagenes
        if (!is_dir('uploads/imagenes')) mkdir('uploads/imagenes', 0755, true);
        foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp) {
            if ($_FILES['imagenes']['error'][$i] === 0) {
                $extImg  = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
                $imgName = time() . "_$i." . $extImg;
                $imgPath = "uploads/imagenes/$imgName";
                move_uploaded_file($tmp, $imgPath);
                $mysqli->query("
                    INSERT INTO imagenes (actividad_id, ruta)
                    VALUES ($actividad_id, '$imgPath')
                ");
            }
        }
    }

    // 2.7) Redirigir al tablón
    header('Location: tablón.php');
    exit;
}

// 3) Cargar datos para el formulario
$tipos     = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");
$usuarios  = $mysqli->query("
    SELECT id, nombre, apellidos
    FROM usuario
    WHERE id <> $usuario_id
    ORDER BY nombre
");
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

      <div class="campo">
        <label>Título:</label>
        <input type="text" name="titulo" required>
      </div>

      <div class="campo">
        <label>Tipo de Actividad:</label>
        <select name="tipoActividad" required>
          <option value="">--</option>
          <?php while ($t = $tipos->fetch_assoc()): ?>
            <option value="<?= $t['id'] ?>"><?= $t['nombre'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="campo">
        <label>Ruta (archivo GPX):</label>
        <input type="file" name="rutaGPX" accept=".gpx" required>
      </div>

      <div class="campo">
        <label>Compañeros:</label>
        <select name="companeros[]" multiple>
          <?php while ($u = $usuarios->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>">
              <?= $u['nombre'] . ' ' . $u['apellidos'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="campo">
        <label>Imágenes:</label>
        <input type="file" name="imagenes[]" accept=".jpg,.jpeg,.png" multiple>
      </div>

      <button class="btn" type="submit">Publicar Actividad</button>
    </form>
  </div>
</body>
</html>
