<?php
// Autor: A. Cambronero Blogpocket.com
// Verifica si el parámetro 'year' está establecido en la URL y es un número válido
if (isset($_GET['year']) && is_numeric($_GET['year'])) {
    $anioObjetivo = intval($_GET['year']);
} else {
    // Si no está establecido o no es numérico, muestra un mensaje de error y termina la ejecución
    echo "Error: Debes proporcionar un año válido en el parámetro 'year' de la URL. Por ejemplo: script.php?year=2024";
    exit(1);
}

// Especifica los archivos de entrada y salida
$archivoEntrada = 'tweet-headers-copia.js';  // Reemplaza con la ruta a tu archivo de entrada
$archivoSalida = 'tweet-headers-salida-'.$anioObjetivo;    // Reemplaza con la ruta a tu archivo de salida

// Lee el contenido del archivo de entrada
$contenido = file_get_contents($archivoEntrada);

// Verifica si la lectura del archivo fue exitosa
if ($contenido === false) {
    echo "Error al leer el archivo de entrada.<br>";
    exit(1);
}

// Extrae el contenido JSON buscando las posiciones de '[' y ']'
$posInicioJson = strpos($contenido, '[');
$posFinJson = strrpos($contenido, ']');

if ($posInicioJson === false || $posFinJson === false) {
    echo "Formato de archivo inválido: No se encontró el array JSON.<br>";
    exit(1);
}

// Incluye el corchete de cierre en la subcadena
$contenidoJson = substr($contenido, $posInicioJson, $posFinJson - $posInicioJson + 1);

// Decodifica el contenido JSON en un array asociativo
$datos = json_decode($contenidoJson, true);

if ($datos === null) {
    echo "Error al decodificar los datos JSON: " . json_last_error_msg() . "<br>";
    exit(1);
}

// Inicializa un array para almacenar los registros filtrados
$datosFiltrados = array();

// Recorre cada registro y filtra por el año objetivo
foreach ($datos as $item) {
    if (isset($item['tweet']['created_at'])) {
        $fechaCreacion = $item['tweet']['created_at'];
        // Extrae el año del campo 'created_at'
        // Formato: "Sun Nov 03 09:52:41 +0000 2024"
        $partesFecha = explode(' ', $fechaCreacion);
        $anio = end($partesFecha);

        if ($anio == $anioObjetivo) {
            $datosFiltrados[] = $item;
        }
    }
}

// Contar el número de registros filtrados
$numRegistros = count($datosFiltrados);

// Prepara el contenido de salida sin el punto y coma al final
$contenidoSalida = "window.YTD.tweet_headers.part0 = " . json_encode($datosFiltrados, JSON_PRETTY_PRINT);

// Escribe los datos filtrados en el archivo de salida
if (file_put_contents($archivoSalida, $contenidoSalida) === false) {
    echo "Error al escribir en el archivo de salida.<br>";
    exit(1);
}

// Muestra el resultado en pantalla
echo "Los datos filtrados se han escrito correctamente en $archivoSalida.<br>";
echo "Se han encontrado $numRegistros registros para el año $anioObjetivo.<br>";

?>
