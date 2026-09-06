<?php
/**
 * Controlador de incidencias.
 *
 * Responsabilidad: recibir las acciones del usuario, validar en servidor,
 * pedir los datos a los modelos y decidir qué Vista se muestra.
 * No escribe HTML ni consultas SQL.
 */

declare(strict_types=1);

require_once RUTA_BASE . '/config/conexion.php';

class IncidenciaController
{
    /** Catálogo de prioridades válidas para el formulario. */
    private const PRIORIDADES = ['Baja', 'Media', 'Alta', 'Crítica'];

    /** Clave de sesión usada para el mensaje flash entre redirecciones. */
    private const CLAVE_MENSAJE = 'mensaje';

    /** Ruta absoluta de la carpeta de vistas. */
    private string $vistas;

    /** Conexión PDO compartida para toda la petición. */
    private PDO $db;

    private Incidencia $incidencia;

    private Categoria $categoria;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->vistas = RUTA_BASE . '/views';

        require_once RUTA_BASE . '/models/Incidencia.php';
        require_once RUTA_BASE . '/models/Categoria.php';

        $this->db = conexion();
        $this->incidencia = new Incidencia($this->db);
        $this->categoria  = new Categoria($this->db);
    }

    /** Tablero de incidencias agrupadas por estado. */
    public function tablero(): void
    {
        $incidenciasPorEstado = $this->incidencia->obtenerPorEstado();

        $titulo = 'Tablero de incidencias';
        $this->render('incidencias/tablero', compact('titulo', 'incidenciasPorEstado'));
    }

    /** Muestra el formulario de registro. */
    public function crear(): void
    {
        $categorias  = $this->categoria->obtenerTodas();
        $prioridades = self::PRIORIDADES;

        $titulo = 'Reportar incidencia';
        $this->render('incidencias/crear', compact('titulo', 'categorias', 'prioridades'));
    }

    /** Procesa el POST del formulario e inserta mediante el modelo. */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir('crear');
            return;
        }

        $datos = [
            'titulo'       => trim((string) ($_POST['titulo'] ?? '')),
            'categoria_id' => trim((string) ($_POST['categoria_id'] ?? '')),
            'prioridad'    => trim((string) ($_POST['prioridad'] ?? '')),
            'reportante'   => trim((string) ($_POST['reportante'] ?? '')),
            'correo'       => trim((string) ($_POST['correo'] ?? '')),
            'area_codigo'  => trim((string) ($_POST['area_codigo'] ?? '')),
            'descripcion'  => trim((string) ($_POST['descripcion'] ?? '')),
        ];

        $categorias = $this->categoria->obtenerTodas();
        $errores    = $this->validarIncidencia($datos, $categorias);

        if (!empty($errores)) {
            $titulo       = 'Reportar incidencia';
            $prioridades  = self::PRIORIDADES;
            $datosPrevios = $datos;
            $this->render('incidencias/crear', compact('titulo', 'categorias', 'prioridades', 'errores', 'datosPrevios'));
            return;
        }

        try {
            $this->incidencia->crear([
                'titulo'       => $datos['titulo'],
                'descripcion'  => $datos['descripcion'],
                'categoria_id' => (int) $datos['categoria_id'],
                'prioridad'    => $datos['prioridad'],
                'reportante'   => $datos['reportante'],
                'correo'       => $datos['correo'],
                'area_codigo'  => (int) $datos['area_codigo'],
            ]);
            $this->mensaje('exito', 'Incidencia registrada correctamente.');
        } catch (PDOException $e) {
            $this->mensaje('error', 'No se pudo guardar la incidencia. Intenta nuevamente.');
        }

        $this->redirigir('tablero');
    }

    /** Consulta de registros en tabla HTML, con búsqueda opcional por el parámetro q. */
    public function listar(): void
    {
        $termino = trim((string) ($_GET['q'] ?? ''));

        $incidencias = $termino !== ''
            ? $this->incidencia->buscar($termino)
            : $this->incidencia->obtenerTodas();

        $titulo = 'Incidencias registradas';
        $this->render('incidencias/listar', compact('titulo', 'incidencias', 'termino'));
    }

    /** Actualiza el estado de una incidencia al arrastrarla en el tablero. */
    public function mover(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id     = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');

        if ($id <= 0 || !in_array($estado, Incidencia::ESTADOS, true)) {
            echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos.', 'estado' => null]);
            return;
        }

        try {
            $actualizado = $this->incidencia->cambiarEstado($id, $estado);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'mensaje' => 'No se pudo actualizar el estado.', 'estado' => null]);
            return;
        }

        echo json_encode([
            'ok'      => $actualizado,
            'mensaje' => $actualizado ? 'Estado actualizado correctamente.' : 'No se encontró la incidencia.',
            'estado'  => $actualizado ? $estado : null,
        ]);
    }

    /** Elimina una incidencia. */
    public function eliminar(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->mensaje('error', 'Identificador de incidencia inválido.');
            $this->redirigir('listar');
            return;
        }

        try {
            $eliminado = $this->incidencia->eliminar($id);
            $this->mensaje(
                $eliminado ? 'exito' : 'error',
                $eliminado ? 'Incidencia eliminada correctamente.' : 'No se encontró la incidencia a eliminar.'
            );
        } catch (PDOException $e) {
            $this->mensaje('error', 'No se pudo eliminar la incidencia.');
        }

        $this->redirigir('listar');
    }

    /** Ruta inexistente. */
    public function noEncontrado(): void
    {
        $titulo = 'Página no encontrada';
        $this->render('404', compact('titulo'));
    }

    /**
     * Comprobación visible y temporal de la conexión a MySQL.
     *
     * TODO: eliminar esta ruta y este método antes de la entrega final,
     * es solo para verificar la conexión mientras se desarrollan los
     * commits siguientes.
     */
    public function diagnostico(): void
    {
        $version = $this->db->query('SELECT VERSION() AS version')->fetch()['version'];
        $totalCategorias = (int) $this->db->query('SELECT COUNT(*) AS total FROM categorias')->fetch()['total'];
        $totalIncidencias = (int) $this->db->query('SELECT COUNT(*) AS total FROM incidencias')->fetch()['total'];

        header('Content-Type: text/plain; charset=utf-8');
        echo "Conexión a MySQL: OK\n";
        echo "Versión del servidor: {$version}\n";
        echo "Registros en categorias: {$totalCategorias}\n";
        echo "Registros en incidencias: {$totalIncidencias}\n";
    }

    /**
     * Valida en servidor los datos del formulario de registro, sin confiar
     * en las validaciones del lado del cliente.
     *
     * @param array<string, string>     $datos
     * @param array<int, array<string, mixed>> $categorias Catálogo vigente de categorías
     * @return array<string, string> Errores por campo, vacío si todo es válido
     */
    private function validarIncidencia(array $datos, array $categorias): array
    {
        $errores = [];

        if ($datos['titulo'] === '') {
            $errores['titulo'] = 'El título es obligatorio.';
        } elseif (mb_strlen($datos['titulo']) < 5 || mb_strlen($datos['titulo']) > 120) {
            $errores['titulo'] = 'El título debe tener entre 5 y 120 caracteres.';
        }

        $idsCategorias = array_column($categorias, 'id');
        if ($datos['categoria_id'] === '' || !ctype_digit($datos['categoria_id'])) {
            $errores['categoria_id'] = 'Selecciona una categoría.';
        } elseif (!in_array((int) $datos['categoria_id'], $idsCategorias, true)) {
            $errores['categoria_id'] = 'La categoría seleccionada no es válida.';
        }

        if (!in_array($datos['prioridad'], self::PRIORIDADES, true)) {
            $errores['prioridad'] = 'Selecciona una prioridad válida.';
        }

        if ($datos['reportante'] === '') {
            $errores['reportante'] = 'Indica quién reporta la incidencia.';
        } elseif (mb_strlen($datos['reportante']) > 100) {
            $errores['reportante'] = 'El nombre no puede superar los 100 caracteres.';
        }

        if ($datos['correo'] === '' || filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) === false) {
            $errores['correo'] = 'Ingresa un correo electrónico válido.';
        } elseif (mb_strlen($datos['correo']) > 150) {
            $errores['correo'] = 'El correo no puede superar los 150 caracteres.';
        }

        if ($datos['area_codigo'] === '' || !ctype_digit($datos['area_codigo'])) {
            $errores['area_codigo'] = 'El código de área debe ser un número.';
        } elseif ((int) $datos['area_codigo'] < 1 || (int) $datos['area_codigo'] > 999) {
            $errores['area_codigo'] = 'El código de área debe estar entre 1 y 999.';
        }

        if ($datos['descripcion'] === '') {
            $errores['descripcion'] = 'La descripción es obligatoria.';
        } elseif (mb_strlen($datos['descripcion']) < 15) {
            $errores['descripcion'] = 'La descripción debe tener al menos 15 caracteres.';
        }

        return $errores;
    }

    /**
     * Carga una vista dentro del layout general.
     *
     * @param string               $vista Ruta relativa dentro de /views (sin .php)
     * @param array<string, mixed> $datos Variables disponibles en la vista
     */
    private function render(string $vista, array $datos = []): void
    {
        $datos['mensaje'] = $datos['mensaje'] ?? $this->leerMensaje();
        extract($datos, EXTR_SKIP);
        require $this->vistas . '/layouts/header.php';
        require $this->vistas . '/' . $vista . '.php';
        require $this->vistas . '/layouts/footer.php';
    }

    /** Redirección interna a otra ruta de la aplicación. */
    private function redirigir(string $ruta): void
    {
        header('Location: ' . URL_BASE . '/index.php?ruta=' . $ruta);
        exit;
    }

    /** Guarda un mensaje flash que se muestra una sola vez tras la redirección. */
    private function mensaje(string $tipo, string $texto): void
    {
        $_SESSION[self::CLAVE_MENSAJE] = ['tipo' => $tipo, 'texto' => $texto];
    }

    /** Lee el mensaje flash y lo elimina de la sesión. */
    private function leerMensaje(): ?array
    {
        if (!isset($_SESSION[self::CLAVE_MENSAJE])) {
            return null;
        }

        $mensaje = $_SESSION[self::CLAVE_MENSAJE];
        unset($_SESSION[self::CLAVE_MENSAJE]);

        return $mensaje;
    }
}
