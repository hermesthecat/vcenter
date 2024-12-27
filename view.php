<?php
require_once 'backend.php';

// Get VM ID and name from URL parameters
$vm_id = $_GET['vm_id'] ?? null;
$vm_name = $_GET['vm_name'] ?? 'Unknown VM';

if (!$vm_id) {
    header('Location: index.php');
    exit;
}

// Initialize vCenter connection
$data = initializeVCenter($vcenter_host, $username, $password);
$session_id = getSessionId($vcenter_host, $username, $password, $curl_opts);

if (!$session_id) {
    header('Location: index.php');
    exit;
}

// Get VM details and metrics
$details = getVMDetails($vcenter_host, $session_id, $curl_opts, $vm_id);
$metrics = getVMMetrics($vcenter_host, $session_id, $curl_opts, $vm_id);

if (!$details && !$metrics) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>VM Statistics - <?php echo htmlspecialchars($vm_name); ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="app.js"></script>
    <script>
        // Pass VM metrics to JavaScript
        window.vmMetrics = <?php echo json_encode($metrics['value'] ?? null); ?>;
    </script>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($vm_name); ?></h1>
            <a href="index.php" class="back-button">← Back to VM List</a>
        </div>

        <?php if ($details): ?>
            <div class="vm-details">
                <h2>VM Details</h2>
                <button class="btn edit-vm-btn" onclick="showEditVMModal('<?php echo htmlspecialchars($vm_id); ?>', '<?php echo htmlspecialchars($vm_name); ?>')">Edit VM</button>
                <table>
                    <tr>
                        <th>Power State</th>
                        <td>
                            <span class="vm-status status-<?php echo strtolower($details['value']['power_state']); ?>">
                                <?php echo htmlspecialchars($details['value']['power_state']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>CPU Configuration</th>
                        <td>
                            <?php echo htmlspecialchars($details['value']['cpu']['count']); ?> CPU(s),
                            <?php echo htmlspecialchars($details['value']['cpu']['cores_per_socket']); ?> Cores per Socket
                        </td>
                    </tr>
                    <tr>
                        <th>Memory</th>
                        <td><?php echo htmlspecialchars($details['value']['memory']['size_MiB']); ?> MB</td>
                    </tr>
                    <tr>
                        <th>Guest OS</th>
                        <td><?php echo htmlspecialchars($details['value']['guest_OS'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php if (isset($details['value']['disks'])): ?>
                        <tr>
                            <th>Disks</th>
                            <td>
                                <ul style="margin: 0; padding-left: 20px;">
                                    <?php foreach ($details['value']['disks'] as $key => $disk): ?>
                                        <li>
                                            <strong>Disk <?php echo htmlspecialchars($key); ?></strong><br>
                                            Size: <?php echo htmlspecialchars(round($disk['capacity'] / (1024 * 1024 * 1024), 2)); ?> GB<br>
                                            Type: <?php echo htmlspecialchars($disk['type'] ?? 'N/A'); ?><br>
                                            Path: <?php echo htmlspecialchars($disk['backing']['vmdk_file'] ?? 'N/A'); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($details['value']['nics'])): ?>
                        <tr>
                            <th>Network Adapters</th>
                            <td>
                                <ul style="margin: 0; padding-left: 20px;">
                                    <?php foreach ($details['value']['nics'] as $key => $nic): ?>
                                        <li>
                                            NIC <?php echo htmlspecialchars($key); ?>:
                                            <?php echo htmlspecialchars($nic['mac_address'] ?? 'N/A'); ?>
                                            (<?php echo htmlspecialchars($nic['state'] ?? 'N/A'); ?>)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($metrics): ?>
            <div class="charts-section">
                <h2>Performance Metrics</h2>
                <div class="charts-container">
                    <div class="chart-box">
                        <canvas id="cpuChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <canvas id="memoryChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <canvas id="diskChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <canvas id="networkChart"></canvas>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Snapshots Section -->
        <div class="snapshots-section">
            <h2>Snapshots</h2>
            <button class="btn create-snapshot-btn" onclick="showCreateSnapshotModal()">Create Snapshot</button>
            <div id="snapshotsTable">
                <!-- Snapshots will be loaded here -->
            </div>
        </div>

        <!-- Create Snapshot Modal -->
        <div id="createSnapshotModal" class="create-vm-modal">
            <div class="create-vm-modal-content">
                <span class="close-modal" onclick="hideCreateSnapshotModal()">&times;</span>
                <h2>Create New Snapshot</h2>
                <form id="createSnapshotForm">
                    <input type="hidden" name="vm_id" value="<?php echo htmlspecialchars($_GET['vm_id']); ?>">
                    <div class="form-group">
                        <label for="snapshot_name">Snapshot Name:</label>
                        <input type="text" id="snapshot_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="snapshot_description">Description:</label>
                        <textarea id="snapshot_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="memory" value="1">
                            Include memory state
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="quiesce" value="1">
                            Quiesce guest file system
                        </label>
                    </div>
                    <button type="submit" class="btn-submit">Create Snapshot</button>
                </form>
            </div>
        </div>

        <!-- Edit VM Modal -->
        <div id="editVMModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideModal()">&times;</span>
                <h2>Edit Virtual Machine</h2>
                <form id="editVMForm">
                    <input type="hidden" name="action" value="edit_vm">
                    <input type="hidden" name="vm_id" value="<?php echo htmlspecialchars($vm_id); ?>">
                    
                    <div class="form-group">
                        <label for="edit_vm_name">VM Name</label>
                        <input type="text" id="edit_vm_name" name="vm_name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_cpu_count">CPU Count</label>
                        <input type="number" id="edit_cpu_count" name="cpu_count" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_cores_per_socket">Cores Per Socket</label>
                        <input type="number" id="edit_cores_per_socket" name="cores_per_socket" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_memory_size">Memory (MB)</label>
                        <input type="number" id="edit_memory_size" name="memory_size" min="1024" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Update VM</button>
                        <button type="button" class="btn-cancel" onclick="hideModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Load snapshots when page loads
    document.addEventListener('DOMContentLoaded', function() {
        loadSnapshots();
    });

    function loadSnapshots() {
        const vmId = '<?php echo htmlspecialchars($_GET['vm_id']); ?>';
        fetchFromAPI(`backend.php?action=get_snapshots&vm_id=${vmId}`)
        .then(data => {
            if (data.success) {
                displaySnapshots(data.snapshots);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.error || 'Failed to load snapshots',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Failed to load snapshots',
                icon: 'error'
            });
        });
    }

    function displaySnapshots(snapshots) {
        const table = document.createElement('table');
        table.className = 'snapshots-table';
        
        // Table header
        const header = `
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Creation Time</th>
                <th>Size</th>
                <th>State</th>
            </tr>
        `;
        
        // Table rows
        const rows = snapshots.map(snapshot => `
            <tr>
                <td>${snapshot.name}</td>
                <td>${snapshot.description || '-'}</td>
                <td>${new Date(snapshot.creation_time).toLocaleString()}</td>
                <td>${formatSize(snapshot.size)}</td>
                <td>${snapshot.state}</td>
            </tr>
        `).join('');
        
        table.innerHTML = header + rows;
        document.getElementById('snapshotsTable').innerHTML = '';
        document.getElementById('snapshotsTable').appendChild(table);
    }

    function formatSize(bytes) {
        if (!bytes) return '-';
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let size = bytes;
        let unitIndex = 0;
        
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        
        return `${size.toFixed(2)} ${units[unitIndex]}`;
    }

    function showCreateSnapshotModal() {
        document.getElementById('createSnapshotModal').style.display = 'block';
    }

    function hideCreateSnapshotModal() {
        document.getElementById('createSnapshotModal').style.display = 'none';
        document.getElementById('createSnapshotForm').reset();
    }

    // Handle snapshot creation
    document.getElementById('createSnapshotForm').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'create_snapshot');
        
        fetch('backend.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success',
                    text: data.message,
                    icon: 'success'
                }).then(() => {
                    hideCreateSnapshotModal();
                    loadSnapshots();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.error,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while creating the snapshot',
                icon: 'error'
            });
        });
    });
    </script>
</body>

</html>