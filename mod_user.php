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

    <form action="ini_admin.php">

      <div class="campo">
        <label for="nombre">Nombre:</label>
        <input type="text"  name="nombre" value="Carlos" required> 
      </div>
      
      <div class="campo">
        <label for="apellidos">Apellidos:</label>
        <input type="text"  name="apellidos" value= "Apellido1 Apellido2" placeholder="Ingrese sus apellidos" required>
      </div>
      
      <div class="campo">
        <label for="tipoActividad">Tipo de Actividad Preferida:</label>
        <select name="tipoActividad" required>
          <option value="1" selected>Ciclismo en Ruta</option>
          <option value="2">Ciclismo MTB</option>
          <option value="3">Senderismo</option>
          <option value="4">Carrera</option>
        </select>
      </div>
      
      <div class="campo">
        <label for="fechaNacimiento">Fecha de Nacimiento:</label>
        <input type="date" value="1990-01-01" max="2025-03-05" min="1900-01-01" required />
      </div>
      
      <div class="campo">
        <label for="pais">País:</label>
        <select name="pais" required>
          <option value="1" selected>España </option>
        </select>
      </div>
      
      <div class="campo">
        <label for="provincia">Provincia:</label>
        <select name="provincia" required>
          <option value="1" selected>Madrid</option>
          <option value="2">Barcelona</option>
        </select>
      </div>
      
      <div class="campo">
        <label for="localidad">Localidad:</label>
        <select name="localidad" required>
          <option value="1" selected>Alcobendas</option>
          <option value="2">Móstoles</option>
        </select>
      </div>
      
      <button class="btn">Modificar</button>


    </form>
  </div>
</body>
</html>
