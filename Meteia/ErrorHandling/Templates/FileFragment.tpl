<div class="font-mono p-0 rounded">
  <div class="grid grid-cols-[auto_1fr]">
    <?php foreach ($this->lines() as $line): ?>
        <div class="select-none text-right px-2 <?= $line->activeLine ? 'bg-indigo-700' : 'odd:bg-gray-700' ?>">
          <a href="<?= $line->href ?>"><?= $line->number ?></a>
        </div>
        <pre class="px-2 <?= $line->activeLine ? 'bg-indigo-950' : '' ?>"><?= rtrim($line->source) ?></pre>
    <?php endforeach; ?>
  </div>
</div>
