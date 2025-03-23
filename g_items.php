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
    <h2>Listado de Ítems</h2>

        <p>ID: 1</p>
        <p>Entidad: Tipo de Actividad</p>
        <p>Nombre: Ciclismo en Ruta</p>

        <button class="btn-registrar">Editar</button>
        <button class="btn-registrar">Eliminar</button>
  

  </div>


  <div class="alta-item">

    <h2>Alta de Ítem</h2>
    <form action="ini_admin.php" method="post">
      <div class="campo">
        <label for="entidad">Entidad:</label>
        <select id="entidad" name="entidad" required>
          <option value="">Seleccione la entidad</option>
          <option value="tipoActividad">Tipo de Actividad</option>
          <option value="pais">País</option>
          <option value="provincia">Provincia</option>
          <option value="localidad">Localidad</option>
        </select>
      </div>


      <div class="campo">
        <label for="nombreItem">Nombre:</label>
        <input type="text" id="nombreItem" name="nombreItem" required>
      </div>
      

      <div class="campo">
        <label for="pais-select">País (para Provincia o Localidad):</label>
        <select id="pais-select" name="pais-select">
          <option value="">Seleccione un país</option>
          <option value="España">España</option>
          <option value="México">México</option>
          <option value="Argentina">Argentina</option>
        </select>
      </div>

      <div class="campo">
        <label for="provincia-select">Provincia (para Localidad):</label>
        <select id="provincia-select" name="provincia-select">
          <option value="">Seleccione una provincia</option>
          <option value="Madrid">Madrid</option>
          <option value="Barcelona">Barcelona</option>
        </select>
      </div>
      
      <button type="submit" class="btn-registrar">Guardar Ítem</button>
    </form>
  </div>

</body>
</html>
