<head>
    <?= $this->title ?>
    <?php foreach ($this->stylesheets as $stylesheet): ?><?= $stylesheet ?><?php endforeach ?>
    <?php foreach ($this->metadata as $metadata): ?><?= $metadata ?><?php endforeach ?>
    <?php foreach ($this->scripts as $script): ?><?= $script ?><?php endforeach ?>
</head>
