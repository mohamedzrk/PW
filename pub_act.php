<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicar Actividad</title>
  <link rel="stylesheet" href="styles.css">
</head>


<body class="bg-bcceee">
  <div class="contenedor-actividad">
    <h1>Publicar Actividad</h1>
    <form action="publicarActividad.php" ethod="post" class="formulario" enctype="multipart/form-data">
      <div class="campo">
        <label for="titulo">Título:</label>
        <input type="text" id="titulo" name="titulo" placeholder="Título de la actividad" required>
      </div>
      
      <div class="campo">
        <label for="tipoActividad">Tipo de Actividad:</label>
        <select id="tipoActividad" name="tipoActividad" required>
          <option value="">Seleccione...</option>
          <option value="ciclismoRuta">Ciclismo en Ruta</option>
          <option value="ciclismoMTB">Ciclismo MTB</option>
          <option value="senderismo">Senderismo</option>
          <option value="carrera">Carrera</option>
        </select>
      </div>
      
      <div class="campo">
        <label for="rutaGPX">Ruta (archivo GPX):</label>
        <input type="file" id="rutaGPX" name="rutaGPX" accept=".gpx" required>
      </div>
      
      <div class="campo">
        <label for="companeros">Compañeros de Actividad:</label>
        <select id="companeros" name="companeros[]" multiple>
          <option value="usuario1">Usuario 1</option>
          <option value="usuario2">Usuario 2</option>
          <option value="usuario3">Usuario 3</option>
          <option value="usuario4">Usuario 4</option>
        </select>
        <small>Usa Ctrl (o Cmd en Mac) para seleccionar varios</small>
      </div>
      
      <div class="campo">
        <label for="imagenes">Imágenes:</label>
        <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>
      </div>
      
      <input type="submit" value="Publicar Actividad" class="btn-registrar">
    </form>
  </div>
</body>
</html>
