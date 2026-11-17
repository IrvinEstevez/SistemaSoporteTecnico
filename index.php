<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';

$contadores = [];
$mensajeError = '';
try {
    $pdo = conectar();
    foreach (entidades() as $nombre => $_) {
        $contadores[$nombre] = (int) $pdo->query("SELECT COUNT(*) FROM `$nombre`")->fetchColumn();
    }
} catch (PDOException) {
    $mensajeError = 'No se pudo conectar a la base';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Soporte Técnico</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <h1>SISTEMA DE SOPORTE TÉCNICO</h1>
        <p>Registro de equipos e incidencias</p>
    </header>
    <nav>
        <a class="activo" href="index.php">Inicio</a>
        <?php foreach (entidades() as $nombre => $config): ?>
            <a href="<?= h($nombre) ?>.php"><?= h($config['titulo']) ?></a>
        <?php endforeach; ?>
    </nav>
    <main>
        <h2>Bienvenido al sistema</h2>
        <p class="intro">Selecciona uno de los módulos para registrar, consultar, editar o eliminar información.</p>
        <?php if ($mensajeError): ?>
            <p class="mensaje error"><?= h($mensajeError) ?></p>
        <?php endif; ?>
        <div class="tarjetas-inicio">
            <?php foreach (entidades() as $nombre => $config): ?>
                <a class="tarjeta resumen" href="<?= h($nombre) ?>.php">
                    <h3><?= h($config['titulo']) ?></h3>
                    <strong><?= h($contadores[$nombre] ?? '—') ?></strong>
                    <span>Ver registros</span>
                </a>
            <?php endforeach; ?>
        </div>
        <section class="tarjeta">
            <h3>Orden recomendado</h3>
            <p>Primero registra <b>Usuarios</b> y <b>Categorías</b>. Después agrega <b>Equipos</b>, <b>Incidencias</b> y <b>Seguimientos</b>, en ese orden.</p>
        </section>
    </main>
    
</body>
</html>
