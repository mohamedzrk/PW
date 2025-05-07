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
            <input type="text" placeholder="Buscar usuario">
            <button class="paginacion" >Buscar</button>
            </div>

            <p>Implementacion estática provisional. No funciona el filtrado</p>

            <div class="g_usuario">
            <img id="imagen10" src="ZLbZBbSTUCTe8Da5-generated_image.jpg" alt="Foto_pred.png">
                <h3>Nombre: Antonio</h3>
                <p> Apellidos: Garcia Montana</p>
                <a href="ver_perfilAmigo.php" class="paginacion">Ver Perfil</a>
                <button class="logout">Eliminar</button>
                <a href="mod_user.php" class="paginacion">Modificar</a>
           </div>
           


</body>