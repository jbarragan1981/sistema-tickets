<?php
/** Tablero por estado. Recibe $incidenciasPorEstado desde el controlador. */

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
    <p>Arrastra una tarjeta hacia otra columna para cambiar su estado. Si el arrastre no está disponible, usa el selector de cada tarjeta.</p>
</section>

<p class="tablero__aviso" id="tablero-aviso" role="status" hidden></p>

<div class="tablero" id="tablero">
    <?php foreach (Incidencia::ESTADOS as $estado): ?>
        <?php $incidencias = $incidenciasPorEstado[$estado] ?? []; ?>
        <section class="columna columna--<?= $clasesEstado[$estado] ?>">
            <header class="columna__cabecera">
                <h2 class="columna__titulo"><?= htmlspecialchars($estado) ?></h2>
                <span class="columna__contador"><?= count($incidencias) ?></span>
            </header>

            <div class="columna__lista" data-estado="<?= htmlspecialchars($estado) ?>">
                <?php if (empty($incidencias)): ?>
                    <p class="columna__vacio">
                        Todavía no hay incidencias aquí.
                        <a href="<?= URL_BASE ?>/index.php?ruta=crear">Registra la primera</a>.
                    </p>
                <?php endif; ?>

                <?php foreach ($incidencias as $incidencia): ?>
                    <article class="tarjeta"
                             draggable="true"
                             tabindex="0"
                             data-id="<?= (int) $incidencia['id'] ?>"
                             data-estado="<?= htmlspecialchars($estado) ?>">
                        <h3 class="tarjeta__titulo"><?= htmlspecialchars($incidencia['titulo']) ?></h3>

                        <div class="tarjeta__meta">
                            <span class="etiqueta etiqueta--<?= $clasesPrioridad[$incidencia['prioridad']] ?>">
                                <?= htmlspecialchars($incidencia['prioridad']) ?>
                            </span>
                            <span class="tarjeta__categoria"><?= htmlspecialchars($incidencia['categoria']) ?></span>
                        </div>

                        <div class="tarjeta__pie">
                            <span class="tarjeta__reportante"><?= htmlspecialchars($incidencia['reportante']) ?></span>
                            <span class="tarjeta__fecha"><?= htmlspecialchars(date('d/m/Y', strtotime($incidencia['fecha_reporte']))) ?></span>
                        </div>

                        <form class="tarjeta__mover" method="post" action="<?= URL_BASE ?>/index.php?ruta=mover">
                            <input type="hidden" name="id" value="<?= (int) $incidencia['id'] ?>">
                            <label class="tarjeta__mover-etiqueta" for="mover-<?= (int) $incidencia['id'] ?>">Mover a</label>
                            <select class="tarjeta__mover-select" id="mover-<?= (int) $incidencia['id'] ?>" name="estado">
                                <?php foreach (Incidencia::ESTADOS as $opcion): ?>
                                    <option value="<?= htmlspecialchars($opcion) ?>" <?= $opcion === $estado ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($opcion) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="tarjeta__mover-boton" type="submit">Cambiar</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
