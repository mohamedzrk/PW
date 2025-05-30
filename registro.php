<?php
// registro.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Datos del formulario
    $nombre         = $_POST['nombre'];
    $apellidos      = $_POST['apellidos'];
    $correo         = $_POST['correo'];
    $password       = $_POST['password'];
    $password2      = $_POST['password2'];
    $fecha_nac      = $_POST['fecha_nacimiento'];
    $pais_id        = $_POST['pais'];
    $actividad_id   = $_POST['actividad'];

    if ($password !== $password2) {
        die('Las contraseñas no coinciden.');
    }

    // 2) Provincia / localidad
    if ($pais_id == 73) {
        // España: vienen IDs
        $provincia_id = $_POST['provincia'];
        $localidad_id = $_POST['localidad'];
    } else {
        // Otros: texto libre
        $prov_txt = $_POST['provincia_text'];
        $loc_txt  = $_POST['localidad_text'];

        // Insertar provincia
        $mysqli->query(
          "INSERT INTO provincias (nombre, pais_id) 
           VALUES ('$prov_txt', $pais_id)"
        );
        $provincia_id = $mysqli->insert_id;

        // Insertar localidad
        $mysqli->query(
          "INSERT INTO localidades (nombre, provincia_id) 
           VALUES ('$loc_txt', $provincia_id)"
        );
        $localidad_id = $mysqli->insert_id;
    }

    // 3) Insertar usuario
    $mysqli->query("
      INSERT INTO usuario
        (nombre, apellidos, email, password, fecha_nacimiento,
         pais_id, provincia_id, localidad_id, tipo_actividad_id)
      VALUES
        ('$nombre', '$apellidos', '$correo', '$password', '$fecha_nac',
         $pais_id, $provincia_id, $localidad_id, $actividad_id)
    ") or die('Error al registrar: ' . $mysqli->error);

    header('Location: identificacion.php');
    exit;
}

// 4) Datos para el formulario
$paises      = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
$actividades = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");

// Provincias & localidades de España (id=73)
$prov_res = $mysqli->query("SELECT id, nombre FROM provincias WHERE pais_id = 73 ORDER BY nombre");
$provincias_data = [];
$prov_ids = [];
while ($row = $prov_res->fetch_assoc()) {
    $provincias_data[] = $row;
    $prov_ids[] = $row['id'];
}

$localidades_data = [];
if (count($prov_ids)) {
    $in = implode(',', $prov_ids);
    $loc_res = $mysqli->query(
      "SELECT id, nombre, provincia_id 
       FROM localidades 
       WHERE provincia_id IN ($in)
       ORDER BY nombre"
    );
    while ($r = $loc_res->fetch_assoc()) {
        $localidades_data[$r['provincia_id']][] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">
  <div class="contenedor-registro">
    <h1>Regístrate ¡GRATIS!</h1>
    <form method="post" action="registro.php">

      <div class="campo">
        <label>Nombre</label>
        <input name="nombre" required>
      </div>
      <div class="campo">
        <label>Apellidos</label>
        <input name="apellidos" required>
      </div>
      <div class="campo">
        <label>Correo</label>
        <input type="email" name="correo" required>
      </div>
      <div class="campo">
        <label>Contraseña</label>
        <input type="password" name="password" required>
      </div>
      <div class="campo">
        <label>Repite Contraseña</label>
        <input type="password" name="password2" required>
      </div>
      <div class="campo">
        <label>Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" required>
      </div>
      <div class="campo">
        <label>País</label>
        <select id="pais" name="pais" required>
          <option value="">--</option>
          <?php while ($p = $paises->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="campo">
        <label>Actividad favorita</label>
        <select name="actividad" required>
          <option value="">--</option>
          <?php while ($a = $actividades->fetch_assoc()): ?>
            <option value="<?= $a['id'] ?>"><?= $a['nombre'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Bloque para España -->
      <div id="bloque-espana" style="display:none">
        <div class="campo">
          <label>Provincia (España)</label>
          <select id="provincia" name="provincia"></select>
        </div>
        <div class="campo">
          <label>Localidad (España)</label>
          <select id="localidad" name="localidad"></select>
        </div>
      </div>

      <!-- Bloque resto de países -->
      <div id="bloque-resto" style="display:none">
        <div class="campo">
          <label>Provincia</label>
          <input name="provincia_text">
        </div>
        <div class="campo">
          <label>Localidad</label>
          <input name="localidad_text">
        </div>
      </div>

      <button class="btn" type="submit">Registrarse</button>
    </form>
  </div>

  <script>
    var provincias = <?= json_encode($provincias_data) ?>;
    var localidades = <?= json_encode($localidades_data) ?>;
    var paisSel = document.getElementById('pais');
    var esp = document.getElementById('bloque-espana');
    var res = document.getElementById('bloque-resto');
    var provSel = document.getElementById('provincia');
    var locSel  = document.getElementById('localidad');

    paisSel.onchange = function() {
      if (this.value == 73) {
        esp.style.display = 'block';
        res.style.display = 'none';
        provSel.innerHTML = '<option value="">--</option>';
        provincias.forEach(function(p) {
          provSel.add(new Option(p.nombre, p.id));
        });
        provSel.onchange();
      } else {
        esp.style.display = 'none';
        res.style.display = 'block';
      }
    };

    provSel.onchange = function() {
      locSel.innerHTML = '<option value="">--</option>';
      var list = localidades[this.value] || [];
      list.forEach(function(l) {
        locSel.add(new Option(l.nombre, l.id));
      });
    };

    window.onload = function() {
      paisSel.onchange();
    };
  </script>
</body>
</html>
