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
    <link rel="stylesheet" href="/app.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <?php foreach ($modules as $module): ?>
        <?php if ($headView = $module->view(Spot::HEAD())): ?>
            <?= $this->fetch($module->id() . '::' . $headView->template(), $headView->data()) ?>
        <?php endif ?>
    <?php endforeach ?>
</head>
<body>
<section class="hero is-fullheight">
    <div class="hero-head">
        <div class="kinodash-top columns">
            <?php foreach ($modules as $module): ?>
                <?php if ($bodyHead = $module->view(Spot::BODY_HEAD())): ?>
                    <div class="column is-vcentered">
                        <?= $this->fetch($module->id() . '::' . $bodyHead->template(), $bodyHead->data()) ?>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        </div>
    </div>

    <div class="hero-body">
        <div class="container has-text-centered">
            <?php foreach ($modules as $module): ?>
                <?php if ($body = $module->view(Spot::BODY())): ?>
                    <?= $this->fetch($module->id() . '::' . $body->template(), $body->data()) ?>
                <?php endif ?>
            <?php endforeach ?>

            <kinodash-launcher></kinodash-launcher>
        </div>
    </div>

    <div class="hero-foot">
    </div>
</section>
<script type="module">
    import '/components/Launcher.js';
</script>
<?php foreach ($modules as $module): ?>
    <?php if ($script = $module->view(Spot::SCRIPT())): ?>
        <?= $this->fetch($module->id() . '::' . $script->template(), $script->data()) ?>
    <?php endif ?>
<?php endforeach ?>
</body>
</html>
