<?php
declare(strict_types=1);
require_once __DIR__ . '/funciones.php';
$pantalla = prepararPantalla('categorias');
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
            <form method="post" action="categorias.php">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id" value="<?= h($pantalla['editarId']) ?>">
                <input type="hidden" name="csrf" value="<?= h($pantalla['csrf']) ?>">
                <div class="formulario">
        <div class="campo">
            <label for="nombre">Nombre de la categoría</label>
            <input type="text" id="nombre" name="nombre" maxlength="70" value="<?= h((string) ($pantalla['datos']['nombre'] ?? '')) ?>" required>
        </div>
        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" maxlength="200" required><?= h((string) ($pantalla['datos']['descripcion'] ?? '')) ?></textarea>
        </div>
        <div class="campo">
            <label for="prioridad">Prioridad</label>
            <select id="prioridad" name="prioridad" required>
                <option value="">Selecciona una opción</option>
                <option value="Baja" <?= (string) ($pantalla['datos']['prioridad'] ?? '') === 'Baja' ? 'selected' : '' ?>>Baja</option>
                <option value="Media" <?= (string) ($pantalla['datos']['prioridad'] ?? '') === 'Media' ? 'selected' : '' ?>>Media</option>
                <option value="Alta" <?= (string) ($pantalla['datos']['prioridad'] ?? '') === 'Alta' ? 'selected' : '' ?>>Alta</option>
            </select>
        </div>
        <div class="campo">
            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
                <option value="">Selecciona una opción</option>
                <option value="Activo" <?= (string) ($pantalla['datos']['estado'] ?? '') === 'Activo' ? 'selected' : '' ?>>Activo</option>
                <option value="Inactivo" <?= (string) ($pantalla['datos']['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
                </div>
                <div class="acciones">
                    <button type="submit"><?= $pantalla['editarId'] !== null ? 'Guardar cambios' : 'Guardar' ?></button>
                    <?php if ($pantalla['editarId'] !== null): ?>
                        <a class="boton secundario" href="categorias.php">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php if ($pantalla['confirmar'] !== null): ?>
            <section class="tarjeta confirmar">
                <h3>Confirmar eliminación</h3>
                <p>¿Deseas eliminar el registro N.º <?= h($pantalla['confirmar']['id_categoria']) ?>?</p>
                <form method="post" action="categorias.php" class="acciones">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= h($pantalla['confirmar']['id_categoria']) ?>">
                    <input type="hidden" name="csrf" value="<?= h($pantalla['csrf']) ?>">
                    <button type="submit" class="peligro">Sí, eliminar</button>
                    <a class="boton secundario" href="categorias.php">Cancelar</a>
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
                    <th>Nombre de la categoría</th>
                    <th>Descripción</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pantalla['registros'] as $registro): ?>
                        <tr>
                            <td><?= h($registro['id_categoria']) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'nombre', $registro['nombre'])) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'descripcion', $registro['descripcion'])) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'prioridad', $registro['prioridad'])) ?></td>
                    <td><?= h(mostrarValor($pantalla, 'estado', $registro['estado'])) ?></td>
                            <td class="opciones-tabla">
                                <a href="categorias.php?editar=<?= h($registro['id_categoria']) ?>">Editar</a>
                                <a class="eliminar" href="categorias.php?eliminar=<?= h($registro['id_categoria']) ?>">Eliminar</a>
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