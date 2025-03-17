<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">  
    <title>Buscador de Usuarios</title> <!-- Titulo que se vera en la pestaña del navegador -->
    <link rel="stylesheet" href="styles.css"> <!-- Enlace al archivo de estilos -->
    
</head>
<body class="bg-bcceee">

        <form class="buscador">
            <input type="text" placeholder="Buscar usuario por nombre o apellidos">
            <button type="submit">Buscar</button>
        </form>

        <div class="resultados">
            <p>No hay resultados aún.</p>
        </div>

</body>
</html>
