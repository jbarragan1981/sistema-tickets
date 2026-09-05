<?php
/**
 * Conexión a MySQL.
 *
 * Archivo independiente: ningún otro archivo del proyecto define credenciales.
 * Base de datos: integradora — usuario root sin contraseña (entorno local XAMPP).
 */

declare(strict_types=1);

const DB_HOST    = 'localhost';
const DB_NOMBRE  = 'integradora';
const DB_USUARIO = 'root';
const DB_CLAVE   = '';
const DB_CHARSET = 'utf8mb4';

/**
 * Devuelve una única instancia de PDO para toda la petición.
 * La conexión se abre una sola vez y se reutiliza en cada llamada
 * (evita abrir una conexión nueva por cada consulta).
 */
function conexion(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NOMBRE . ';charset=' . DB_CHARSET;

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USUARIO, DB_CLAVE, $opciones);
    } catch (PDOException $e) {
        // No se expone la contraseña ni la traza completa, solo una guía de qué revisar.
        die(
            'No se pudo conectar a la base de datos. Verifique que: ' .
            '1) el servicio MySQL de XAMPP esté iniciado, y ' .
            '2) la base "integradora" haya sido importada (database/integradora.sql).'
        );
    }

    return $pdo;
}
