<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">  
    <title>Buscador de Usuarios</title> <!-- Titulo que se vera en la pestaña del navegador -->
    <link rel="stylesheet" href="styles.css"> <!-- Enlace al archivo de estilos -->

</head>

<body class="bg-index">

        <div class="buscador">
            <input type="text"  placeholder="Buscar usuario">

            <button class="paginacion">Buscar</button>
        </div>

            <p>Implementacion estática provisional. No funciona el filtrado</p>

        <div class="usuarioAmigo">
                <h5>¡En la lista de amigos amigos!</h5>
                <img src="ZLbZBbSTUCTe8Da5-generated_image.jpg" alt="Foto_pred.png">
                <h3>Nombre: Antonio</h3>
                <p> Apellidos: Garcia Montana</p>
                <button class="btn">Ultima actividad</button>
                <a href="ver_perfilAmigo.php" class="btn">Ver Perfil</a>
        </div>

        <div class="usuarioNoAmigo">
                <h5 id="no">¡No esta en la lista de amigos!</h5>
                <img src="ZLbZBbSTUCTe8Da5-generated_image.jpg" alt="Foto_pred.png">
                <h3>Nombre: Ivan</h3>
                <p> Apellidos: Zamorano Hernandez</p>
                <button class="btn">Ultima actividad</button>
                <button class="btn">Enviar solicitud de amistad</button>
        </div>

</body>
</html>