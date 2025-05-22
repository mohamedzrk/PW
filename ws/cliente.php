<?php


//Leemos datos del formulario
$nombre    = isset($_POST['nombre'])    ? $_POST['nombre']    : '';
$apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';

$usuarios = array();  // array donde luego almacenaremos los usuarios que nos devuelva el servicio SOAP.
$error    = '';       // mensaje de error en caso de que falle algo



// 2. Si el formulario se ha enviado, llamamos al servicio SOAP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {  //Comprueba que el usuario haya pulsado en el boton buscar


    try {
        // Creamos el cliente SOAP en modo sin WSDL
        $cliente = new SoapClient(null, [
            'location' => 'http://localhost/PracticaWeb/ws/servidor.php',
            'uri'      => 'http://localhost/PracticaWeb/ws/servidor.php',
        ]);

        
        $respuesta = $cliente->obtenerUsuarios($nombre, $apellidos); //Llamamos a la funcion obtener usuarios del servidor

        //Introducimos la respuesta en el array $usuarios
            $usuarios = $respuesta;
        
    } catch (SoapFault $e) {
        $error = 'Error SOAP: ' . $e->getMessage();
    }
}

?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Búsqueda de usuarios WebService</title>
  <link rel="stylesheet" href="../styles.css">
</head>

<body class="bg-index">
  <h1>Búsqueda de usuarios (servicio web)</h1>

  <!-- 3. Formulario -->
  <form method="post" id="form">

    <div class="campo">
      <label for="nombre">Nombre:</label>
      <input id="nombre" name="nombre" value="<?php echo $nombre ?>" placeholder="Nombre">
    </div>

    <div class="campo">
      <label for="apellidos">Apellidos:</label>
      <input id="apellidos" name="apellidos" value="<?php echo $apellidos ?>" placeholder="Apellidos">
    </div>

    <button class="paginacion" type="submit">Buscar</button>
    
  </form>

  <!-- 4. Mostrar error si lo hay -->
  <?php if ($error != ''): ?>
    <div id="msg" class="error"><?php echo $error ?></div>
  <?php endif; ?>

  <!-- 5. Mostrar resultados -->
  <div id="results">

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {

        if (count($usuarios) === 0) {
            echo '<div id="msg" class="error">Sin coincidencias.</div>';
        }
         else {
            foreach ($usuarios as $u) {

                    $n = $u['nombre'];
                    $a = $u['apellidos'];
                    $act = $u['actividad_preferida'];
              
                echo '<div class="usuario">'
                     . $n . ' ' . $a
                     . ' — Actividad: ' . $act
                     . '</div>';
            }
        }
    }
    ?>
  </div>

</body>
</html>
