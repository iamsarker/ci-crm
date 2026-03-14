<?php
$siteUrl = $_SESSION['site_url'] ?? $installer->detectSiteUrl();
$adminUrl = rtrim($siteUrl, '/') . '/whmazadmin/authenticate/login';
$clientUrl = rtrim($siteUrl, '/') . '/auth/login';
?>

<div class="success-box">
    <div class="success-icon">
        <i class="fas fa-check"></i>
    </div>
    <h2>Installation Complete!</h2>
    <p>Congratulations! WHMAZ has been successfully installed on your server.</p>
</div>

<div class="info-box">
    <h4><i class="fas fa-link"></i> Your Portal URLs</h4>
    <ul class="link-list">
        <li>
            <span class="label">Admin Panel</span>
            <a href="<?= htmlspecialchars($adminUrl) ?>" target="_blank">
                <?= htmlspecialchars($adminUrl) ?>
                <i class="fas fa-external-link-alt" style="margin-left: 5px;"></i>
            </a>
        </li>
        <li>
            <span class="label">Client Portal</span>
            <a href="<?= htmlspecialchars($clientUrl) ?>" target="_blank">
                <?= htmlspecialchars($clientUrl) ?>
                <i class="fas fa-external-link-alt" style="margin-left: 5px;"></i>
            </a>
        </li>
    </ul>
</div>

<div class="warning-box">
    <i class="fas fa-shield-alt"></i>
    <div class="warning-box-content">
        <h4>Security Reminder</h4>
        <p>
            For security reasons, please <strong>delete the /install folder</strong> from your server.
            You can do this manually via FTP or click the button below.
        </p>
    </div>
</div>

<div class="info-box">
    <h4><i class="fas fa-list-check"></i> Next Steps</h4>
    <ul style="padding-left: 20px; color: #5b6b8a; margin-top: 10px;">
        <li>Login to the admin panel with your credentials</li>
        <li>Configure your company information in Settings</li>
        <li>Set up payment gateways (Stripe, SSLCommerz, etc.)</li>
        <li>Configure email settings for notifications</li>
        <li>Add your products and services</li>
        <li>Set up cron jobs for automation</li>
    </ul>
</div>

<div class="btn-group" style="flex-direction: column; gap: 10px;">
    <a href="<?= htmlspecialchars($adminUrl) ?>" class="btn btn-success btn-lg btn-block" target="_blank">
        <i class="fas fa-sign-in-alt"></i> Go to Admin Panel
    </a>

    <button type="button" class="btn btn-danger btn-block" id="deleteInstallBtn"
            onclick="deleteInstallFolder()">
        <i class="fas fa-trash-alt"></i> Delete Install Folder
    </button>
</div>

<div id="deleteStatus" class="alert" style="display: none; margin-top: 20px;"></div>

<script>
function deleteInstallFolder() {
    if (!confirm('Are you sure you want to delete the install folder? This action cannot be undone.')) {
        return;
    }

    const btn = document.getElementById('deleteInstallBtn');
    const status = document.getElementById('deleteStatus');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Deleting...';

    const data = new FormData();
    data.append('action', 'delete_install');
    data.append('csrf_token', CSRF_TOKEN);

    fetch('index.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(result => {
        status.style.display = 'flex';
        if (result.success) {
            status.className = 'alert alert-success';
            status.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;
            btn.style.display = 'none';

            // Redirect after 2 seconds
            setTimeout(function() {
                window.location.href = '<?= htmlspecialchars($adminUrl) ?>';
            }, 2000);
        } else {
            status.className = 'alert alert-danger';
            status.innerHTML = '<i class="fas fa-times-circle"></i> ' + result.message +
                ' Please delete the /install folder manually via FTP.';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash-alt"></i> Delete Install Folder';
        }
    })
    .catch(error => {
        status.style.display = 'flex';
        status.className = 'alert alert-danger';
        status.innerHTML = '<i class="fas fa-times-circle"></i> Failed to delete folder. Please delete manually.';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt"></i> Delete Install Folder';
    });
}
</script>
