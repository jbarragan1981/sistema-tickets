<?php
/** Consulta de incidencias en tabla. Recibe $incidencias y $termino desde el controlador. */

$clasesEstado = [
    'Nuevo'      => 'nuevo',
    'En proceso' => 'proceso',
    'Resuelto'   => 'resuelto',
    'Cerrado'    => 'cerrado',
];

$clasesPrioridad = [
    'Baja'    => 'baja',
    'Media'   => 'media',
    'Alta'    => 'alta',
    'Crítica' => 'critica',
];
?>
<section class="encabezado-seccion">
    <h1>Incidencias registradas</h1>
    <p>Consulta el detalle de cada incidencia, filtra por título o reportante y elimina las que ya no apliquen.</p>
</section>

<form class="buscador" method="get" action="<?= URL_BASE ?>/index.php">
    <input type="hidden" name="ruta" value="listar">
    <label class="buscador__etiqueta" for="q">Buscar</label>
    <input class="buscador__control" type="search" id="q" name="q"
           value="<?= htmlspecialchars($termino) ?>" placeholder="Título o persona que reporta">
    <button class="boton" type="submit">Buscar</button>
    <?php if ($termino !== ''): ?>
        <a class="boton boton--claro" href="<?= URL_BASE ?>/index.php?ruta=listar">Quitar filtro</a>
    <?php endif; ?>
</form>

<?php if (empty($incidencias)): ?>
    <p class="tabla-vacia">
        <?= $termino !== ''
            ? 'Ninguna incidencia coincide con la búsqueda.'
            : 'Todavía no hay incidencias registradas.' ?>
    </p>
<?php else: ?>
    <div class="tabla-scroll">
        <table class="tabla-datos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Reportante</th>
                    <th>Correo</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidencias as $incidencia): ?>
                    <tr>
                        <td class="tabla-datos__id">#<?= (int) $incidencia['id'] ?></td>
                        <td><?= htmlspecialchars($incidencia['titulo']) ?></td>
                        <td><?= htmlspecialchars($incidencia['categoria']) ?></td>
                        <td>
                            <span class="etiqueta etiqueta--<?= $clasesPrioridad[$incidencia['prioridad']] ?>">
                                <?= htmlspecialchars($incidencia['prioridad']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="marca-estado marca-estado--<?= $clasesEstado[$incidencia['estado']] ?>">
                                <?= htmlspecialchars($incidencia['estado']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($incidencia['reportante']) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($incidencia['correo']) ?>"><?= htmlspecialchars($incidencia['correo']) ?></a></td>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($incidencia['fecha_reporte']))) ?></td>
                        <td>
                            <form method="post" action="<?= URL_BASE ?>/index.php?ruta=eliminar"
                                  onsubmit="return confirm('¿Eliminar la incidencia #<?= (int) $incidencia['id'] ?>? Esta acción no se puede deshacer.');">
                                <input type="hidden" name="id" value="<?= (int) $incidencia['id'] ?>">
                                <button class="boton-tabla boton-tabla--peligro" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
