// API Base URL
const API_URL = "api.php";

// Load servers on page load
document.addEventListener("DOMContentLoaded", function () {
  loadServers();
});

// Load all servers
async function loadServers() {
  try {
    const response = await fetch(`${API_URL}?action=list`);
    const result = await response.json();

    if (result.success) {
      displayServers(result.data);
    } else {
      showToast("Failed to load servers", "error");
    }
  } catch (error) {
    showToast("Error: " + error.message, "error");
  }
}

// Display servers in table
function displayServers(servers) {
  const tbody = document.getElementById("serversTableBody");

  if (servers.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="7" class="loading">No servers configured yet</td></tr>';
    return;
  }

  tbody.innerHTML = servers
    .map(
      (server) => `
        <tr>
            <td>${server.id}</td>
            <td><span class="badge badge-info">Backend ${server.backend_id}</span></td>
            <td>${server.name}</td>
            <td><small>${server.base_url}</small></td>
            <td>${server.phone || "-"}</td>
            <td>
                <span class="badge ${server.is_active ? "badge-success" : "badge-danger"}">
                    ${server.is_active ? "Active" : "Inactive"}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-secondary" onclick="editServer(${server.id})">Edit</button>
                    <button class="btn btn-sm ${server.is_active ? "btn-danger" : "btn-success"}" 
                            onclick="toggleServer(${server.id})">
                        ${server.is_active ? "Disable" : "Enable"}
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteServer(${server.id})">Delete</button>
                </div>
            </td>
        </tr>
    `,
    )
    .join("");
}

// Show add modal
function showAddModal() {
  document.getElementById("modalTitle").textContent = "Add New Server";
  document.getElementById("serverForm").reset();
  document.getElementById("serverId").value = "";
  document.getElementById("is_active").checked = true;
  toggleFields();
  document.getElementById("serverModal").classList.add("show");
}

// Edit server
async function editServer(id) {
  try {
    const response = await fetch(`${API_URL}?action=get&id=${id}`);
    const result = await response.json();

    if (result.success) {
      const server = result.data;
      document.getElementById("modalTitle").textContent = "Edit Server";
      document.getElementById("serverId").value = server.id;
      document.getElementById("backend_id").value = server.backend_id;
      document.getElementById("name").value = server.name;
      document.getElementById("base_url").value = server.base_url;
      document.getElementById("token").value = server.token || "";
      document.getElementById("session_id").value = server.session_id || "";
      document.getElementById("phone").value = server.phone || "";
      document.getElementById("userkey").value = server.userkey || "";
      document.getElementById("passkey").value = server.passkey || "";
      document.getElementById("is_active").checked = server.is_active == 1;

      toggleFields();
      document.getElementById("serverModal").classList.add("show");
    } else {
      showToast("Failed to load server details", "error");
    }
  } catch (error) {
    showToast("Error: " + error.message, "error");
  }
}

// Save server (create or update)
async function saveServer(event) {
  event.preventDefault();

  const id = document.getElementById("serverId").value;
  const formData = {
    backend_id: document.getElementById("backend_id").value,
    name: document.getElementById("name").value,
    base_url: document.getElementById("base_url").value,
    token: document.getElementById("token").value,
    session_id: document.getElementById("session_id").value,
    phone: document.getElementById("phone").value,
    userkey: document.getElementById("userkey").value,
    passkey: document.getElementById("passkey").value,
    is_active: document.getElementById("is_active").checked ? 1 : 0,
  };

  try {
    const url = id
      ? `${API_URL}?action=update&id=${id}`
      : `${API_URL}?action=create`;
    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(formData),
    });

    const result = await response.json();

    if (result.success) {
      showToast(result.message, "success");
      closeModal();
      loadServers();
    } else {
      showToast(result.message || "Failed to save server", "error");
    }
  } catch (error) {
    showToast("Error: " + error.message, "error");
  }
}

// Delete server
async function deleteServer(id) {
  if (!confirm("Are you sure you want to delete this server?")) {
    return;
  }

  try {
    const response = await fetch(`${API_URL}?action=delete&id=${id}`, {
      method: "POST",
    });

    const result = await response.json();

    if (result.success) {
      showToast(result.message, "success");
      loadServers();
    } else {
      showToast(result.message || "Failed to delete server", "error");
    }
  } catch (error) {
    showToast("Error: " + error.message, "error");
  }
}

// Toggle server active status
async function toggleServer(id) {
  try {
    const response = await fetch(`${API_URL}?action=toggle&id=${id}`, {
      method: "POST",
    });

    const result = await response.json();

    if (result.success) {
      showToast(result.message, "success");
      loadServers();
    } else {
      showToast(result.message || "Failed to toggle server status", "error");
    }
  } catch (error) {
    showToast("Error: " + error.message, "error");
  }
}

// Close modal
function closeModal() {
  document.getElementById("serverModal").classList.remove("show");
}

// Toggle form fields based on backend type
function toggleFields() {
  const backendId = document.getElementById("backend_id").value;

  const tokenGroup = document.getElementById("tokenGroup");
  const sessionGroup = document.getElementById("sessionGroup");
  const userkeyGroup = document.getElementById("userkeyGroup");
  const passkeyGroup = document.getElementById("passkeyGroup");

  // Hide all optional fields first
  tokenGroup.style.display = "none";
  sessionGroup.style.display = "none";
  userkeyGroup.style.display = "none";
  passkeyGroup.style.display = "none";

  const backendDescription = document.getElementById("backendDescription");
  const serverList = {
    1: "https://go.topidesta.my.id/v1",
    2: "https://go.topidesta.my.id/v2",
    3: "https://go.topidesta.my.id/v3",
    4: "https://go.topidesta.my.id/v4-3",
    5: "https://go.topidesta.my.id/v4-4",
    6: "https://go.topidesta.my.id/v6",
    7: "https://go.topidesta.my.id/v6-3",
    8: "https://go.topidesta.my.id/v7-3",
    99: "https://go.topidesta.my.id/v99",
  };
  const url = serverList[backendId];
  if (url) {
    backendDescription.innerHTML = `<a href="${url}" target="_blank">${url}</a>`;
  } else {
    backendDescription.innerHTML = "";
  }

  // Show fields based on backend type
  if (["1", "2", "3", "5", "7", "8"].includes(backendId)) {
    tokenGroup.style.display = "block";
    sessionGroup.style.display = "block";
  } else if (["4", "6"].includes(backendId)) {
    tokenGroup.style.display = "block";
  } else if (backendId === "99") {
    userkeyGroup.style.display = "block";
    passkeyGroup.style.display = "block";
  }
}

// Show toast notification
function showToast(message, type = "success") {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.className = `toast ${type}`;
  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
}

// Close modal when clicking outside
window.onclick = function (event) {
  const modal = document.getElementById("serverModal");
  if (event.target === modal) {
    closeModal();
  }
};
