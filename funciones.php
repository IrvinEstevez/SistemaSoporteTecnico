<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

function entidades(): array
{
    return [
        'usuarios' => [
            'titulo' => 'Usuarios', 'id' => 'id_usuario',
            'campos' => [
                'nombre' => ['etiqueta' => 'Nombre', 'tipo' => 'text', 'max' => 90],
                'correo' => ['etiqueta' => 'Correo electrónico', 'tipo' => 'email', 'max' => 120],
                'area' => ['etiqueta' => 'Área', 'tipo' => 'text', 'max' => 80],
                'estado' => ['etiqueta' => 'Estado', 'tipo' => 'opciones', 'opciones' => ['Activo', 'Inactivo']],
            ],
        ],
        'categorias' => [
            'titulo' => 'Categorías', 'id' => 'id_categoria',
            'campos' => [
                'nombre' => ['etiqueta' => 'Nombre de la categoría', 'tipo' => 'text', 'max' => 70],
                'descripcion' => ['etiqueta' => 'Descripción', 'tipo' => 'textarea', 'max' => 200],
                'prioridad' => ['etiqueta' => 'Prioridad', 'tipo' => 'opciones', 'opciones' => ['Baja', 'Media', 'Alta']],
                'estado' => ['etiqueta' => 'Estado', 'tipo' => 'opciones', 'opciones' => ['Activo', 'Inactivo']],
            ],
        ],
        'equipos' => [
            'titulo' => 'Equipos', 'id' => 'id_equipo',
            'campos' => [
                'id_usuario' => ['etiqueta' => 'Usuario responsable', 'tipo' => 'relacion', 'tabla' => 'usuarios', 'clave' => 'id_usuario', 'texto' => 'nombre'],
                'id_categoria' => ['etiqueta' => 'Categoría', 'tipo' => 'relacion', 'tabla' => 'categorias', 'clave' => 'id_categoria', 'texto' => 'nombre'],
                'codigo' => ['etiqueta' => 'Código del equipo', 'tipo' => 'text', 'max' => 30],
                'estado' => ['etiqueta' => 'Estado', 'tipo' => 'opciones', 'opciones' => ['Operativo', 'En reparación', 'Baja']],
            ],
        ],
        'incidencias' => [
            'titulo' => 'Incidencias', 'id' => 'id_incidencia',
            'campos' => [
                'id_equipo' => ['etiqueta' => 'Equipo', 'tipo' => 'relacion', 'tabla' => 'equipos', 'clave' => 'id_equipo', 'texto' => 'codigo'],
                'fecha' => ['etiqueta' => 'Fecha', 'tipo' => 'date'],
                'descripcion' => ['etiqueta' => 'Descripción del problema', 'tipo' => 'textarea', 'max' => 300],
                'estado' => ['etiqueta' => 'Estado', 'tipo' => 'opciones', 'opciones' => ['Pendiente', 'En proceso', 'Resuelta']],
            ],
        ],
        'seguimientos' => [
            'titulo' => 'Seguimientos', 'id' => 'id_seguimiento',
            'campos' => [
                'id_incidencia' => ['etiqueta' => 'Incidencia', 'tipo' => 'relacion', 'tabla' => 'incidencias', 'clave' => 'id_incidencia', 'texto' => 'descripcion'],
                'fecha' => ['etiqueta' => 'Fecha', 'tipo' => 'date'],
                'observacion' => ['etiqueta' => 'Observación', 'tipo' => 'textarea', 'max' => 300],
                'estado' => ['etiqueta' => 'Estado', 'tipo' => 'opciones', 'opciones' => ['Registrado', 'Finalizado']],
            ],
        ],
    ];
}

