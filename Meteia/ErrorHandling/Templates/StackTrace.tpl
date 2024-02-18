<div class="container mx-auto">
<h1 class="text-xl p-4 border-b border-red-400"><?= $this->throwable->getMessage(); ?></h1>
<div class="ml-4 mt-8 text-2xl">Stacktrace (newest first)</div>
<div class="mt-2">
  <ul class="space-y-3">
    <?= implode("\n", $this->stackFrames()); ?>
  </ul>
</div>
</div>
<script>hljs.initHighlightingOnLoad();</script>
