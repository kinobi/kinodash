<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>KinoDash</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.0/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <?php foreach ($modules as $module): ?>
        <?php echo $module->head() ?>
    <?php endforeach ?>
</head>
<body>
<section class="section">
    <div class="columns is-vcentered">
        <div class="column"></div>
        <div class="column">
            <h1 class="title has-text-centered">
                Hello World
            </h1>
        </div>
        <div class="column"></div>
    </div>
</section>
<?php foreach ($modules as $module): ?>
    <?php if ($module->script()): ?>
        <script><?php echo $module->script() ?></script>
    <?php endif ?>
<?php endforeach ?>
</body>
</html>
