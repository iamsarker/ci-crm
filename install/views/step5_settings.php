<h2>Site Settings</h2>
<p class="subtitle">Configure your site details and create the administrator account.</p>

<?php
$detectedUrl = $installer->detectSiteUrl();
?>

<form method="POST" action="index.php" id="settingsForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="step" value="5">

    <h4 class="section-title"><i class="fas fa-globe"></i> Site Information</h4>

    <div class="form-group">
        <label for="site_name">Site Name <span class="required">*</span></label>
        <input type="text" class="form-control" id="site_name" name="site_name"
               value="WHMAZ" placeholder="My Company Name" required>
        <p class="form-text">This will be displayed in the header and emails</p>
    </div>

    <div class="form-group">
        <label for="site_url">Site URL <span class="required">*</span></label>
        <input type="url" class="form-control" id="site_url" name="site_url"
               value="<?= htmlspecialchars($detectedUrl) ?>" placeholder="https://example.com/" required>
        <p class="form-text">Include http:// or https:// and trailing slash</p>
    </div>

    <h4 class="section-title"><i class="fas fa-user-shield"></i> Administrator Account</h4>

    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Important:</strong> This will be the main administrator account.
            Use a strong password and keep it secure.
        </div>
    </div>

    <div class="input-group">
        <div class="form-group">
            <label for="admin_first_name">First Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="admin_first_name" name="admin_first_name"
                   placeholder="John" required>
        </div>
        <div class="form-group">
            <label for="admin_last_name">Last Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="admin_last_name" name="admin_last_name"
                   placeholder="Doe" required>
        </div>
    </div>

    <div class="form-group">
        <label for="admin_email">Admin Email <span class="required">*</span></label>
        <input type="email" class="form-control" id="admin_email" name="admin_email"
               placeholder="admin@example.com" required>
        <p class="form-text">This email will be used to log in to the admin panel</p>
    </div>

    <div class="input-group">
        <div class="form-group">
            <label for="admin_password">Password <span class="required">*</span></label>
            <input type="password" class="form-control" id="admin_password" name="admin_password"
                   placeholder="Enter password" required minlength="8">
            <p class="form-text">Minimum 8 characters with uppercase, lowercase, and numbers</p>
        </div>
        <div class="form-group">
            <label for="admin_password_confirm">Confirm Password <span class="required">*</span></label>
            <input type="password" class="form-control" id="admin_password_confirm" name="admin_password_confirm"
                   placeholder="Confirm password" required>
        </div>
    </div>

    <div id="passwordStrength" style="display: none;">
        <div class="requirement-item" id="reqLength">
            <div class="requirement-icon"><i class="fas fa-times"></i></div>
            <span class="requirement-name">At least 8 characters</span>
        </div>
        <div class="requirement-item" id="reqUpper">
            <div class="requirement-icon"><i class="fas fa-times"></i></div>
            <span class="requirement-name">At least one uppercase letter</span>
        </div>
        <div class="requirement-item" id="reqLower">
            <div class="requirement-icon"><i class="fas fa-times"></i></div>
            <span class="requirement-name">At least one lowercase letter</span>
        </div>
        <div class="requirement-item" id="reqNumber">
            <div class="requirement-icon"><i class="fas fa-times"></i></div>
            <span class="requirement-name">At least one number</span>
        </div>
    </div>

    <h4 class="section-title"><i class="fas fa-key"></i> Software License</h4>

    <div class="alert alert-warning">
        <i class="fas fa-info-circle"></i>
        <div>
            Enter the license key you received when you purchased WHMAZ. The
            install verifies it against the vendor and unlocks your tier's
            features. You can proceed without it, but tier features stay locked
            until a valid key is saved.
        </div>
    </div>

    <div class="form-group">
        <label class="checkbox-inline">
            <input type="checkbox" id="is_license_master" name="is_license_master" value="1">
            This is the master / vendor server (skip license key)
        </label>
        <p class="form-text">Only tick this on the CRM that <strong>sells</strong> licenses. Client installs leave it unchecked.</p>
    </div>

    <div id="licenseFields">
        <div class="form-group">
            <label for="license_key">License Key</label>
            <input type="text" class="form-control" id="license_key" name="license_key"
                   placeholder="WHMAZ-XXXXX-XXXXX-XXXXX-XXXXX" autocomplete="off">
        </div>

        <div class="form-group">
            <label for="license_server_url">License Server URL</label>
            <input type="url" class="form-control" id="license_server_url" name="license_server_url"
                   placeholder="https://vendor-crm.example.com">
            <p class="form-text">The vendor CRM that issued your key (include http:// or https://).</p>
        </div>

        <div class="form-group">
            <button type="button" class="btn btn-secondary" id="verifyLicenseBtn">
                <i class="fas fa-sync"></i> Verify License
            </button>
            <span id="licenseResult" style="margin-left: 10px;"></span>
        </div>
    </div>

    <div class="btn-group">
        <a href="index.php?step=4" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button type="submit" name="action" value="next" class="btn btn-primary" id="submitBtn">
            Complete Installation <i class="fas fa-check"></i>
        </button>
    </div>
</form>

<script>
const passwordInput = document.getElementById('admin_password');
const confirmInput = document.getElementById('admin_password_confirm');
const strengthDiv = document.getElementById('passwordStrength');

passwordInput.addEventListener('focus', function() {
    strengthDiv.style.display = 'block';
});

passwordInput.addEventListener('input', function() {
    const password = this.value;

    // Check requirements
    updateRequirement('reqLength', password.length >= 8);
    updateRequirement('reqUpper', /[A-Z]/.test(password));
    updateRequirement('reqLower', /[a-z]/.test(password));
    updateRequirement('reqNumber', /[0-9]/.test(password));
});

function updateRequirement(id, passed) {
    const el = document.getElementById(id);
    if (passed) {
        el.className = 'requirement-item passed';
        el.querySelector('i').className = 'fas fa-check';
    } else {
        el.className = 'requirement-item failed';
        el.querySelector('i').className = 'fas fa-times';
    }
}

document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    const email = document.getElementById('admin_email').value;

    // Validate email
    if (!email.includes('@') || !email.includes('.')) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return;
    }

    // Validate password strength
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long.');
        return;
    }

    if (!/[A-Z]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one uppercase letter.');
        return;
    }

    if (!/[a-z]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one lowercase letter.');
        return;
    }

    if (!/[0-9]/.test(password)) {
        e.preventDefault();
        alert('Password must contain at least one number.');
        return;
    }

    // Check passwords match
    if (password !== confirm) {
        e.preventDefault();
        alert('Passwords do not match.');
        return;
    }
});

