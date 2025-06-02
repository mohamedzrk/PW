<?php
// g_items.php
include 'db.php';
session_start();

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: identificacion.php');
    exit;
}


// 1) Procesar formulario de alta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['item_type'];      // 'pais', 'actividad', 'provincia' o 'localidad'
    $name = trim($_POST['name']);     // Nombre del ítem

    if ($name !== '') {
        $nameEsc = $mysqli->real_escape_string($name);

        switch ($type) {
            case 'pais':
                $mysqli->query("INSERT INTO paises (nombre) VALUES ('$nameEsc')");
                break;

            case 'actividad':
                $mysqli->query("INSERT INTO tipo_actividad (nombre) VALUES ('$nameEsc')");
                break;

            case 'provincia':
                $paisId = (int)$_POST['pais_id'];
                $mysqli->query(
                    "INSERT INTO provincias (nombre, pais_id)
                     VALUES ('$nameEsc', $paisId)"
                );
                break;

            case 'localidad':
                $provId = (int)$_POST['provincia_id'];
                $mysqli->query(
                    "INSERT INTO localidades (nombre, provincia_id)
                     VALUES ('$nameEsc', $provId)"
                );
                break;
        }
    }

    // Volver a cargar la página para ver el nuevo ítem
    header('Location: g_items.php');
    exit;
}

// 2) Cargar datos para los selects
$listaPaises     = $mysqli->query("SELECT id, nombre FROM paises ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$listaProvincias = $mysqli->query("SELECT id, nombre FROM provincias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

include 'header_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Ítems</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-admin">
  <h1>Gestión de Ítems</h1>

  <div class="listado-items">
    <h2>Listado de Ítems</h2>
    <ul class="menu">
      <li><a href="paises.php">País</a></li>
      <li><a href="tipoActividad.php">Actividad</a></li>
      <li><a href="provincias.php">Provincia</a></li>
      <li><a href="localidades.php">Localidad</a></li>
    </ul>
  </div>

  <!-- Alta País -->
  <div id="alta-pais" class="alta-item">
    <h2>Alta País</h2>
    <form method="post">
      <input type="hidden" name="item_type" value="pais">
      <div class="campo">
        <label>Nombre del país:</label>
        <input type="text" name="name" required>
      </div>
      <button class="btn">Guardar País</button>
    </form>
  </div>

  <!-- Alta Tipo de Actividad -->
  <div id="alta-actividad" class="alta-item">
    <h2>Alta Tipo de Actividad</h2>
    <form method="post">
      <input type="hidden" name="item_type" value="actividad">
      <div class="campo">
        <label>Nombre de la actividad:</label>
        <input type="text" name="name" required>
      </div>
      <button class="btn">Guardar Actividad</button>
    </form>
  </div>

  <!-- Alta Provincia -->
  <div id="alta-provincia" class="alta-item">
    <h2>Alta Provincia</h2>
    <form method="post">
      <input type="hidden" name="item_type" value="provincia">
      <div class="campo">
        <label>Nombre de la provincia:</label>
        <input type="text" name="name" required>
      </div>
      <div class="campo">
        <label>País:</label>
        <select name="pais_id" required>
          <option value="">-- Selecciona país --</option>
          <?php foreach ($listaPaises as $p): ?>
            <option value="<?= $p['id'] ?>"><?= $p['nombre'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn">Guardar Provincia</button>
    </form>
  </div>

  <!-- Alta Localidad -->
  <div id="alta-localidad" class="alta-item">
    <h2>Alta Localidad</h2>
    <form method="post">
      <input type="hidden" name="item_type" value="localidad">
      <div class="campo">
        <label>Nombre de la localidad:</label>
        <input type="text" name="name" required>
      </div>
      <div class="campo">
        <label>Provincia:</label>
        <select name="provincia_id" required>
          <option value="">-- Selecciona provincia --</option>
          <?php foreach ($listaProvincias as $pr): ?>
            <option value="<?= $pr['id'] ?>"><?= $pr['nombre'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn">Guardar Localidad</button>
    </form>
  </div>
</body>
</html>
