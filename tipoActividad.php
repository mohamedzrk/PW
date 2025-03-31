<?php include 'header_admin.php'; ?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de usuarios</title>
  <link rel="stylesheet" href="styles.css" /> <!-- Vinculamos con hoja de estilos-->
</head>

<body class="bg-admin">

        <div class="buscador">
            <input type="text" placeholder="Buscar tipo de actividad">
            <button class="paginacion" >Buscar</button>
        </div>

        <div class="resultados">
            <p>Implementacion estática provisional. No funciona el filtrado</p>

           <div class="tipoItem">
                <h3>Nombre: Carrera</h3>
                <button class="logout">Eliminar</button>
                <a class="paginacion">Editar</a>
        </div>

        <div class="tipoItem">
                <h3>Nombre: Ciclismo en ruta</h3>
                <button class="logout">Eliminar</button>
                <a class="paginacion">Modificar</a>
        </div>

        <div class="tipoItem">
                <h3>Nombre: Senderismo</h3>
                <button class="logout">Eliminar</button>
                <a class="paginacion">Modificar</a>
        </div>

        <div class="tipoItem">
                <h3>Nombre: Ciclismo MTB</h3>
                <button class="logout">Eliminar</button>
                <a class="paginacion">Modificar</a>
        </div>




</body>