<?php include_once __DIR__ . "/../dashboard/header-dashboard.php"; ?>

<?php if (count($proyectos) === 0) { ?>

    <p class="no-proyectos">No hay Proyectos Aún <a href="/crear-proyecto">Comienza Creando Uno</a></p>
<?php } else { ?>
    <ul class="listado-proyectos">
        <?php foreach ($proyectos as $proyecto): ?>

            <li class="proyecto">
                <a href="/proyecto?id=<?php echo $proyecto->url; ?>">
                    <?php echo $proyecto->proyecto; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php } ?>

<?php include_once __DIR__ . "/../dashboard/footer-dashboard.php"; ?>