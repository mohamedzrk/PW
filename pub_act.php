<?php

include 'db.php';

// 1) Verificar sesión
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
    $sql1 = "
      INSERT INTO actividad (usuario_id, titulo, tipo_actividad_id, fecha)
      VALUES ($usuario_id, '$titulo', $tipoActividad, '$fecha')
    ";
    $mysqli->query($sql1) or die("Error al guardar actividad: " . $mysqli->error);
    $actividad_id = $mysqli->insert_id;

    // 2.4) Guardar ruta GPX en la tabla 'rutas'
    $sql2 = "
      INSERT INTO rutas (actividad_id, archivo)
      VALUES ($actividad_id, '$rutaGpx')
    ";
    $mysqli->query($sql2) or die("Error al guardar ruta GPX: " . $mysqli->error);

    // 2.5) Asociar compañeros (si se seleccionaron)
    if (!empty($_POST['companeros'])) {
        foreach ($_POST['companeros'] as $cid) {
            $cid = (int) $cid;
            $sql3 = "
              INSERT INTO compania (actividad_id, usuario_id)
              VALUES ($actividad_id, $cid)
            ";
            $mysqli->query($sql3) or die("Error al asociar compañero: " . $mysqli->error);
        }
    }

    // 2.6) Subir múltiples imágenes (opcional)
    if (!empty($_FILES['imagenes']['name'][0])) {
        if (!is_dir('uploads/imagenes')) {
            mkdir('uploads/imagenes', 0755, true);
        }
        foreach ($_FILES['imagenes']['tmp_name'] as $i => $tmp) {
            if ($_FILES['imagenes']['error'][$i] === 0) {
                $extImg  = pathinfo($_FILES['imagenes']['name'][$i], PATHINFO_EXTENSION);
                $imgName = time() . "_{$i}." . $extImg;
                $imgPath = "uploads/imagenes/$imgName";
                move_uploaded_file($tmp, $imgPath);
                $sql4 = "
                  INSERT INTO imagenes (actividad_id, ruta)
                  VALUES ($actividad_id, '$imgPath')
                ";
                $mysqli->query($sql4) or die("Error al guardar imagen: " . $mysqli->error);
            }
        }
    }

    // 2.7) Redirigir al tablón
    header('Location: tablón.php');
    exit;
}

// 3) Cargar datos para mostrar en el formulario
// 3.1) Tipos de actividad
$tipos = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");

?>
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicar Actividad</title>
  <link rel="stylesheet" href="styles.css">

  <!-- Cargamos jQuery 1.11.3  -->
  <script src="jquery-1.11.3.min.js"></script>
  <script>
    
  
    

    function cargaAmigos() {
      $.ajax({
        type: "POST",
        url: 'cargaAmigos.php',
        dataType: 'json',
        beforeSend: function() {
          $("#resultado").html("Cargando amigos...");
        },
        success: function(data) {
          $("#resultado").html("Listo.");
          var select = $("#companeros"),
              options = '';
          select.empty();
          // data es un array de objetos {id, nombre_completo}
          for (var i = 0; i < data.length; i++) {
            options += "<option value='" 
                       + data[i].id + "'>"
                       + data[i].nombre_completo 
                       + "</option>";
          }
          select.append(options);
        },
        error: function() {
          $("#resultado").html("Error al cargar amigos.");
        }
      });
    }

    cargaAmigos();
  </script>
</head>
<body class="bg-index">

  <div class="contenedor-actividad">
    <h1>Publicar Actividad</h1>
    <form method="post" enctype="multipart/form-data">

      <!-- Título -->
      <div class="campo">
        <label>Título:</label>
        <input type="text" name="titulo" required>
      </div>

      <!-- Tipo de actividad -->
      <div class="campo">
        <label>Tipo de Actividad:</label>
        <select name="tipoActividad" required>
          <option value="">--</option>
          <?php while ($t = $tipos->fetch_assoc()): ?>
            <option value="<?= $t['id'] ?>"><?= $t['nombre'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Archivo GPX -->
      <div class="campo">
        <label>Ruta (archivo GPX):</label>
        <input type="file" name="rutaGPX" accept=".gpx" required>
      </div>

      <!-- Compañeros (se llenará vía AJAX) -->
      <div class="campo">
        <label>Compañeros (sólo tus amigos):</label>
        <select id="companeros" name="companeros[]" multiple size="5"">
        
        </select>
        
        <div id="resultado"></div>
      </div>

      <!-- Múltiples imágenes -->
      <div class="campo">
        <label>Imágenes (puedes seleccionar varias):</label>
        <input type="file" name="imagenes[]" accept=".jpg,.jpeg,.png" multiple>
      </div>

      <button class="btn" type="submit">Publicar Actividad</button>
    </form>
  </div>

</body>
</html>
