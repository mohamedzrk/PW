<?php
// g_items.php
include 'db.php';  // Arranca sesión y deja disponible $mysqli

// 1) Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}

// 2) Procesar formulario de alta (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['item_type'];   // 'pais', 'actividad', 'provincia' o 'localidad'
    $name = $_POST['name'];        // Nombre del ítem 

    if ($name !== '') {
        if ($type === 'pais') {
            // INSERT sencillo en tabla paises
            $mysqli->query("INSERT INTO paises (nombre) VALUES ('$name')");
        }
        elseif ($type === 'actividad') {
            // INSERT en tipo_actividad
            $mysqli->query("INSERT INTO tipo_actividad (nombre) VALUES ('$name')");
        }
        elseif ($type === 'provincia') {
            // INSERT en provincias, recibimos pais_id
            $paisId = (int) $_POST['pais_id'];
            $mysqli->query("
                INSERT INTO provincias (nombre, pais_id)
                VALUES ('$name', $paisId)
            ");
        }
        elseif ($type === 'localidad') {
            // INSERT en localidades, recibimos provincia_id
            $provId = (int) $_POST['provincia_id'];
            $mysqli->query("
                INSERT INTO localidades (nombre, provincia_id)
                VALUES ('$name', $provId)
            ");
        }
    }

    // Redirigimos para limpiar POST y ver los nuevos ítems
    header('Location: g_items.php');
    exit;
}

// 3) Cargar listas para los selects “País” y “Provincia”
$resPaises     = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
$resProvincias = $mysqli->query("SELECT id, nombre FROM provincias ORDER BY nombre");

// 4) Incluir cabecera de administrador
include 'header_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Ítems</title>
  <link rel="stylesheet" href="styles.css">

  <!-- Incluir jQuery -->
  <script src="jquery-1.11.3.min.js"></script>
</head>
<body class="bg-admin">

  <h1>Gestión de Ítems</h1>

  <div class="listado-items">
    <h2>Menú de ítems</h2>
    <ul class="menu">
      <li><a href="paises.php">País</a></li>
      <li><a href="tipoActividad.php">Actividad</a></li>
      <li><a href="provincias.php">Provincia</a></li>
      <li><a href="localidades.php">Localidad</a></li>
    </ul>
  </div>

  <!-- === Alta País === -->
  <div class="alta-item">
    <h2>Alta País</h2>
    <form method="post" action="g_items.php">
      <input type="hidden" name="item_type" value="pais">
      <div class="campo">
        <label>Nombre del país:</label>
        <input type="text" name="name" required>
      </div>
      <button class="btn" type="submit">Guardar País</button>
    </form>
  </div>

  <!-- === Alta Tipo de Actividad === -->
  <div class="alta-item">
    <h2>Alta Tipo de Actividad</h2>
    <form method="post" action="g_items.php">
      <input type="hidden" name="item_type" value="actividad">
      <div class="campo">
        <label>Nombre de la actividad:</label>
        <input type="text" name="name" required>
      </div>
      <button class="btn" type="submit">Guardar Actividad</button>
    </form>
  </div>

  <!-- === Alta Provincia === -->
  <div class="alta-item">
    <h2>Alta Provincia</h2>
    <form method="post" action="g_items.php">
      <input type="hidden" name="item_type" value="provincia">
      <div class="campo">
        <label>Nombre de la provincia:</label>
        <input type="text" name="name" required>
      </div>
      <div class="campo">
        <label>País:</label>
        <select name="pais_id" required>
          <option value="">-- Selecciona país --</option>
          <?php while ($fila = $resPaises->fetch_assoc()): ?>
            <option value="<?= $fila['id'] ?>"><?= $fila['nombre'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <button class="btn" type="submit">Guardar Provincia</button>
    </form>
  </div>

  <!-- === Alta Localidad (con AJAX) === -->
  <div class="alta-item">
    <h2>Alta Localidad</h2>
    <form method="post" action="g_items.php">
      <input type="hidden" name="item_type" value="localidad">

      <div class="campo">
        <label>Nombre de la localidad:</label>
        <input type="text" name="name" required>
      </div>

      <!-- Desplegable de País -->
      <div class="campo">
        <label>País:</label>
        <select id="pais_select" name="pais_id" required>
          <option value="">-- Selecciona país --</option>
          <?php
            // Volvemos a consultar países para este select
            $resP = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre");
            while ($p = $resP->fetch_assoc()):
          ?>
            <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Desplegable de Provincia (se llenará vía AJAX) -->
      <div class="campo">
        <label>Provincia:</label>
        <select id="prov_select" name="provincia_id" required>
          <option value="">-- Primero elige país --</option>
        </select>
      </div>

      <button class="btn" type="submit">Guardar Localidad</button>
    </form>
  </div>

  <script>
    
    $('#pais_select').on('change', function() {
      var paisId = $(this).val();

      if (paisId === '') {
        // Si no hay país, dejamos el select de provincias vacío
        $('#prov_select').html('<option value="">-- Primero elige país --</option>');
        return;
      }

      // AJAX con GET a obtenerProvincias.php?pais_id=...
      $.getJSON('obtenerProvincias.php', { pais_id: paisId })
        .done(function(data) {
          var html = '<option value="">-- Selecciona provincia --</option>';
          for (var i = 0; i < data.length; i++) {
            html += '<option value="' + data[i].id + '">' + data[i].nombre + '</option>';
          }
          $('#prov_select').html(html);
        })
        .fail(function() {
          $('#prov_select').html('<option value="">Error al cargar provincias</option>');
        });
    });
  </script>

</body>
</html>
