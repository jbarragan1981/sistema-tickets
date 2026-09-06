<?php
/**
 * Tabla Incidencia.
 */

declare(strict_types=1);

class Incidencia
{
    /** Estados válidos del tablero, en el orden en que se muestran. */
    public const ESTADOS = ['Nuevo', 'En proceso', 'Resuelto', 'Cerrado'];

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Inserta una incidencia y devuelve el id generado.
     * El estado no se recibe: toda incidencia nueva entra como 'Nuevo'.
     *
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO incidencias
                    (titulo, descripcion, categoria_id, prioridad, estado, reportante, correo, area_codigo)
                VALUES
                    (:titulo, :descripcion, :categoria_id, :prioridad, :estado, :reportante, :correo, :area_codigo)';

        $consulta = $this->db->prepare($sql);
        $consulta->execute([
            ':titulo'       => $datos['titulo'],
            ':descripcion'  => $datos['descripcion'],
            ':categoria_id' => $datos['categoria_id'],
            ':prioridad'    => $datos['prioridad'],
            ':estado'       => 'Nuevo',
            ':reportante'   => $datos['reportante'],
            ':correo'       => $datos['correo'],
            ':area_codigo'  => $datos['area_codigo'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** Devuelve todas las incidencias con su categoría, ordenadas por fecha de reporte descendente. */
    public function obtenerTodas(): array
    {
        $sql = 'SELECT i.id, i.titulo, i.descripcion, i.categoria_id,
                       c.nombre AS categoria, c.color AS categoria_color,
                       i.prioridad, i.estado, i.reportante, i.correo, i.area_codigo,
                       i.fecha_reporte, i.fecha_actualizacion
                FROM incidencias i
                INNER JOIN categorias c ON c.id = i.categoria_id
                ORDER BY i.fecha_reporte DESC';

        return $this->db->query($sql)->fetchAll();
    }

    /** Devuelve las incidencias agrupadas por estado para pintar el tablero. */
    public function obtenerPorEstado(): array
    {
        $agrupadas = array_fill_keys(self::ESTADOS, []);

        foreach ($this->obtenerTodas() as $incidencia) {
            $agrupadas[$incidencia['estado']][] = $incidencia;
        }

        return $agrupadas;
    }

    /** Cambia el estado de una incidencia al moverla de columna. */
    public function cambiarEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            return false;
        }

        $consulta = $this->db->prepare('UPDATE incidencias SET estado = :estado WHERE id = :id');

        return $consulta->execute([':estado' => $estado, ':id' => $id]);
    }

    /** Busca incidencias por título o por persona que reporta. */
    public function buscar(string $termino): array
    {
        $sql = 'SELECT i.id, i.titulo, i.descripcion, i.categoria_id,
                       c.nombre AS categoria, c.color AS categoria_color,
                       i.prioridad, i.estado, i.reportante, i.correo, i.area_codigo,
                       i.fecha_reporte, i.fecha_actualizacion
                FROM incidencias i
                INNER JOIN categorias c ON c.id = i.categoria_id
                WHERE i.titulo LIKE :termino_titulo OR i.reportante LIKE :termino_reportante
                ORDER BY i.fecha_reporte DESC';

        $comodin = '%' . $termino . '%';

        $consulta = $this->db->prepare($sql);
        $consulta->execute([
            ':termino_titulo'     => $comodin,
            ':termino_reportante' => $comodin,
        ]);

        return $consulta->fetchAll();
    }

    /** Elimina una incidencia por id. */
    public function eliminar(int $id): bool
    {
        $consulta = $this->db->prepare('DELETE FROM incidencias WHERE id = :id');

        return $consulta->execute([':id' => $id]);
    }
}
