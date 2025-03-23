<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">  
    <title>Buscador de Usuarios</title> <!-- Titulo que se vera en la pestaña del navegador -->
    <link rel="stylesheet" href="styles.css"> <!-- Enlace al archivo de estilos -->

</head>
<body class="bg-index">

        <form class="buscador">
            <input type="text" placeholder="Buscar usuario">
            <button type="submit">Buscar</button>
        </form>

        <div class="resultados">
            <p>Implementacion estática provisional. No funciona el filtrado</p>

            <div class="usuario">
                <img src="ZLbZBbSTUCTe8Da5-generated_image.jpg" alt="Foto_pred.png">
                <h3>Correo: dummy@gmail.com</h3>
                <p>Actividades: 5</p>
                <p>País: España</p>
                <p>Provincia: Madrid</p>
                <a href="ver_perfil.php" class="btn-registrar">Ver Perfil</a>
        </div>

</body>
</html>