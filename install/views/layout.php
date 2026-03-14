<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>WHMAZ Installation - Step <?= $currentStep ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Installer CSS -->
    <link rel="stylesheet" href="assets/installer.css">
</head>
<body>
    <div class="installer-wrapper">
        <!-- Header -->
        <div class="installer-header">
            <div class="logo">
                <i class="fas fa-server"></i>
                <span>WHMAZ</span>
            </div>
            <h1>Installation Wizard</h1>
        </div>

        <!-- Progress Steps -->
        <div class="installer-steps">
            <div class="step <?= $currentStep >= 1 ? ($currentStep > 1 ? 'completed' : 'active') : '' ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 1): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        1
                    <?php endif; ?>
                </div>
                <span>Welcome</span>
            </div>
            <div class="step-line <?= $currentStep > 1 ? 'completed' : '' ?>"></div>

            <div class="step <?= $currentStep >= 2 ? ($currentStep > 2 ? 'completed' : 'active') : '' ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 2): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        2
                    <?php endif; ?>
                </div>
                <span>Requirements</span>
            </div>
            <div class="step-line <?= $currentStep > 2 ? 'completed' : '' ?>"></div>

            <div class="step <?= $currentStep >= 3 ? ($currentStep > 3 ? 'completed' : 'active') : '' ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 3): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        3
                    <?php endif; ?>
                </div>
                <span>Database</span>
            </div>
            <div class="step-line <?= $currentStep > 3 ? 'completed' : '' ?>"></div>

            <div class="step <?= $currentStep >= 4 ? ($currentStep > 4 ? 'completed' : 'active') : '' ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 4): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        4
                    <?php endif; ?>
                </div>
                <span>Import</span>
            </div>
            <div class="step-line <?= $currentStep > 4 ? 'completed' : '' ?>"></div>

            <div class="step <?= $currentStep >= 5 ? ($currentStep > 5 ? 'completed' : 'active') : '' ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 5): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        5
                    <?php endif; ?>
                </div>
                <span>Settings</span>
            </div>
            <div class="step-line <?= $currentStep > 5 ? 'completed' : '' ?>"></div>

            <div class="step <?= $currentStep >= 6 ? 'active' : '' ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 6): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        6
                    <?php endif; ?>
                </div>
                <span>Complete</span>
            </div>
        </div>

        <!-- Content Area -->
        <div class="installer-content">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php include $stepView; ?>
        </div>

        <!-- Footer -->
        <div class="installer-footer">
            <p>&copy; <?= date('Y') ?> WHMAZ. All rights reserved.</p>
        </div>
    </div>

    <script>
        // CSRF Token for AJAX requests
        const CSRF_TOKEN = '<?= $csrfToken ?>';
    </script>
</body>
</html>
