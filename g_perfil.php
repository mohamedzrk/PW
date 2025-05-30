<?php
// g_perfil.php
include 'db.php';
session_start();

// 1) Comprobar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}
$userId = (int)$_SESSION['usuario_id'];

// 2) Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2.1) Datos básicos
    $nombre      = $_POST['nombre'];
    $apellidos   = $_POST['apellidos'];
    $fecha_nac   = $_POST['fecha_nacimiento'];
    $pais_id     = (int)$_POST['pais'];
    $tipo_act_id = (int)$_POST['tipoActividad'];
    $fotoRuta    = $_POST['foto_actual']; // ruta vieja

    // 2.2) Provincia / localidad
    if ($pais_id === 73) {
        // España: tomamos los IDs
        $provincia_id = (int)$_POST['provincia'];
        $localidad_id = (int)$_POST['localidad'];
    } else {
        // Otros países: texto libre
        $prov_txt = $_POST['provincia_text'];
        $loc_txt  = $_POST['localidad_text'];
        // Insertar provincia
        $mysqli->query("
            INSERT INTO provincias (nombre, pais_id)
            VALUES ('$prov_txt', $pais_id)
        ");
        $provincia_id = $mysqli->insert_id;
        // Insertar localidad
        $mysqli->query("
            INSERT INTO localidades (nombre, provincia_id)
            VALUES ('$loc_txt', $provincia_id)
        ");
        $localidad_id = $mysqli->insert_id;
    }

    // 2.3) Subida de foto (opcional)
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/uploads/profile';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $file = "$dir/$userId.$ext";
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $file)) {
            $fotoRuta = "uploads/profile/$userId.$ext";
        }
    }

    // 2.4) Actualizar la fila del usuario
    $mysqli->query("
        UPDATE usuario SET
            nombre = '$nombre',
            apellidos = '$apellidos',
            fecha_nacimiento = '$fecha_nac',
            pais_id = $pais_id,
            provincia_id = $provincia_id,
            localidad_id = $localidad_id,
            tipo_actividad_id = $tipo_act_id,
            foto = '$fotoRuta'
        WHERE id = $userId
    ") or die("Error al guardar: " . $mysqli->error);

    header('Location: g_perfil.php');
    exit;
}

// 3) Cargar datos actuales del usuario
$res = $mysqli->query("
    SELECT nombre, apellidos, email, fecha_nacimiento,
           pais_id, provincia_id, localidad_id,
           tipo_actividad_id, foto
    FROM usuario
    WHERE id = $userId
");
$user = $res->fetch_assoc();
$fotoActual = $user['foto'] ?: 'img/default.png';

// 3.1) Para pre-llenar manual si no es España
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

// 4) Listas para los selects
$paises      = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
$actividades = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");

// Provincias y localidades de España (id=73)
$prov_res = $mysqli->query("SELECT id, nombre FROM provincias WHERE pais_id=73 ORDER BY nombre");
$provincias_data = [];
$prov_ids = [];
while ($p = $prov_res->fetch_assoc()) {
    $provincias_data[] = $p;
    $prov_ids[] = $p['id'];
}
$localidades_data = [];
if (count($prov_ids) > 0) {
    $in = implode(',', $prov_ids);
    $loc_res = $mysqli->query("
        SELECT id, nombre, provincia_id
        FROM localidades
        WHERE provincia_id IN ($in)
        ORDER BY nombre
    ");
    while ($l = $loc_res->fetch_assoc()) {
        $localidades_data[$l['provincia_id']][] = $l;
    }
}
?>
<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Perfil</title>
  <link rel="stylesheet" href="styles.css">
  <script>
    // Datos para JS
    var provincias  = <?= json_encode($provincias_data, JSON_UNESCAPED_UNICODE) ?>;
    var localidades = <?= json_encode($localidades_data, JSON_UNESCAPED_UNICODE) ?>;
    var selProv     = <?= $user['provincia_id'] ?>;
    var selLoc      = <?= $user['localidad_id'] ?>;

    window.onload = function() {
      var paisSel   = document.getElementById('pais');
      var espBlock  = document.getElementById('bloque-esp');
      var restoBlock= document.getElementById('bloque-resto');
      var provSel   = document.getElementById('provincia');
      var locSel    = document.getElementById('localidad');

      // Mostrar/ocultar bloques y rellenar selects
      function actualizarUbicacion() {
        if (+paisSel.value === 73) {
          espBlock.style.display   = 'block';
          restoBlock.style.display = 'none';

          // Provincias
          provSel.innerHTML = '<option value="">--</option>';
          provincias.forEach(function(p) {
            var opt = new Option(p.nombre, p.id);
            if (p.id == selProv) opt.selected = true;
            provSel.add(opt);
          });
          provSel.onchange();
        } else {
          espBlock.style.display   = 'none';
          restoBlock.style.display = 'block';
        }
      }

      // Al cambiar provincia, cargar sus localidades
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
      actualizarUbicacion();
    };
  </script>
</head>
<body class="bg-index">
  <div class="contenedor-registro">
    <h1>Gestión de Perfil</h1>
    <form method="post" enctype="multipart/form-data">

      <!-- Foto actual -->
      <div class="campo">
        <label>Foto actual:</label><br>
        <img src="<?= $fotoActual ?>" style="max-width:120px"><br>
        <input type="file" name="foto" accept="image/*">
        <input type="hidden" name="foto_actual" value="<?= $user['foto'] ?>">
      </div>

      <!-- Nombre y apellidos -->
      <div class="campo">
        <label>Nombre</label>
        <input name="nombre" value="<?= $user['nombre'] ?>" required>
      </div>
      <div class="campo">
        <label>Apellidos</label>
        <input name="apellidos" value="<?= $user['apellidos'] ?>" required>
      </div>

      <!-- Fecha de nacimiento -->
      <div class="campo">
        <label>Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento"
               value="<?= $user['fecha_nacimiento'] ?>" required>
      </div>

      <!-- País y actividad -->
      <div class="campo">
        <label>País</label>
        <select id="pais" name="pais" required>
          <option value="">--</option>
          <?php while ($p = $paises->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"
              <?= $p['id']==$user['pais_id']?'selected':''?>>
              <?= $p['nombre'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="campo">
        <label>Tipo de actividad</label>
        <select name="tipoActividad" required>
          <option value="">--</option>
          <?php while ($a = $actividades->fetch_assoc()): ?>
            <option value="<?= $a['id'] ?>"
              <?= $a['id']==$user['tipo_actividad_id']?'selected':''?>>
              <?= $a['nombre'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Bloque para España (selects) -->
      <div id="bloque-esp" style="display:none">
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
      <div id="bloque-resto" style="display:none">
        <div class="campo">
          <label>Provincia</label>
          <input name="provincia_text" value="<?= $provManual ?>">
        </div>
        <div class="campo">
          <label>Localidad</label>
          <input name="localidad_text" value="<?= $locManual ?>">
        </div>
      </div>

      <button class="btn">Guardar cambios</button>
      <a href="ini.php" class="btn">Cancelar</a>
    </form>
  </div>
</body>
</html>
