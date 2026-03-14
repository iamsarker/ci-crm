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
</script>
