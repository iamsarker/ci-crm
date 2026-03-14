<h2>Database Import</h2>
<p class="subtitle">Importing database tables and initial data. Please wait...</p>

<div class="progress-wrapper">
    <div class="progress-bar-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <p class="progress-text">
        <span id="progressText">Preparing to import...</span>
        <span class="progress-percentage" id="progressPercent">0%</span>
    </p>
</div>

<div class="status-message loading" id="statusMessage">
    <i class="fas fa-circle-notch"></i>
    <p id="statusText">Initializing database import...</p>
</div>

<div id="importResult" style="display: none;">
    <div class="alert" id="resultAlert">
        <i class="fas fa-check-circle"></i>
        <span id="resultMessage"></span>
    </div>

    <div id="errorDetails" style="display: none;">
        <h4 class="section-title"><i class="fas fa-exclamation-triangle"></i> Import Warnings</h4>
        <div id="errorList" style="max-height: 200px; overflow-y: auto; background: #fef2f2; padding: 15px; border-radius: 8px; font-size: 13px;"></div>
    </div>

    <form method="POST" action="index.php" id="continueForm">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="step" value="4">
        <input type="hidden" name="action" value="next">

        <div class="btn-group">
            <button type="submit" class="btn btn-primary" id="continueBtn" disabled>
                Continue <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>

<script>
// Start import process
function startImport() {
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressPercent = document.getElementById('progressPercent');
    const statusMessage = document.getElementById('statusMessage');
    const statusText = document.getElementById('statusText');
    const importResult = document.getElementById('importResult');
    const resultAlert = document.getElementById('resultAlert');
    const resultMessage = document.getElementById('resultMessage');
    const errorDetails = document.getElementById('errorDetails');
    const errorList = document.getElementById('errorList');
    const continueBtn = document.getElementById('continueBtn');

    // Import main database
    importFile('crm_db.sql', 'Importing main database...', 0, 70)
        .then(() => {
            // Import views
            return importFile('crm_db_views.sql', 'Importing database views...', 70, 100);
        })
        .then(result => {
            // Success
            statusMessage.style.display = 'none';
            importResult.style.display = 'block';
            resultAlert.className = 'alert alert-success';
            resultAlert.querySelector('i').className = 'fas fa-check-circle';
            resultMessage.textContent = 'Database imported successfully!';
            continueBtn.disabled = false;
            progressBar.style.width = '100%';
            progressPercent.textContent = '100%';
            progressText.textContent = 'Import complete!';
        })
        .catch(error => {
            // Error
            statusMessage.style.display = 'none';
            importResult.style.display = 'block';
            resultAlert.className = 'alert alert-danger';
            resultAlert.querySelector('i').className = 'fas fa-times-circle';
            resultMessage.textContent = 'Import failed: ' + error.message;

            if (error.errors && error.errors.length > 0) {
                errorDetails.style.display = 'block';
                let errorHtml = '<ul style="margin: 0; padding-left: 20px;">';
                error.errors.forEach(err => {
                    errorHtml += '<li>' + escapeHtml(err.error) + '</li>';
                });
                errorHtml += '</ul>';
                errorList.innerHTML = errorHtml;
            }
        });
}

function importFile(filename, statusMsg, progressStart, progressEnd) {
    return new Promise((resolve, reject) => {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressPercent = document.getElementById('progressPercent');
        const statusText = document.getElementById('statusText');

        statusText.textContent = statusMsg;
        progressText.textContent = 'Importing ' + filename + '...';

        const data = new FormData();
        data.append('action', 'import_sql');
        data.append('csrf_token', CSRF_TOKEN);
        data.append('file', filename);

        // Animate progress bar
        let currentProgress = progressStart;
        const progressInterval = setInterval(() => {
            if (currentProgress < progressEnd - 10) {
                currentProgress += 1;
                progressBar.style.width = currentProgress + '%';
                progressPercent.textContent = currentProgress + '%';
            }
        }, 100);

        fetch('index.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            clearInterval(progressInterval);
            progressBar.style.width = progressEnd + '%';
            progressPercent.textContent = progressEnd + '%';

            if (result.success) {
                resolve(result);
            } else {
                reject({
                    message: result.message || 'Import failed',
                    errors: result.errors || []
                });
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            reject({ message: 'Network error during import', errors: [] });
        });
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Start import when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(startImport, 500);
});
</script>
