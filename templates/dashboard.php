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
        <?php if ($head = $module->head()): ?>
            <?= $this->fetch($module->id() . '::' . $head->template(), $head->data()) ?>
        <?php endif ?>
    <?php endforeach ?>
</head>
<body>
<section class="section">
    <div class="container">
        <div class="columns is-vcentered">
            <div class="column"></div>
            <div class="column is-half">
                <?php foreach ($modules as $module): ?>
                    <?php if ($center = $module->center()): ?>
                        <?= $this->fetch($module->id() . '::' . $center->template(), $center->data()) ?>
                    <?php endif ?>
                <?php endforeach ?>
            </div>
            <div class="column"></div>
        </div>
    </div>
</section>
<?php foreach ($modules as $module): ?>
    <?php if ($script = $module->script()): ?>
        <?= $this->fetch($module->id() . '::' . $script->template(), $script->data()) ?>
    <?php endif ?>
<?php endforeach ?>
</body>
</html>
