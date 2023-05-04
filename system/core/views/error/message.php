<div>
    <?php foreach (error() as $k => $v): ?>
    <small id="emailHelp" class="form-text text-danger"><?= is_numeric($k) ? $v : '' ?></small>
    <?php endforeach; ?>
</div>