<?php
/**
 * Vista: tablero de incidencias por estado.
 * Solo presenta datos; no consulta la base de datos.
 */

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
    <h1>Tablero de incidencias</h1>
    <p>Arrastra una tarjeta hacia otra columna para actualizar su estado.</p>
</section>

<div class="tablero">
    <?php foreach (Incidencia::ESTADOS as $estado): ?>
        <?php $tarjetas = $incidenciasPorEstado[$estado] ?? []; ?>
        <section class="columna columna--<?= $clasesEstado[$estado] ?>">
            <header class="columna__cabecera">
                <h2 class="columna__titulo"><?= htmlspecialchars($estado) ?></h2>
                <span class="columna__contador"><?= count($tarjetas) ?></span>
            </header>

            <div class="columna__lista" data-estado="<?= htmlspecialchars($estado) ?>">
                <?php if (empty($tarjetas)): ?>
                    <p class="columna__vacio">Sin incidencias</p>
                <?php endif; ?>

                <?php foreach ($tarjetas as $tarjeta): ?>
                    <article class="tarjeta"
                             draggable="true"
                             tabindex="0"
                             data-id="<?= (int) $tarjeta['id'] ?>"
                             data-estado="<?= htmlspecialchars($estado) ?>">
                        <h3 class="tarjeta__titulo"><?= htmlspecialchars($tarjeta['titulo']) ?></h3>

                        <div class="tarjeta__meta">
                            <span class="etiqueta etiqueta--<?= $clasesPrioridad[$tarjeta['prioridad']] ?>">
                                <?= htmlspecialchars($tarjeta['prioridad']) ?>
                            </span>
                            <span class="tarjeta__categoria"><?= htmlspecialchars($tarjeta['categoria']) ?></span>
                        </div>

                        <div class="tarjeta__pie">
                            <span class="tarjeta__reportante"><?= htmlspecialchars($tarjeta['reportante']) ?></span>
                            <span class="tarjeta__fecha"><?= htmlspecialchars(date('d/m/Y', strtotime($tarjeta['fecha_reporte']))) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
