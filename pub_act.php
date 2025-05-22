<?php
include 'db.php'; // Incluir conexión a la base de datos

session_start(); // Iniciar sesión

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id']; // ID del usuario que publica la actividad

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $titulo = $_POST['titulo'];
    $tipo_actividad_id = $_POST['tipoActividad'];
    $companeros = $_POST['companeros']; // Compañeros seleccionados
    $fecha = date("Y-m-d H:i:s"); // Fecha y hora de la actividad

    // Subida del archivo GPX
    if (isset($_FILES['rutaGPX']) && $_FILES['rutaGPX']['error'] == 0) {
        $rutaGPX = $_FILES['rutaGPX'];
        $ext = pathinfo($rutaGPX['name'], PATHINFO_EXTENSION);
        $filePath = 'uploads/gpx/' . time() . '.' . $ext;

        // Mover archivo GPX a la carpeta correspondiente
        if (!move_uploaded_file($rutaGPX['tmp_name'], $filePath)) {
            die("Error al subir el archivo GPX");
        }
    } else {
        die("Debe subir un archivo GPX");
    }

    // Insertar la actividad en la base de datos
    $sql = "INSERT INTO actividad (usuario_id, titulo, tipo_actividad_id, fecha, ruta_gpx) 
            VALUES ($usuario_id, '$titulo', $tipo_actividad_id, '$fecha', '$filePath')";

    if (!$mysqli->query($sql)) {
        die("Error al guardar la actividad: " . $mysqli->error);
    }

    // Obtener el ID de la actividad recién insertada
    $actividad_id = $mysqli->insert_id;

    // Asociar los compañeros de la actividad
    foreach ($companeros as $compañero_id) {
        $sql_com = "INSERT INTO compania (actividad_id, usuario_id) VALUES ($actividad_id, $compañero_id)";
        $mysqli->query($sql_com);
    }

    // Subida de imágenes (si las hay)
    if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
        $imagenes = $_FILES['imagenes'];

        foreach ($imagenes['tmp_name'] as $index => $tmpName) {
            $imagenPath = 'uploads/imagenes/' . time() . $index . '.' . pathinfo($imagenes['name'][$index], PATHINFO_EXTENSION);
            if (move_uploaded_file($tmpName, $imagenPath)) {
                $sql_img = "INSERT INTO imagenes (actividad_id, ruta) VALUES ($actividad_id, '$imagenPath')";
                $mysqli->query($sql_img);
            }
        }
    }

    header('Location: tablón.php'); // Redirigir al tablón de actividades
    exit;
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
    <p>Complete el siguiente formulario para publicar una nueva actividad.</p>

    <!-- Formulario para publicar actividad -->
    <form action="publicar_actividad.php" method="post" enctype="multipart/form-data">
      <div class="campo">
        <label for="titulo">Título:</label>
        <input type="text" name="titulo" placeholder="Título de la actividad" required>
      </div>

      <div class="campo">
        <label for="tipoActividad">Tipo de Actividad:</label>
        <select name="tipoActividad" required>
          <option value="1">Ciclismo en Ruta</option>
          <option value="2">Ciclismo MTB</option>
          <option value="3">Senderismo</option>
          <option value="4">Carrera</option>
        </select>
      </div>

      <div class="campo">
        <label for="rutaGPX">Ruta (archivo GPX):</label>
        <input type="file" name="rutaGPX" accept=".gpx" required>
      </div>

      <div class="campo">
        <label for="companeros">Compañeros de Actividad:</label>
        <select name="companeros[]" multiple>
          <option value="1">Usuario 1</option>
          <option value="2">Usuario 2</option>
          <option value="3">Usuario 3</option>
          <option value="4">Usuario 4</option>
        </select>
      </div>

      <div class="campo">
        <label for="imagenes">Imágenes:</label>
        <input type="file" name="imagenes[]" accept=".jpg,.jpeg,.png" multiple>
      </div>

      <button class="btn">Publicar Actividad</button>
    </form>

  </div>
</body>
</html>
