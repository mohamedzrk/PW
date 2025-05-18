<?php
include 'db.php';
$error = '';

// Solo ejecuta el código si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoge los valores sin modificaciones
    $correo = $_POST['correo'];
    $pass = $_POST['password'];

    // Consulta para encontrar el usuario
    $res = $mysqli->query("SELECT id, password FROM usuario WHERE email = '$correo'");

    if ($u = $res->fetch_assoc()) {
        // Comparar contraseñas tal cual (sin cifrado)
        if ($pass === $u['password']) {
            // Guardar sesión y redirigir
            session_start();
            $_SESSION['usuario_id'] = $u['id'];
            header('Location: ini.php');
            exit;
        } else {
            $error = 'Contraseña incorrecta.';
        }
    } else {
        $error = 'Usuario no encontrado.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="bg-index">
  <div class="contenedor-inicio">
    <h1>¡Inicia Sesión!</h1>

    <?php if ($error): ?>
      <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <form action="identificacion.php" method="post">
      <div class="campo">
        <label for="correo">Correo Electrónico</label>
        <input type="email" id="correo" name="correo" required />
      </div>

      <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required />
      </div>

      <button class="btn" type="submit">Iniciar Sesión</button>
    </form>

    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
  </div>
</body>
</html>
