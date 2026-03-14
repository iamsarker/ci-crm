<?php
$siteUrl = $installer->detectSiteUrl();
$adminUrl = rtrim($siteUrl, '/') . '/whmazadmin/authenticate/login';
?>

<div class="already-installed">
    <div class="icon">
        <i class="fas fa-check-circle"></i>
    </div>
    <h2>Already Installed</h2>
    <p>WHMAZ is already installed on this server. If you need to reinstall, please delete the <code>.env</code> file and <code>install/install.lock</code> file first.</p>

    <div class="btn-group" style="justify-content: center; margin-top: 30px;">
        <a href="<?= htmlspecialchars($adminUrl) ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt"></i> Go to Admin Panel
        </a>
    </div>

    <div class="warning-box" style="margin-top: 30px;">
        <i class="fas fa-shield-alt"></i>
        <div class="warning-box-content">
            <h4>Security Reminder</h4>
            <p>Please delete the <code>/install</code> folder from your server for security.</p>
        </div>
    </div>
</div>
