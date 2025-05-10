<?php
// register.php
include 'db.php';  // Aquí defines $conn = mysqli_connect(...)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Recoger y escapar datos
    $nombre    = mysqli_real_escape_string($conn, trim($_POST['nombre']));
    $apellidos = mysqli_real_escape_string($conn, trim($_POST['apellidos']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['correo']));
    $pass1     = $_POST['password'];
    $pass2     = $_POST['password2'];
    $fecha     = $_POST['fecha_nacimiento'];
    $pais      = (int) $_POST['pais'];
    $provincia = (int) $_POST['provincia'];
    $localidad = (int) $_POST['localidad'];
    $actividad = (int) $_POST['actividad'];

    // 2) Validaciones básicas
    if (!$nombre || !$apellidos || !$email || !$pass1 || !$pass2) {
        die('Faltan datos obligatorios.');
    }
    if ($pass1 !== $pass2) {
        die('Las contraseñas no coinciden.');
    }

    


    // 4) Insertar en la base de datos
    $sql = "
      INSERT INTO usuario
        (nombre, apellidos, email, password, fecha_nacimiento,
         pais_id, provincia_id, localidad_id, tipo_actividad_id)
      VALUES
        ('$nombre', '$apellidos', '$email', '$pass_md5', '$fecha',
         $pais, $provincia, $localidad, $actividad)
    ";
    if (!mysqli_query($conn, $sql)) {
        die('Error al registrar usuario: ' . mysqli_error($conn));
    }

    // 5) Redirigir al login
    header('Location: identificación.html');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Página de registro</title>
  <link rel="stylesheet" href="styles.css" /> <!-- Vinculamos con hoja de estilos-->
</head>


<body class="bg-index">

  <div class="contenedor-registro">
   
    <h1>
      Regístrese de forma completamente ¡GRATUITA!
    </h1>


    <!-- Formulario de registro -->
    <form action="register.php" method="post"> 
     
      <div class="campo">
        <label for="nombre">Nombre</label>
        <input type="text"/>
      </div>
      
      
      <div class="campo">
        <label for="apellidos">Apellidos</label>
        <input type="text"/>
      </div>
      
     
      <div class="campo">
        <label for="correo">Correo Electrónico</label>
        <input type="email" name="correo" required />
      </div>

       
       <div class="campo">
        <label for="nombreUsuario">Nombre de usuario</label>
        <input type="text"/>
      </div>

     
      <h2>¿Cuál es tu actividad favorita?</h2>
      <div class="campo">
        <label for="actividad">Tipo de actividad preferida</label>
        <select name="actividad" required>
          <option value="1">Ciclismo en ruta</option>
          <option value="2">Ciclismo MTB</option>
          <option value="3">Senderismo</option>
          <option value="4">Carrera</option>
        </select>
      </div>

      <h2>¿Cuál es tu fecha de nacimiento?</h2>
      <div class="campo">
        <label for="fecha_nacimiento">Fecha de nacimiento</label>
        <input name= "fecha_nacimiento" type="date" max="2025-03-05" min="1900-01-01" required />
      </div>
        
      <div class="campo">
        <label for="pais">País en el que se encuentra:</label>
        <select name="pais" >
          <option value="1">España</option>
         
        </select>
      </div>

   
      <div class="campo">
        <label for="provincia">Provincia</label>
        <select name="provincia">
          <option value="1">Ceuta</option>
          
        </select>
      </div>

      <div class="campo">
        <label for="localidad">Localidad</label>
        <select name="localidad">
          <option value="1">Ceuta</option>
          
        </select>
      </div>

      <div class="campo">
        <label for="password">Contraseña</label>
        <input name="password" type="password" required />
      </div>

    
      <div class="campo">
        <label for="password2">Repetir Contraseña</label>
        <input name="password2" type="password"  required />
      </div>

     
      <button class="btn" type="submit">Registrarse</button>
      </form>
     
  </div>

  
</body>
</html>
