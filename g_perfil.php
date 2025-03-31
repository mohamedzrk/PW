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


   <form action="ini.php"> 
    
      <div class="campo">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" value="Carlos" required> <!-- Campo obligatorio -->
      </div>
      
      <div class="campo">
        <label for="apellidos">Apellidos:</label>
        <input type="text"  name="apellidos" value= "Apellido1 Apellido2" placeholder="Ingrese sus apellidos" required>
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
          <option value="Francia">Francia</option>
          <option value="Alemania">Alemania</option>
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

      <button class="btn">Guardar Cambios</button>
      <button class="btn">Cancelar</button>

      </form>
      

    
  
  </div>
</body>
</html>
