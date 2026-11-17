<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ejecuta este generador desde la terminal de PHP.');
}

require_once __DIR__ . '/funciones.php';

function crearCampo(string $nombre, array $definicion): string
{
    $etiqueta = htmlspecialchars($definicion['etiqueta'], ENT_QUOTES, 'UTF-8');
    $tipo = $definicion['tipo'];
    $inicio = "        <div class=\"campo\">\n            <label for=\"$nombre\">$etiqueta</label>\n";
    $fin = "        </div>\n";

    if ($tipo === 'textarea') {
        return $inicio . '            <textarea id="' . $nombre . '" name="' . $nombre . '" maxlength="' . $definicion['max'] . '" required><?= h((string) ($pantalla[\'datos\'][\'' . $nombre . '\'] ?? \'\')) ?></textarea>' . "\n" . $fin;
    }
    if ($tipo === 'opciones') {
        $html = $inicio . '            <select id="' . $nombre . '" name="' . $nombre . '" required>' . "\n";
        $html .= '                <option value="">Selecciona una opción</option>' . "\n";
        foreach ($definicion['opciones'] as $opcion) {
            $valor = htmlspecialchars($opcion, ENT_QUOTES, 'UTF-8');
            $html .= '                <option value="' . $valor . '" <?= (string) ($pantalla[\'datos\'][\'' . $nombre . '\'] ?? \'\') === \'' . $opcion . '\' ? \'selected\' : \'\' ?>>' . $valor . '</option>' . "\n";
        }
        return $html . "            </select>\n" . $fin;
    }
    if ($tipo === 'relacion') {
        $html = $inicio . '            <select id="' . $nombre . '" name="' . $nombre . '" required>' . "\n";
        $html .= '                <option value="">Selecciona un registro</option>' . "\n";
        $html .= '                <?php foreach ($pantalla[\'listas\'][\'' . $nombre . '\'] as $opcionId => $opcionTexto): ?>' . "\n";
        $html .= '                    <option value="<?= h($opcionId) ?>" <?= (string) ($pantalla[\'datos\'][\'' . $nombre . '\'] ?? \'\') === (string) $opcionId ? \'selected\' : \'\' ?>><?= h($opcionTexto) ?></option>' . "\n";
        $html .= '                <?php endforeach; ?>' . "\n";
        return $html . "            </select>\n" . $fin;
    }
    $max = isset($definicion['max']) ? ' maxlength="' . $definicion['max'] . '"' : '';
    return $inicio . '            <input type="' . $tipo . '" id="' . $nombre . '" name="' . $nombre . '"' . $max . ' value="<?= h((string) ($pantalla[\'datos\'][\'' . $nombre . '\'] ?? \'\')) ?>" required>' . "\n" . $fin;
}

function crearTablaCampos(array $config): array
{
    $encabezados = '';
    $celdas = '';
    foreach ($config['campos'] as $nombre => $definicion) {
        $etiqueta = htmlspecialchars($definicion['etiqueta'], ENT_QUOTES, 'UTF-8');
        $encabezados .= "                    <th>$etiqueta</th>\n";
        $celdas .= '                    <td><?= h(mostrarValor($pantalla, \'' . $nombre . '\', $registro[\'' . $nombre . '\'])) ?></td>' . "\n";
    }
    return [$encabezados, $celdas];
}

$plantilla = <<<'HTML'
<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';
$pantalla = prepararPantalla('%%ENTIDAD%%');
$config = $pantalla['config'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($config['titulo']) ?> | Soporte técnico</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <h1>SISTEMA DE SOPORTE TÉCNICO</h1>
        <p>Registro de equipos e incidencias</p>
    </header>
    <nav>
        <a href="index.php">Inicio</a>
        <?php foreach (entidades() as $clave => $seccion): ?>
            <a href="<?= h($clave) ?>.php" class="<?= $pantalla['entidad'] === $clave ? 'activo' : '' ?>"><?= h($seccion['titulo']) ?></a>
        <?php endforeach; ?>
    </nav>
    <main>
        <h2><?= h($config['titulo']) ?></h2>
        <?php if ($pantalla['mensaje']): ?>
            <p class="mensaje exito"><?= h($pantalla['mensaje']) ?></p>
        <?php endif; ?>
        <?php if ($pantalla['error']): ?>
            <p class="mensaje error"><?= h($pantalla['error']) ?></p>
        <?php endif; ?>

        <section class="tarjeta">
            <h3><?= $pantalla['editarId'] !== null ? 'Editar registro' : 'Registrar nuevo registro' ?></h3>
            <form method="post" action="%%ENTIDAD%%.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id" value="<?= h($pantalla['editarId']) ?>">
                <input type="hidden" name="csrf" value="<?= h($pantalla['csrf']) ?>">
                <div class="formulario">
%%CAMPOS%%
                </div>
                <div class="acciones">
                    <button type="submit"><?= $pantalla['editarId'] !== null ? 'Guardar cambios' : 'Guardar' ?></button>
                    <?php if ($pantalla['editarId'] !== null): ?>
                        <a class="boton secundario" href="%%ENTIDAD%%.php">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php if ($pantalla['confirmar'] !== null): ?>
            <section class="tarjeta confirmar">
                <h3>Confirmar eliminación</h3>
                <p>¿Deseas eliminar el registro N.º <?= h($pantalla['confirmar']['%%ID%%']) ?>?</p>
                <form method="post" action="%%ENTIDAD%%.php" class="acciones">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= h($pantalla['confirmar']['%%ID%%']) ?>">
                    <input type="hidden" name="csrf" value="<?= h($pantalla['csrf']) ?>">
                    <button type="submit" class="peligro">Sí, eliminar</button>
                    <a class="boton secundario" href="%%ENTIDAD%%.php">Cancelar</a>
                </form>
            </section>
        <?php endif; ?>

        <section class="tarjeta">
            <h3>Registros guardados</h3>
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
%%ENCABEZADOS%%
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pantalla['registros'] as $registro): ?>
                        <tr>
                            <td><?= h($registro['%%ID%%']) ?></td>
%%CELDAS%%
                            <td class="opciones-tabla">
                                <a href="%%ENTIDAD%%.php?editar=<?= h($registro['%%ID%%']) ?>">Editar</a>
                                <a class="eliminar" href="%%ENTIDAD%%.php?eliminar=<?= h($registro['%%ID%%']) ?>">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (!$pantalla['registros']): ?>
                    <p class="vacio">Todavía no hay registros. Utiliza el formulario para agregar el primero.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>Aplicaciones Web · Semana 4 · PHP y MySQL</footer>
</body>
</html>
HTML;

foreach (entidades() as $entidad => $config) {
    $campos = '';
    foreach ($config['campos'] as $nombre => $definicion) {
        $campos .= crearCampo($nombre, $definicion);
    }
    [$encabezados, $celdas] = crearTablaCampos($config);
    $contenido = strtr($plantilla, [
        '%%ENTIDAD%%' => $entidad,
        '%%ID%%' => $config['id'],
        '%%CAMPOS%%' => rtrim($campos),
        '%%ENCABEZADOS%%' => rtrim($encabezados),
        '%%CELDAS%%' => rtrim($celdas),
    ]);
    $ruta = __DIR__ . '/' . $entidad . '.php';
    if (file_put_contents($ruta, $contenido) === false) {
        throw new RuntimeException("No se pudo escribir: $ruta");
    }
    echo 'Generado: ' . basename($ruta) . PHP_EOL;
}