function h(string|int|float|null $texto): string
{
    return htmlspecialchars((string) ($texto ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

interface Crud
{
    public function todos(): array;
    public function uno(int $id): ?array;
    public function crear(array $datos): void;
    public function actualizar(int $id, array $datos): void;
    public function eliminar(int $id): void;
}

final class Repositorio implements Crud
{
    private string $tabla;
    private string $clave;
    private array $campos;

    public function __construct(private PDO $pdo, string $entidad)
    {
        $configuracion = entidades()[$entidad] ?? throw new InvalidArgumentException('Entidad desconocida');
        $this->tabla = $entidad;
        $this->clave = $configuracion['id'];
        $this->campos = array_keys($configuracion['campos']);
    }

    public function todos(): array
    {
        return $this->pdo->query("SELECT * FROM `{$this->tabla}` ORDER BY `{$this->clave}` DESC")->fetchAll();
    }

    public function uno(int $id): ?array
    {
        $consulta = $this->pdo->prepare("SELECT * FROM `{$this->tabla}` WHERE `{$this->clave}` = ?");
        $consulta->execute([$id]);
        return $consulta->fetch() ?: null;
    }

    public function crear(array $datos): void
    {
        $columnas = implode(', ', array_map(fn(string $nombre) => "`$nombre`", $this->campos));
        $marcadores = implode(', ', array_fill(0, count($this->campos), '?'));
        $consulta = $this->pdo->prepare("INSERT INTO `{$this->tabla}` ($columnas) VALUES ($marcadores)");
        $consulta->execute(array_map(fn(string $campo) => $datos[$campo], $this->campos));
    }

    public function actualizar(int $id, array $datos): void
    {
        $asignaciones = implode(', ', array_map(fn(string $nombre) => "`$nombre` = ?", $this->campos));
        $consulta = $this->pdo->prepare("UPDATE `{$this->tabla}` SET $asignaciones WHERE `{$this->clave}` = ?");
        $valores = array_map(fn(string $campo) => $datos[$campo], $this->campos);
        $valores[] = $id;
        $consulta->execute($valores);
    }

    public function eliminar(int $id): void
    {
        $consulta = $this->pdo->prepare("DELETE FROM `{$this->tabla}` WHERE `{$this->clave}` = ?");
        $consulta->execute([$id]);
    }
}

function validarDatos(array $campos, array $entrada): array
{
    $datos = [];
    foreach ($campos as $nombre => $definicion) {
        $valorBruto = $entrada[$nombre] ?? '';
        if (!is_string($valorBruto)) {
            throw new InvalidArgumentException('Los datos enviados no son válidos.');
        }
        $valor = trim($valorBruto);
        if ($valor === '') {
            throw new InvalidArgumentException('Debes completar el campo ' . $definicion['etiqueta'] . '.');
        }
        $tipo = $definicion['tipo'];
        if ($tipo === 'email' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ingresa un correo electrónico válido.');
        }
        if ($tipo === 'opciones' && !in_array($valor, $definicion['opciones'], true)) {
            throw new InvalidArgumentException('Selecciona una opción válida en ' . $definicion['etiqueta'] . '.');
        }
        if ($tipo === 'relacion' && (!ctype_digit($valor) || (int) $valor < 1)) {
            throw new InvalidArgumentException('Selecciona un registro válido en ' . $definicion['etiqueta'] . '.');
        }
        if ($tipo === 'date') {
            $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
            if ($fecha === false || $fecha->format('Y-m-d') !== $valor) {
                throw new InvalidArgumentException('La fecha no es válida.');
            }
        }
        if (isset($definicion['max'])) {
            $longitud = function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
            if ($longitud > $definicion['max']) {
                throw new InvalidArgumentException($definicion['etiqueta'] . ' supera el tamaño permitido.');
            }
        }
        $datos[$nombre] = $tipo === 'relacion' ? (int) $valor : $valor;
    }
    return $datos;
}

function listasRelacion(PDO $pdo, array $campos): array
{
    $listas = [];
    foreach ($campos as $nombre => $definicion) {
        if ($definicion['tipo'] !== 'relacion') {
            continue;
        }
        $tabla = $definicion['tabla'];
        $clave = $definicion['clave'];
        $texto = $definicion['texto'];
        $filas = $pdo->query("SELECT `$clave`, `$texto` FROM `$tabla` ORDER BY `$texto`")->fetchAll();
        $listas[$nombre] = [];
        foreach ($filas as $fila) {
            $listas[$nombre][(int) $fila[$clave]] = (string) $fila[$texto];
        }
    }
    return $listas;
}

function mostrarValor(array $pantalla, string $nombre, mixed $valor): string
{
    if (isset($pantalla['listas'][$nombre])) {
        return $pantalla['listas'][$nombre][(int) $valor] ?? ('ID: ' . (string) $valor);
    }
    return (string) $valor;
}

function prepararPantalla(string $entidad): array
{
    $config = entidades()[$entidad] ?? throw new InvalidArgumentException('Entidad desconocida');
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['csrf'] ??= bin2hex(random_bytes(24));

    try {
        $pdo = conectar();
        $repositorio = new Repositorio($pdo, $entidad);
        $listas = listasRelacion($pdo, $config['campos']);
    } catch (PDOException) {
        http_response_code(500);
        exit('No se pudo conectar a la base de datos');
    }

    $datos = array_fill_keys(array_keys($config['campos']), '');
    $editarId = null;
    $confirmar = null;
    $error = '';
    $mensajes = ['creado' => 'Registro guardado correctamente.', 'editado' => 'Registro actualizado correctamente.', 'eliminado' => 'Registro eliminado correctamente.'];
    $codigoMensaje = $_GET['ok'] ?? '';
    $mensaje = is_string($codigoMensaje) ? ($mensajes[$codigoMensaje] ?? '') : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tokenRecibido = $_POST['csrf'] ?? '';
        try {
            if (!is_string($tokenRecibido) || !hash_equals($_SESSION['csrf'], $tokenRecibido)) {
                throw new InvalidArgumentException('La sesión del formulario no es válida. Recarga la página.');
            }
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'guardar') {
                foreach ($config['campos'] as $campo => $_) {
                    $datos[$campo] = is_string($_POST[$campo] ?? '') ? $_POST[$campo] : '';
                }
                $idTexto = $_POST['id'] ?? '';
                $id = $idTexto === '' ? null : filter_var($idTexto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id === false) {
                    throw new InvalidArgumentException('Identificador no válido.');
                }
                $editarId = $id;
                $validados = validarDatos($config['campos'], $_POST);
                if ($id !== null) {
                    if ($repositorio->uno($id) === null) {
                        throw new InvalidArgumentException('El registro ya no existe.');
                    }
                    $repositorio->actualizar($id, $validados);
                    $resultado = 'editado';
                } else {
                    $repositorio->crear($validados);
                    $resultado = 'creado';
                }
            } elseif ($accion === 'eliminar') {
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($id === false || $id === null || $repositorio->uno($id) === null) {
                    throw new InvalidArgumentException('El registro no existe.');
                }
                $repositorio->eliminar($id);
                $resultado = 'eliminado';
            } else {
                throw new InvalidArgumentException('Acción no válida.');
            }
            header('Location: ' . $entidad . '.php?ok=' . $resultado, true, 303);
            exit;
        } catch (InvalidArgumentException $excepcion) {
            $error = $excepcion->getMessage();
        } catch (PDOException $excepcion) {
            $error = $excepcion->getCode() === '23000'
                ? 'No se puede realizar la operación: existe un valor duplicado o un registro relacionado.'
                : 'No se pudo completar la operación. Revisa la conexión y la base de datos.';
        }
    } else {
        $editar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $eliminar = filter_input(INPUT_GET, 'eliminar', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (is_int($editar)) {
            $registro = $repositorio->uno($editar);
            if ($registro !== null) {
                $datos = $registro;
                $editarId = $editar;
            } else {
                $error = 'El registro solicitado no existe.';
            }
        }
        if (is_int($eliminar)) {
            $confirmar = $repositorio->uno($eliminar);
        }
    }

    return [
        'config' => $config,
        'entidad' => $entidad,
        'datos' => $datos,
        'editarId' => $editarId,
        'confirmar' => $confirmar,
        'registros' => $repositorio->todos(),
        'listas' => $listas,
        'csrf' => $_SESSION['csrf'],
        'mensaje' => $mensaje,
        'error' => $error,
    ];
}
