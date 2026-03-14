<h2>Welcome to WHMAZ</h2>
<p class="subtitle">Thank you for choosing WHMAZ CRM. This wizard will guide you through the installation process.</p>

<div class="info-box">
    <h4><i class="fas fa-info-circle"></i> Before You Begin</h4>
    <p>Please make sure you have the following information ready:</p>
    <ul style="margin-top: 10px; padding-left: 20px; color: #5b6b8a;">
        <li>Database hostname (usually <code>localhost</code>)</li>
        <li>Database name</li>
        <li>Database username and password</li>
        <li>Your admin email address</li>
    </ul>
</div>

<div class="info-box">
    <h4><i class="fas fa-clock"></i> Installation Time</h4>
    <p>The installation process typically takes 2-5 minutes depending on your server.</p>
</div>

<form method="POST" action="index.php">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    <input type="hidden" name="step" value="1">
    <input type="hidden" name="action" value="next">

    <div class="form-check">
        <input type="checkbox" id="agree" name="agree" required>
        <label for="agree">
            I have read and understood that this installation will set up WHMAZ on my server.
            I confirm that I have the necessary permissions and credentials to proceed.
        </label>
    </div>

    <div class="btn-group">
        <button type="submit" class="btn btn-primary btn-lg">
            Start Installation <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</form>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const checkbox = document.getElementById('agree');
    if (!checkbox.checked) {
        e.preventDefault();
        alert('Please check the agreement box to continue.');
    }
});
</script>
