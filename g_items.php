<?php include 'header_admin.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Gestión de Ítems</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body class="bg-admin">

  <h1>Gestión de Ítems</h1>


  <div class="listado-items">
    <h2>Listado de Ítems (Seleccione el tipo de item que quiere listar)</h2>

    
    <ul class="menu"> 
      <li><a href="tipoActividad.php">Tipo de actividad</a></li>
      <li><a href="paises.php">Paises</a></li>
      <li><a href="provincias.php">Provincias</a></li>
      <li><a href="localidades.php"> Localidades</a></li>
    </ul>
  

  </div>

  <h1>Añadir Item</h1>
  
  <div class="alta-item">

    <h2>Alta Pais</h2>
    <form action="ini_admin.php">


      <div class="campo">
        <label for="nombreItem">Nombre:</label>
        <input type="text" name="nombreItem" required>
      </div>
      
      <button class="btn">Guardar Ítem</button>
    </form>
  </div>


  <div class="alta-item">

    <h2>Alta tipo de actividad</h2>
    <form action="ini_admin.php">


      <div class="campo">
        <label for="nombreItem">Nombre:</label>
        <input type="text" name="nombreItem" required>
      </div>
      
      
      <button class="btn">Guardar Ítem</button>
    </form>

  </div>


  <div class="alta-item">

    <h2>Alta Provincia</h2>
    <form action="ini_admin.php">


      <div class="campo">
        <label for="nombreItem">Nombre:</label>
        <input type="text" name="nombreItem" required>
      </div>
      

      <div class="campo">
        <label for="pais">País:</label>
        <select name="pais">
         
          <option value="1">España</option>
          
        </select>
      </div>
      
      <button class="btn">Guardar Ítem</button>
    </form>
  </div>


<div class="alta-item">

<h2>Alta Localidad</h2>
<form action="ini_admin.php">


  <div class="campo">
    <label for="nombreItem">Nombre:</label>
    <input type="text" name="nombreItem" required>
  </div>
  

  <div class="campo">
    <label for="pais">País:</label>
    <select name="pais">
      <option value="1">España</option>
    </select>
  </div>

  <div class="campo">
    <label for="provincia">Provincia:</label>
    <select  name="provincia">
      <option value="1">Madrid</option>
    </select>
  </div>
  
  <button class="btn">Guardar Ítem</button>
</form>

</div>


</body>
</html>
