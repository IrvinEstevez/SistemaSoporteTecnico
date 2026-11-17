<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';
$pantalla = prepararPantalla('incidencias');
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
            <form method="post" action="incidencias.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id" value="<?= h($pantalla['editarId']) ?>">
                <input type="hidden" name="csrf" value="<?= h($pantalla['csrf']) ?>">
                <div class="formulario">
        <div class="campo">
            <label for="id_equipo">Equipo</label>
            <select id="id_equipo" name="id_equipo" required>
                <option value="">Selecciona un registro</option>
                <?php foreach ($pantalla['listas']['id_equipo'] as $opcionId => $opcionTexto): ?>
                    <option value="<?= h($opcionId) ?>" <?= (string) ($pantalla['datos']['id_equipo'] ?? '') === (string) $opcionId ? 'selected' : '' ?>><?= h($opcionTexto) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="<?= h((string) ($pantalla['datos']['fecha'] ?? '')) ?>" required>
        </div>
        <div class="campo">
            <label for="descripcion">Descripción del problema</label>
            <textarea id="descripcion" name="descripcion" maxlength="300" required><?= h((string) ($pantalla['datos']['descripcion'] ?? '')) ?></textarea>
        </div>
        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
                <option value="">Selecciona una opción</option>
                <option value="Pendiente" <?= (string) ($pantalla['datos']['estado'] ?? '') === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                <option value="En proceso" <?= (string) ($pantalla['datos']['estado'] ?? '') === 'En proceso' ? 'selected' : '' ?>>En proceso</option>
                <option value="Resuelta" <?= (string) ($pantalla['datos']['estado'] ?? '') === 'Resuelta' ? 'selected' : '' ?>>Resuelta</option>
            </select>
        </div>
                </div>
                <div class="acciones">
                    <button type="submit"><?= $pantalla['editarId'] !== null ? 'Guardar cambios' : 'Guardar' ?></button>
                    <?php if ($pantalla['editarId'] !== null): ?>
                        <a class="boton secundario" href="incidencias.php">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php if ($pantalla['confirmar'] !== null): ?>
            <section class="tarjeta confirmar">
                <h3>Confirmar eliminación</h3>
                <p>¿Deseas eliminar el registro N.º <?= h($pantalla['confirmar']['id_incidencia']) ?>?</p>
                <form method="post" action="incidencias.php" class="acciones">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= h($pantalla['confirmar']['id_incidencia']) ?>">
                    <input type="hidden" name="csrf" value="<?= h($pantalla['csrf']) ?>">
                    <button type="submit" class="peligro">Sí, eliminar</button>
                    <a class="boton secundario" href="incidencias.php">Cancelar</a>
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
                    <th>Equipo</th>
                    <th>Fecha</th>
                    <th>Descripción del problema</th>
                    <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pantalla['registros'] as $registro): ?>
                        <tr>
                            <td><?= h($registro['id_incidencia']) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'id_equipo', $registro['id_equipo'])) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'fecha', $registro['fecha'])) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'descripcion', $registro['descripcion'])) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'estado', $registro['estado'])) ?></td>
                            <td class="opciones-tabla">
                                <a href="incidencias.php?editar=<?= h($registro['id_incidencia']) ?>">Editar</a>
                                <a class="eliminar" href="incidencias.php?eliminar=<?= h($registro['id_incidencia']) ?>">Eliminar</a>
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