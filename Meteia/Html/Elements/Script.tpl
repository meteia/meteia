<?php /** @var $this \Meteia\Examples\Tags\Script */ ?>
<script <?= $this->isModule ? ' type="module"' : ' nomodule' ?><?= $this->defer ? ' defer' : '' ?><?= $this->async ? ' async' : '' ?> src="<?= $this->src ?>" integrity="<?= $this->integrity ?>" crossorigin="<?= $this->crossorigin ?>"></script>
