<?php
// registro.php
include 'db.php';  // Conexión $mysqli


// 1) ENDPOINTS AJAX PARA PROVINCIAS Y LOCALIDADES (JSON)

//Reponde a una peticion ajax para cargar provincias,
//dependiendo de los parámetros enviados
if (isset($_GET['prov_list'])) {
    header('Content-Type: application/json; charset=UTF-8');
    $out = [['id'=>0,'nombre'=>'--']];
    $q = $mysqli->query("SELECT id, nombre FROM provincias WHERE pais_id = 73 ORDER BY nombre");
    while ($r = $q->fetch_assoc()) $out[] = $r;
    echo json_encode($out);
    exit;
}

// Requiere el ID de la provincia para cargar localidades
// y devuelve un JSON con el ID y nombre de cada localidad
// La primera opción es un valor por defecto
if (isset($_GET['loc_list'])) {
    header('Content-Type: application/json; charset=UTF-8');
    $out = [['id'=>0,'nombre'=>'--']];
    $prov_id = (int)$_GET['prov_id'];
    $q = $mysqli->query("SELECT id, nombre FROM localidades WHERE provincia_id = {$prov_id} ORDER BY nombre");
    while ($r = $q->fetch_assoc()) $out[] = $r;
    echo json_encode($out); //enviar JSON al navegador
    exit;
}

// Para cargar dinámicamente la lista de provincias y localidades sin recargar toda la página.

// ——————————————————————————————————————————————————————————————
// 2) PROCESO DE ALTA (POST)
// ——————————————————————————————————————————————————————————————
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2.1) Campos básicos
    $nombre     = $_POST['nombre'];
    $apellidos  = $_POST['apellidos'];
    $correo     = $_POST['correo'];
    $pass1      = $_POST['password'];
    $pass2      = $_POST['password2'];
    $fecha      = $_POST['fecha_nacimiento'];
    $pais       = (int) $_POST['pais'];
    $actividad  = (int) $_POST['actividad'];  // FK válida

    // 2.2) Validaciones
    if ($pass1 !== $pass2) {
        die('Las contraseñas no coinciden.');
    }

    // 2.3) Provincia / localidad
    if ($pais === 73) {
        // España: recibimos IDs desde <select>
        $provincia_id = (int) $_POST['provincia'];
        $localidad_id = (int) $_POST['localidad'];
        if ($provincia_id === 0) die('Selecciona una provincia de España.');
        if ($localidad_id === 0)  die('Selecciona una localidad de España.');
    }  //si el pais es españa, Se leen los IDs de los <select> generados por AJAX.

    
    else {
        // Otro país: recibimos textos, insertarlos en provincias y localidades
        $prov_txt = $_POST['provincia_text'];
        $loc_txt  = $_POST['localidad_text'];
        if (!$prov_txt || !$loc_txt) {
            die('Escribe provincia y localidad.');
        }

        // Insert provincia
        $stmt = $mysqli->prepare("INSERT INTO provincias (nombre, pais_id) VALUES (?, ?)");
        $stmt->bind_param('si', $prov_txt, $pais);
        $stmt->execute();
        $provincia_id = $stmt->insert_id;
        $stmt->close();

        // Insert localidad
        $stmt = $mysqli->prepare("INSERT INTO localidades (nombre, provincia_id) VALUES (?, ?)");
        $stmt->bind_param('si', $loc_txt, $provincia_id);
        $stmt->execute();
        $localidad_id = $stmt->insert_id;
        $stmt->close();
    }

    // 2.4) Insertar usuario
    $sql = "
      INSERT INTO usuario
        (nombre, apellidos, email, password, fecha_nacimiento,
         pais_id, provincia_id, localidad_id, tipo_actividad_id)
      VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $mysqli->prepare($sql);

    $stmt->bind_param(
      'sssssiiii',
      $nombre, $apellidos, $correo, $pass1, $fecha,
      $pais, $provincia_id, $localidad_id, $actividad
    );
    if (!$stmt->execute()) {
        die('Error al registrar usuario: ' . $stmt->error);
    }

    header('Location: identificacion.php');
    exit;
}