// ─── Software license ─────────────────────────────────────────────────────
const masterCheckbox = document.getElementById('is_license_master');
const licenseFields = document.getElementById('licenseFields');
const verifyBtn = document.getElementById('verifyLicenseBtn');
const licenseResult = document.getElementById('licenseResult');

masterCheckbox.addEventListener('change', function() {
    licenseFields.style.display = this.checked ? 'none' : 'block';
});

verifyBtn.addEventListener('click', function() {
    const key = document.getElementById('license_key').value.trim();
    const url = document.getElementById('license_server_url').value.trim();

    if (!key || !url) {
        licenseResult.innerHTML = '<span style="color:#c0392b;">Enter both the license key and server URL.</span>';
        return;
    }

    verifyBtn.disabled = true;
    licenseResult.innerHTML = '<span style="color:#555;"><i class="fas fa-spinner fa-spin"></i> Verifying…</span>';

    const body = new URLSearchParams();
    body.append('action', 'verify_license');
    body.append('csrf_token', '<?= $csrfToken ?>');
    body.append('license_key', key);
    body.append('license_server_url', url);

    fetch('index.php', { method: 'POST', body: body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tier = data.plan_key ? ' (' + data.plan_key + ')' : '';
                licenseResult.innerHTML = '<span style="color:#27ae60;"><i class="fas fa-check-circle"></i> ' + (data.message || 'Valid') + tier + '</span>';
            } else {
                licenseResult.innerHTML = '<span style="color:#c0392b;"><i class="fas fa-times-circle"></i> ' + (data.message || 'Invalid license') + '</span>';
            }
        })
        .catch(() => {
            licenseResult.innerHTML = '<span style="color:#c0392b;">Verification request failed.</span>';
        })
        .finally(() => { verifyBtn.disabled = false; });
});
</script>
