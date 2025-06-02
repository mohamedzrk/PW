<?php
include 'db.php';
$error = '';

// Solo ejecuta si vienen datos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $pass   = $_POST['password'];

    // Consulta preparada que trae también es_admin
    $stmt = $mysqli->prepare(
        "SELECT id, password, es_admin 
         FROM usuario 
         WHERE email = ?"
    );
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($u = $res->fetch_assoc()) {
        if ($pass === $u['password']) {
            // Guardamos sesión
            $_SESSION['usuario_id'] = $u['id'];

            // Redirigimos según rol
            if ($u['es_admin'] == 1) {
                header('Location: ini_admin.php');
            } else {
                header('Location: ini.php');
            }
            exit;
        } else {
            $error = 'Contraseña incorrecta.';
        }
    } else {
        $error = 'Usuario no encontrado.';
    }

    $stmt->close();
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
