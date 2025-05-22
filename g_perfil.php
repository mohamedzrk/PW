<?php
include 'db.php';

/* ───────── SESIÓN ─────────────────────────────────────────── */
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { header('Location: identificacion.php'); exit; }
$userId = (int)$_SESSION['usuario_id'];

/* ───────── CARGAR DATOS ACTUALES ──────────────────────────── */
$res = $mysqli->query("SELECT * FROM usuario WHERE id=$userId");
if (!$res) die("Error BD: ".$mysqli->error);
$user = $res->fetch_assoc();
if (!$user) die("Usuario no encontrado");

$fotoActual = $user['foto'] ?: 'img/default.png';     // ruta foto o por-defecto
$provSel    = $user['provincia_id'];                  // provincia actual
$locSel     = $user['localidad_id'];                  // localidad actual

/* ───────── GUARDAR CAMBIOS ───────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $nombre    = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $fechaNac  = $_POST['fechaNacimiento'];
    $paisId    = (int)$_POST['pais'];
    $provId    = (int)$_POST['provincia'];
    $locId     = (int)$_POST['localidad'];
    $tipoActId = (int)$_POST['tipoActividad'];
    $fotoRuta  = $fotoActual;                                // conservar foto

    /* ── subida de nueva foto (opcional) ─────────────────── */
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error']===0) {
        $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $dir  = __DIR__.'/uploads/profile';
        if (!is_dir($dir)) mkdir($dir,0755,true);
        $file = "$dir/$userId.$ext";
        if (move_uploaded_file($_FILES['foto']['tmp_name'],$file))
            $fotoRuta = "uploads/profile/$userId.$ext";
    }

    /* ── comprobar FK provincia / localidad ──────────────── */
    $okProv = $mysqli->query("SELECT 1 FROM provincias WHERE id=$provId")->num_rows;
    $okLoc  = $mysqli->query("SELECT 1 FROM localidades WHERE id=$locId")->num_rows;
    if (!$okProv || !$okLoc) die("Provincia o localidad inexistente");

    /* ── actualizar ─────────────────────────────────────── */
    $sql = "UPDATE usuario SET
              nombre='$nombre', apellidos='$apellidos',
              tipo_actividad_id=$tipoActId, fecha_nacimiento='$fechaNac',
              pais_id=$paisId, provincia_id=$provId, localidad_id=$locId,
              foto='$fotoRuta'
            WHERE id=$userId";
    if (!$mysqli->query($sql)) die("Error al guardar: ".$mysqli->error);

    header('Location: g_perfil.php');   // recargar con datos ya guardados
    exit;
}

/* ───────── LISTAS PARA SELECTS ──────────────────────────── */
$paises      = $mysqli->query("SELECT id,nombre FROM paises ORDER BY nombre");
$actividades = $mysqli->query("SELECT id,nombre FROM tipo_actividad ORDER BY nombre");
?>
<?php include 'header.php'; ?>
<!DOCTYPE html><html lang="es"><head>
<meta charset="utf-8"><title>Perfil</title><link rel="stylesheet" href="styles.css">
<script>
let selProv = <?= $provSel ?>, selLoc = <?= $locSel ?>;

function cargaProvincias() {
  const pais = document.getElementById('pais').value,
        provSelc = document.getElementById('provincia'),
        locSelc  = document.getElementById('localidad');

  if (pais == 73) {          // España → AJAX
    fetch('registro.php?prov_list=1').then(r=>r.json()).then(data=>{
      provSelc.innerHTML='';
      data.forEach(p=>provSelc.add(new Option(p.nombre,p.id)));
      provSelc.value = selProv;     // marcar seleccionada
      cargaLocalidades();           // y cargar localidades
    });
    document.getElementById('prov-select').style.display='';
    document.getElementById('loc-select').style.display='';
    document.getElementById('prov-text').style.display='none';
    document.getElementById('loc-text').style.display='none';
  } else {                  // otro país → campos libres
    document.getElementById('prov-select').style.display='none';
    document.getElementById('loc-select').style.display='none';
    document.getElementById('prov-text').style.display='';
    document.getElementById('loc-text').style.display='';
  }
}

function cargaLocalidades() {
  const provId = document.getElementById('provincia').value,
        locSelc= document.getElementById('localidad');
  fetch(`registro.php?loc_list=1&prov_id=${provId}`).then(r=>r.json()).then(data=>{
    locSelc.innerHTML='';
    data.forEach(l=>locSelc.add(new Option(l.nombre,l.id)));
    locSelc.value = selLoc;        // marcar seleccionada
  });
}

document.addEventListener('DOMContentLoaded',()=>{
  cargaProvincias();
});
</script>
</head><body class="bg-index">
<div class="contenedor-registro">
  <h1>Gestión de Perfil</h1>
  <form method="post" enctype="multipart/form-data">

    <div class="campo">
      <label>Foto actual:</label><br>
      <img src="<?= $fotoActual ?>" style="max-width:120px"><br>
      <input type="file" name="foto" accept="image/*">
    </div>

    <div class="campo">
      <label>Nombre:</label>
      <input name="nombre" value="<?= $user['nombre'] ?>" required>
    </div>

    <div class="campo">
      <label>Apellidos:</label>
      <input name="apellidos" value="<?= $user['apellidos'] ?>" required>
    </div>

    <div class="campo">
      <label>Tipo de actividad:</label>
      <select name="tipoActividad" required>
        <?php while($a=$actividades->fetch_assoc()): ?>
          <option value="<?= $a['id'] ?>" <?= $a['id']==$user['tipo_actividad_id']?'selected':'' ?>>
            <?= $a['nombre'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="campo">
      <label>Fecha de nacimiento:</label>
      <input type="date" name="fechaNacimiento" value="<?= $user['fecha_nacimiento'] ?>" required>
    </div>

    <div class="campo">
      <label>País:</label>
      <select id="pais" name="pais" onchange="cargaProvincias()" required>
        <?php while($p=$paises->fetch_assoc()): ?>
          <option value="<?= $p['id'] ?>" <?= $p['id']==$user['pais_id']?'selected':'' ?>>
            <?= $p['nombre'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- selects España -->
    <div class="campo" id="prov-select" style="display:none">
      <label>Provincia:</label>
      <select id="provincia" name="provincia" onchange="cargaLocalidades()" required></select>
    </div>

    <div class="campo" id="loc-select" style="display:none">
      <label>Localidad:</label>
      <select id="localidad" name="localidad" required></select>
    </div>

    <!-- inputs manuales resto países -->
    <div class="campo" id="prov-text" style="display:none">
      <label>Provincia (manual):</label>
      <input name="provincia_text">
    </div>
    <div class="campo" id="loc-text" style="display:none">
      <label>Localidad (manual):</label>
      <input name="localidad_text">
    </div>

    <button class="btn">Guardar cambios</button>
    <a href="ini.php" class="btn">Cancelar</a>
  </form>
</div>
</body></html>
