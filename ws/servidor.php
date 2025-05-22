<?php

include __DIR__ . '/../db.php';   // conexion a la base de datos


/* Función que el servicio SOAP expondrá */
function obtenerUsuarios($nombre = '', $apellidos = '')
{
    /* 1. Construir la consulta */
    $sql = "
      SELECT
        u.nombre,
        u.apellidos,
        (SELECT ta.nombre
         FROM   tipo_actividad ta
         WHERE  ta.id = u.tipo_actividad_id) AS actividad_preferida
      FROM usuario u
      WHERE 1 = 1  
    "; //Añadimos el where 1=1 para facilitar la concatenación de filtros

    /* Añadir filtros sólo si el usuario los ha escrito */
    if ($nombre    !== '') $sql .= " AND u.nombre    LIKE '%$nombre%'";
    if ($apellidos !== '') $sql .= " AND u.apellidos LIKE '%$apellidos%'";

    /* 2. Ejecutar y recopilar resultados */
    $res = $GLOBALS['mysqli']->query($sql); 



    $usuarios = [];
    while ($fila = $res->fetch_assoc()) { //Recorremos los resultados y los guardamos en un array 
        $usuarios[] = $fila;
    }
    return $usuarios;   // PHP lo convertirá a XML‑SOAP
}


/* 3. Crear el servidor SOAP */
$server = new SoapServer(null, ['uri' => 'http://localhost/PracticaWeb/ws/servidor.php']);  // URI de la aplicación
$server->addFunction('obtenerUsuarios');  // Añadir la función al servidor
$server->handle();  // Manejar la petición SOAP
