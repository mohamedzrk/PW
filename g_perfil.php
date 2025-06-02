<?php

include 'db.php';

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

$userId = $_SESSION['usuario_id'];

// 1) Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $nombre       = $_POST['nombre'];
    $apellidos    = $_POST['apellidos'];
    $correo       = $_POST['correo']; // Aunque no lo editamos, lo podemos mostrar
    $fecha_nac    = $_POST['fecha_nacimiento'];
    $pais_id      = $_POST['pais'];
    $actividad_id = $_POST['actividad'];
    $fotoRuta     = $_POST['foto_actual']; // ruta anterior

    // Provincia / localidad
    if ($pais_id == 73) {
        // España: elegimos de los selects
        $provincia_id = $_POST['provincia'];
        $localidad_id = $_POST['localidad'];
    } else {
        // Otros países: texto libre
        $prov_txt = $_POST['provincia_text'];
        $loc_txt  = $_POST['localidad_text'];

        // Insertar nueva provincia
        $mysqli->query(
          "INSERT INTO provincias (nombre, pais_id)
           VALUES ('$prov_txt', $pais_id)"
        );
        $provincia_id = $mysqli->insert_id;

        // Insertar nueva localidad
        $mysqli->query(
          "INSERT INTO localidades (nombre, provincia_id)
           VALUES ('$loc_txt', $provincia_id)"
        );
        $localidad_id = $mysqli->insert_id;
    }

    // Subida de foto (opcional)
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/uploads/profile';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file = "$dir/$userId.$ext";
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $file)) {
            $fotoRuta = "uploads/profile/$userId.$ext";
        }
    }

    // Actualizar la fila del usuario
    $mysqli->query(
      "UPDATE usuario SET
         nombre            = '$nombre',
         apellidos         = '$apellidos',
         fecha_nacimiento  = '$fecha_nac',
         pais_id           = $pais_id,
         provincia_id      = $provincia_id,
         localidad_id      = $localidad_id,
         tipo_actividad_id = $actividad_id,
         foto              = '$fotoRuta'
       WHERE id = $userId"
    ) or die('Error al guardar: ' . $mysqli->error);

    header('Location: ini.php');
    exit;
}

// 2) Cargar datos actuales del usuario
$res = $mysqli->query(
  "SELECT nombre, apellidos, email, fecha_nacimiento,
          pais_id, provincia_id, localidad_id,
          tipo_actividad_id, foto
   FROM usuario
   WHERE id = $userId"
);
$user = $res->fetch_assoc();
$fotoActual = $user['foto'] ?: 'img/default.png';

// Si el país no es España (id=73), cargar texto manual
$provManual = '';
$locManual  = '';
if ($user['pais_id'] != 73) {
    $r = $mysqli->query("SELECT nombre FROM provincias WHERE id = {$user['provincia_id']}");
    if ($row = $r->fetch_assoc()) {
        $provManual = $row['nombre'];
    }
    $r = $mysqli->query("SELECT nombre FROM localidades WHERE id = {$user['localidad_id']}");
    if ($row = $r->fetch_assoc()) {
        $locManual = $row['nombre'];
    }
}

// 3) Listas para los selects
$paises      = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
$actividades = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");

// Provincias y localidades de España (id=73)
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
  <title>Gestión de Perfil</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-index">
  <div class="contenedor-registro">
    <h1>Gestión de Perfil</h1>

    <form method="post" action="g_perfil.php" enctype="multipart/form-data">
      <!-- Foto actual -->
      <div class="campo">
        <label>Foto actual:</label><br>
        <img src="<?php echo $fotoActual ?>" style="max-width:120px"><br>
        <input type="file" name="foto" accept="image/*">
        <input type="hidden" name="foto_actual" value="<?php echo $user['foto'] ?>">
      </div>

      <!-- Nombre -->
      <div class="campo">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?php echo $user['nombre'] ?>" required>
      </div>

      <!-- Apellidos -->
      <div class="campo">
        <label>Apellidos</label>
        <input type="text" name="apellidos" value="<?php echo $user['apellidos'] ?>" required>
      </div>

      <!-- Correo (solo lectura, para mostrar) -->
      <div class="campo">
        <label>Correo</label>
        <input type="email" value="<?php echo $user['email'] ?>" readonly>
      </div>

      <!-- Fecha de nacimiento -->
      <div class="campo">
        <label>Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento"
               value="<?php echo $user['fecha_nacimiento'] ?>" required>
      </div>

      <!-- País -->
      <div class="campo">
        <label>País</label>
        <select id="pais" name="pais" required>
          <option value="">--</option>
          <?php while ($p = $paises->fetch_assoc()): ?>
            <option value="<?php echo $p['id'] ?>"
              <?php if ($p['id'] == $user['pais_id']) echo 'selected' ?>>
              <?php echo $p['nombre'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Actividad favorita -->
      <div class="campo">
        <label>Actividad favorita</label>
        <select name="actividad" required>
          <option value="">--</option>
          <?php while ($a = $actividades->fetch_assoc()): ?>
            <option value="<?php echo $a['id'] ?>"
              <?php if ($a['id'] == $user['tipo_actividad_id']) echo 'selected' ?>>
              <?php echo $a['nombre'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Bloque para España (selects) -->
      <div id="bloque-espana">
        <div class="campo">
          <label>Provincia (España)</label>
          <select id="provincia" name="provincia"></select>
        </div>
        <div class="campo">
          <label>Localidad (España)</label>
          <select id="localidad" name="localidad"></select>
        </div>
      </div>

      <!-- Bloque para resto de países (texto libre) -->
      <div id="bloque-resto">
        <div class="campo">
          <label>Provincia</label>
          <input type="text" name="provincia_text" value="<?php echo $provManual ?>">
        </div>
        <div class="campo">
          <label>Localidad</label>
          <input type="text" name="localidad_text" value="<?php echo $locManual ?>">
        </div>
      </div>

      <button class="btn" type="submit">Guardar cambios</button>
      <a href="ini.php" class="btn">Cancelar</a>
    </form>
  </div>

  <script>
    var provincias  = <?php echo json_encode($provincias_data) ?>;
    var localidades = <?php echo json_encode($localidades_data) ?>;
    var selProv     = <?php echo $user['provincia_id'] ?>;
    var selLoc      = <?php echo $user['localidad_id'] ?>;

    var paisSel = document.getElementById('pais');
    var esp     = document.getElementById('bloque-espana');
    var res     = document.getElementById('bloque-resto');
    var provSel = document.getElementById('provincia');
    var locSel  = document.getElementById('localidad');

    function actualizarUbicacion() {
      if (paisSel.value == 73) {
        esp.style.display   = 'block';
        res.style.display   = 'none';

        provSel.innerHTML = '<option value="">--</option>';
        provincias.forEach(function(p) {
          var opt = new Option(p.nombre, p.id);
          if (p.id == selProv) opt.selected = true;
          provSel.add(opt);
        });
        provSel.onchange();
      } else {
        esp.style.display = 'none';
        res.style.display = 'block';
      }
    }

    provSel.onchange = function() {
      locSel.innerHTML = '<option value="">--</option>';
      var list = localidades[this.value] || [];
      list.forEach(function(l) {
        var opt = new Option(l.nombre, l.id);
        if (l.id == selLoc) opt.selected = true;
        locSel.add(opt);
      });
    };

    paisSel.onchange = actualizarUbicacion;
    window.onload = actualizarUbicacion;
  </script>
</body>
</html>
