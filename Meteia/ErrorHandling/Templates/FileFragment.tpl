<div class="hljs leading-tight font-mono p-0 rounded">
  <?php foreach ($this->lines() as $line): ?>
    <div class="flex items-center">
      <div class="<?= $line->activeLine ? 'bg-indigo-700' : 'odd:bg-gray-700' ?> py-0 px-4">
        <a href="<?= $line->href ?>"><?= $line->number ?></a>
      </div>
      <div class="p-0 bg-indigo-950 w-full">
        <pre><code class="php p-0 <?= $line->activeLine ? 'bg-transparent' : '' ?>"><?= rtrim($line->source) ?></code></pre>
      </div>
    </div>
  <?php endforeach; ?>
</div>
