<?php use Kinodash\Dashboard\Spot; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>KinoDash</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.0/css/bulma.min.css">
    <link rel="stylesheet" href=./app.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <?php foreach ($modules as $module): ?>
        <?php if ($headView = $module->view(Spot::HEAD())): ?>
            <?= $this->fetch($module->id() . '::' . $headView->template(), $headView->data()) ?>
        <?php endif ?>
    <?php endforeach ?>
</head>
<body>
<section class="section">
    <div class="container">
        <div class="columns is-vcentered">
            <div class="column"></div>
            <div class="column is-half">
                <div class="kinodash-modules">
                    <?php foreach ($modules as $module): ?>
                        <?php if ($middleCenter = $module->view(Spot::MIDDLE_CENTER())): ?>
                            <?= $this->fetch($module->id() . '::' . $middleCenter->template(), $middleCenter->data()) ?>
                        <?php endif ?>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="column"></div>
        </div>
    </div>
</section>
<?php foreach ($modules as $module): ?>
    <?php if ($script = $module->view(Spot::SCRIPT())): ?>
        <?= $this->fetch($module->id() . '::' . $script->template(), $script->data()) ?>
    <?php endif ?>
<?php endforeach ?>
</body>
</html>
