<?php
/**
 * Tabla Categoria.
 */

declare(strict_types=1);

class Categoria
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** Devuelve el catálogo completo de categorías, ordenado por nombre. */
    public function obtenerTodas(): array
    {
        return $this->db->query('SELECT id, nombre, color FROM categorias ORDER BY nombre')->fetchAll();
    }
}
