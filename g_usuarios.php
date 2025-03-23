<?php include 'header_admin.php'; ?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de usuarios</title>
  <link rel="stylesheet" href="styles.css" /> <!-- Vinculamos con hoja de estilos-->
</head>

<body class="bg-admin">

<form class="buscador">
            <input type="text" placeholder="Buscar usuario">
            <button type="submit">Buscar</button>
        </form>

        <div class="resultados">
            <p>Implementacion estática provisional. No funciona el filtrado</p>

            <div class="g_usuario">
                <img src="ZLbZBbSTUCTe8Da5-generated_image.jpg" alt="Foto_pred.png">
                <h3>Correo: dummy@gmail.com</h3>
                <p>Actividades: 5</p>
                <p>País: España</p>
                <p>Provincia: Madrid</p>

                <a href="ver_perfil.php" class="btn-registrar">Ver Perfil</a>
                <button class="btn-registrar">Eliminar</button>
                <a href="mod_user.php" class="btn-registrar">Modificar</a>
        </div>





</body>