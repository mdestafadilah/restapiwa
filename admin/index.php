<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Gateway Admin</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 WhatsApp Gateway Admin</h1>
            <p>Manage your WhatsApp server configurations</p>
        </div>

        <div class="actions">
            <button class="btn btn-primary" onclick="showAddModal()">
                <span>➕</span> Add New Server
            </button>
        </div>

        <div class="table-container">
            <table id="serversTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Backend</th>
                        <th>Name</th>
                        <th>Base URL</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="serversTableBody">
                    <tr>
                        <td colspan="7" class="loading">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="serverModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Server</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="serverForm" onsubmit="saveServer(event)">
                <input type="hidden" id="serverId">
                
                <div class="form-group">
                    <label for="backend_id">Backend ID *</label>
                    <select id="backend_id" name="backend_id" required onchange="toggleFields()">
                        <option value="">-- Select Backend --</option>
                        <option value="1">Backend 1 (Free)</option>
                        <option value="2">Backend 2 (Free)</option>
                        <option value="3">Backend 3 (Free)</option>
                        <option value="4">Backend 4 (Free)</option>
                        <option value="5">Backend 5 (Free)</option>
                        <option value="6">Backend 6 (Free)</option>
                        <option value="7">Backend 7 (Free)</option>
                        <option value="8">Backend 8 (Free)</option>
                        <option value="99">Backend 99 (OTP - Paid)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Server Name *</label>
                    <input type="text" id="name" name="name" placeholder="e.g., Production Server" required>
                </div>

                <div class="form-group">
                    <label for="base_url">Base URL *</label>
                    <input type="url" id="base_url" name="base_url" placeholder="https://example.com/" required>
                </div>

                <div class="form-group" id="tokenGroup">
                    <label for="token">Token</label>
                    <input type="text" id="token" name="token" placeholder="API Token">
                </div>

                <div class="form-group" id="sessionGroup">
                    <label for="session_id">Session ID</label>
                    <input type="text" id="session_id" name="session_id" placeholder="Session ID">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" placeholder="628123456789">
                </div>

                <div class="form-group" id="userkeyGroup" style="display:none;">
                    <label for="userkey">User Key</label>
                    <input type="text" id="userkey" name="userkey" placeholder="User Key">
                </div>

                <div class="form-group" id="passkeyGroup" style="display:none;">
                    <label for="passkey">Pass Key</label>
                    <input type="text" id="passkey" name="passkey" placeholder="Pass Key">
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="is_active" name="is_active" checked>
                        Active
                    </label>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="assets/script.js"></script>
</body>
</html>
