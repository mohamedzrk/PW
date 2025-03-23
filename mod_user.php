<?php include 'header_admin.php'; ?>


<!DOCTYPE html>
<html lang="es">
  
<head>
  <meta charset="UTF-8">
  <title>Modificar usuario</title>
  <link rel="stylesheet" href="styles.css">
</head>


<body class="bg-admin">



  <div class="contenedor-registro">
    <h1>Modificar perfil</h1>
    <form action="ini_admin.php" method="POST" class="formulario">
      <div class="campo">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="Carlos" required> <!-- Campo obligatorio -->
      </div>
      
      <div class="campo">
        <label for="apellidos">Apellidos:</label>
        <input type="text" id="apellidos" name="apellidos" value= "Apellido1 Apellido2" placeholder="Ingrese sus apellidos" required>
      </div>
      
      <div class="campo">
        <label for="tipoActividad">Tipo de Actividad Preferida:</label>
        <select name="tipoActividad" required>
          <option value="">Seleccione...</option>
          <option value="ciclismoRuta" selected>Ciclismo en Ruta</option>
          <option value="ciclismoMTB">Ciclismo MTB</option>
          <option value="senderismo">Senderismo</option>
          <option value="carrera">Carrera</option>
        </select>
      </div>
      
      <div class="campo">
        <label for="fechaNacimiento">Fecha de Nacimiento:</label>
        <input type="date" value="1990-01-01" max="2025-03-05" min="1900-01-01" required />
      </div>
      
      <div class="campo">
        <label for="pais">País:</label>
        <select name="pais" required>
          <option value="">Seleccione País</option>
          <option value="España" selected>España </option>
          <option value="Mexico">México</option>
          <option value="Argentina">Argentina</option>
        </select>
      </div>
      
      <div class="campo">
        <label for="provincia">Provincia:</label>
        <select name="provincia" required>
          <option value="">Seleccione Provincia</option>
          <option value="Madrid" selected>Madrid</option>
          <option value="Barcelona">Barcelona</option>
        </select>
      </div>
      
      <div class="campo">
        <label for="localidad">Localidad:</label>
        <select name="localidad" required>
          <option value="">Seleccione Localidad</option>
          <option value="Alcobendas" selected>Alcobendas</option>
          <option value="Móstoles">Móstoles</option>
        </select>
      </div>
      
      <input type="submit" value="Actualizar Perfil" class="btn-registrar">
    </form>
  </div>
</body>
</html>