// ——————————————————————————————————————————————————————————————
// 3) CARGAR DATOS PARA EL FORMULARIO
// ——————————————————————————————————————————————————————————————
// 3.1) Países
$countries = [];
$q = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
while ($r = $q->fetch_assoc()) $countries[] = $r;
// 3.2) Actividades
$actividades = [];
$q = $mysqli->query("SELECT id, nombre FROM tipo_actividad ORDER BY nombre");
while ($r = $q->fetch_assoc()) $actividades[] = $r;
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
    <form id="registro" method="post" action="registro.php">
      <!-- Campos básicos -->
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
      <label>Repetir contraseña</label>
      <input type="password" name="password2" required>
      </div>

      <div class="campo">
      <label>Fecha de nacimiento</label>
      <input type="date" name="fecha_nacimiento" required>
      </div>

      <!-- País -->
      <div class="campo">
        <label>País</label>
        <select id="pais" name="pais" required>
          <?php foreach($countries as $p): ?>
            <option value="<?=$p['id']?>"><?=$p['nombre']?></option>
          <?php endforeach?>
        </select>
      </div>

      <!-- Actividad favorita -->
      <div class="campo">
        <label>Actividad favorita</label>
        <select name="actividad" required>
          <?php foreach($actividades as $a): ?>
            <option value="<?=$a['id']?>"><?=$a['nombre']?></option>
          <?php endforeach?>
        </select>
      </div>

      <!-- Provincia / Localidad select (España) -->
      <div class="campo" id="prov-select" style="display:none">
        <label>Provincia</label>
        <select id="provincia" name="provincia"></select>
      </div>
      
      <div class="campo" id="loc-select" style="display:none">
        <label>Localidad</label>
        <select id="localidad" name="localidad"></select>
      </div>

      <!-- Inputs manuales (otros países) -->
      <div class="campo" id="prov-text" style="display:none">
        <label>Provincia (manual)</label>
        <input name="provincia_text">
      </div>
      <div class="campo" id="loc-text" style="display:none">
        <label>Localidad (manual)</label>
        <input name="localidad_text">
      </div>

      <button class="btn" type="submit">Registrarse</button>
    </form>
  </div>


  <script>
    // Referencia a los elementos del formulario
    const pais     = document.getElementById('pais'); // País
    const provS    = document.getElementById('prov-select'); //  div Select provincia
    const locS     = document.getElementById('loc-select');   // div  Select localidad
    const provT    = document.getElementById('prov-text'); // Input provincia
    const locT     = document.getElementById('loc-text');  // Input localidad
    const prov     = document.getElementById('provincia');  // Select provincia
    const loc      = document.getElementById('localidad'); // Select localidad

    
    // Carga provincias de España
    function loadProvincias() { 
      fetch('registro.php?prov_list=1') // Llama al endpoint AJAX
        .then(r=>r.json()) // Convierte la respuesta a JSON
        .then(data=>{ // CUando llegan los datos
          prov.innerHTML = ''; // Limpia el select
          data.forEach(o=> prov.add(new Option(o.nombre, o.id))); // Añade opciones
          loc.innerHTML = '<option value="0">--</option>'; // Reseteamos localidades
        });
    }

    // Para que cuando cambien las provincias, se carguen las localidades
    function loadLocalidades() {
      fetch(`registro.php?loc_list=1&prov_id=${prov.value}`)
        .then(r=>r.json())
        .then(data=>{
          loc.innerHTML = '';
          data.forEach(o=> loc.add(new Option(o.nombre, o.id)));
        });
    }

    // Para que al cambiar el país, se carguen provincias o localidades o input
    pais.addEventListener('change', ()=>{ // Cambia vista
      if (+pais.value === 73) { // España
        // España → muestra selects
        provS.style.display = locS.style.display = 'block';
        provT.style.display = locT.style.display = 'none';
        loadProvincias(); 
      } else {
        // Otros países → inputs libres
        provS.style.display = locS.style.display = 'none';
        provT.style.display = locT.style.display = 'block';
      }
    });

    // Enlazamos el evento change del <select id="provincia"> a la función que llama al AJAX de localidades.
    prov.addEventListener('change', loadLocalidades); 

    // Inicializa la vista según país por defecto
    document.addEventListener('DOMContentLoaded', ()=> pais.dispatchEvent(new Event('change')));
  </script>
