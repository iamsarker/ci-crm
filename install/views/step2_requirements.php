<h2>Server Requirements</h2>
<p class="subtitle">Checking if your server meets the minimum requirements to run WHMAZ.</p>

<?php
$requirements = $installer->checkAllRequirements();
$allPassed = $requirements['all_passed'];
?>

<!-- PHP Version -->
<h4 class="section-title"><i class="fas fa-code"></i> PHP Version</h4>
<ul class="requirements-list">
    <li class="requirement-item <?= $requirements['php']['passed'] ? 'passed' : 'failed' ?>">
        <div class="requirement-icon">
            <i class="fas fa-<?= $requirements['php']['passed'] ? 'check' : 'times' ?>"></i>
        </div>
        <span class="requirement-name">PHP Version</span>
        <span class="requirement-value">
            Required: <?= $requirements['php']['required'] ?> |
            Current: <?= $requirements['php']['current'] ?>
        </span>
    </li>
</ul>

<!-- PHP Extensions -->
<h4 class="section-title"><i class="fas fa-puzzle-piece"></i> PHP Extensions</h4>
<ul class="requirements-list">
    <?php foreach ($requirements['extensions'] as $ext => $info): ?>
    <li class="requirement-item <?= $info['loaded'] ? 'passed' : ($info['required'] ? 'failed' : 'warning') ?>">
        <div class="requirement-icon">
            <i class="fas fa-<?= $info['loaded'] ? 'check' : ($info['required'] ? 'times' : 'exclamation') ?>"></i>
        </div>
        <span class="requirement-name">
            <?= $info['name'] ?>
            <?php if (!$info['required']): ?>
            <small style="color: #8392a5;">(Optional)</small>
            <?php endif; ?>
        </span>
        <span class="requirement-value">
            <?= $info['loaded'] ? 'Installed' : 'Not Installed' ?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>

<!-- Writable Directories -->
<h4 class="section-title"><i class="fas fa-folder-open"></i> Writable Directories</h4>
<ul class="requirements-list">
    <?php foreach ($requirements['directories'] as $dir => $info): ?>
    <li class="requirement-item <?= $info['passed'] ? 'passed' : 'failed' ?>">
        <div class="requirement-icon">
            <i class="fas fa-<?= $info['passed'] ? 'check' : 'times' ?>"></i>
        </div>
        <span class="requirement-name"><?= $info['path'] ?></span>
        <span class="requirement-value">
            <?php if (!$info['exists']): ?>
                Directory not found
            <?php elseif (!$info['writable']): ?>
                Not writable (chmod 755)
            <?php else: ?>
                Writable
            <?php endif; ?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>

<!-- Required Files -->
<h4 class="section-title"><i class="fas fa-file-alt"></i> Required Files</h4>
<ul class="requirements-list">
    <?php foreach ($requirements['files'] as $file => $info): ?>
    <li class="requirement-item <?= $info['passed'] ? 'passed' : 'failed' ?>">
        <div class="requirement-icon">
            <i class="fas fa-<?= $info['passed'] ? 'check' : 'times' ?>"></i>
        </div>
        <span class="requirement-name"><?= $info['file'] ?></span>
        <span class="requirement-value">
            <?= $info['exists'] ? 'Found' : 'Not Found' ?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>

<?php if (!$allPassed): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        <strong>Requirements Not Met</strong><br>
        Please fix the issues marked in red before continuing.
    </div>
</div>
<?php endif; ?>

<form method="POST" action="index.php">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="step" value="2">

    <div class="btn-group">
        <a href="index.php?step=1" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <?php if ($allPassed): ?>
        <button type="submit" name="action" value="next" class="btn btn-primary">
            Continue <i class="fas fa-arrow-right"></i>
        </button>
        <?php else: ?>
        <button type="submit" name="action" value="recheck" class="btn btn-secondary">
            <i class="fas fa-sync-alt"></i> Re-check Requirements
        </button>
        <?php endif; ?>
    </div>
</form>
