<div class="already-installed">
    <div class="icon">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <h2>Installation Error</h2>
    <p><?= htmlspecialchars($errorMessage ?? 'An unexpected error occurred.') ?></p>

    <?php if (!empty($errorDetails)): ?>
    <div class="info-box" style="text-align: left; margin-top: 20px;">
        <h4><i class="fas fa-info-circle"></i> Details</h4>
        <p style="font-size: 13px; font-family: monospace; word-break: break-all;">
            <?= htmlspecialchars($errorDetails) ?>
        </p>
    </div>
    <?php endif; ?>

    <div class="btn-group" style="justify-content: center;">
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-redo"></i> Try Again
        </a>
    </div>
</div>
