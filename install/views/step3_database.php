<h2>Database Configuration</h2>
<p class="subtitle">Enter your database connection details. The database must already exist.</p>

<div class="alert alert-info">
    <i class="fas fa-lightbulb"></i>
    <div>
        <strong>Tip:</strong> If you're using cPanel, create the database first via MySQL Databases.
        The hostname is usually <code>localhost</code>.
    </div>
</div>

<?php
$dbConfig = $installer->getDbCredentials();
?>

<form method="POST" action="index.php" id="dbForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="step" value="3">

    <div class="input-group">
        <div class="form-group">
            <label for="db_host">Database Hostname <span class="required">*</span></label>
            <input type="text" class="form-control" id="db_host" name="db_host"
                   value="<?= htmlspecialchars($dbConfig['host'] ?? 'localhost') ?>"
                   placeholder="localhost" required>
            <p class="form-text">Usually "localhost" for most servers</p>
        </div>
        <div class="form-group">
            <label for="db_port">Database Port <span class="required">*</span></label>
            <input type="text" class="form-control" id="db_port" name="db_port"
                   value="<?= htmlspecialchars($dbConfig['port'] ?? '3306') ?>"
                   placeholder="3306" required>
            <p class="form-text">Default MySQL port is 3306</p>
        </div>
    </div>

    <div class="form-group">
        <label for="db_name">Database Name <span class="required">*</span></label>
        <input type="text" class="form-control" id="db_name" name="db_name"
               value="<?= htmlspecialchars($dbConfig['database'] ?? '') ?>"
               placeholder="whmaz_db" required>
        <p class="form-text">The database must already exist on your server</p>
    </div>

    <div class="input-group">
        <div class="form-group">
            <label for="db_user">Database Username <span class="required">*</span></label>
            <input type="text" class="form-control" id="db_user" name="db_user"
                   value="<?= htmlspecialchars($dbConfig['username'] ?? '') ?>"
                   placeholder="whmaz_user" required>
        </div>
        <div class="form-group">
            <label for="db_pass">Database Password</label>
            <input type="password" class="form-control" id="db_pass" name="db_pass"
                   value="<?= htmlspecialchars($dbConfig['password'] ?? '') ?>"
                   placeholder="Enter password">
            <p class="form-text">Leave empty if no password</p>
        </div>
    </div>

    <button type="button" class="btn btn-secondary test-connection-btn" id="testConnection">
        <i class="fas fa-plug"></i> Test Connection
    </button>

    <div class="connection-status" id="connectionStatus">
        <i class="fas fa-check-circle"></i>
        <span id="connectionMessage"></span>
    </div>

    <div class="btn-group">
        <a href="index.php?step=2" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button type="submit" name="action" value="next" class="btn btn-primary" id="submitBtn">
            Continue <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</form>

<script>
document.getElementById('testConnection').addEventListener('click', function() {
    const btn = this;
    const status = document.getElementById('connectionStatus');
    const message = document.getElementById('connectionMessage');

    // Get form values
    const data = new FormData();
    data.append('action', 'test_db');
    data.append('csrf_token', '<?= $csrfToken ?>');
    data.append('db_host', document.getElementById('db_host').value);
    data.append('db_port', document.getElementById('db_port').value);
    data.append('db_name', document.getElementById('db_name').value);
    data.append('db_user', document.getElementById('db_user').value);
    data.append('db_pass', document.getElementById('db_pass').value);

    // Show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Testing...';
    status.className = 'connection-status';
    status.style.display = 'none';

    fetch('index.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(result => {
        status.style.display = 'flex';
        if (result.success) {
            status.className = 'connection-status show success';
            status.querySelector('i').className = 'fas fa-check-circle';
            let msg = result.message;
            if (result.has_tables) {
                msg += ' Warning: Database has existing tables. They will be replaced.';
            }
            message.textContent = msg;
        } else {
            status.className = 'connection-status show error';
            status.querySelector('i').className = 'fas fa-times-circle';
            message.textContent = result.message;
        }
    })
    .catch(error => {
        status.className = 'connection-status show error';
        status.querySelector('i').className = 'fas fa-times-circle';
        message.textContent = 'Connection test failed. Please check your details.';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection';
    });
});

document.getElementById('dbForm').addEventListener('submit', function(e) {
    const host = document.getElementById('db_host').value.trim();
    const name = document.getElementById('db_name').value.trim();
    const user = document.getElementById('db_user').value.trim();

    if (!host || !name || !user) {
        e.preventDefault();
        alert('Please fill in all required fields.');
        return;
    }
});
</script>
