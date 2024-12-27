<?php
require_once 'backend.php';

// Initialize vCenter connection and get initial data
$data = initializeVCenter($vcenter_host, $username, $password);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alerts = handleFormSubmission($session_id);
}

// Get templates for VM creation
$templates = $data['templates'] ?? null;
$vms = $data['vms'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>vCenter Management</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="app.js"></script>
</head>
<body>
    <div class="container">
        <?php if (isset($alerts)): ?>
            <?php foreach ($alerts as $alert): ?>
                <script>
                    Swal.fire({
                        title: '<?php echo $alert['alert_title']; ?>',
                        text: '<?php echo $alert['alert_message']; ?>',
                        icon: '<?php echo $alert['alert_type']; ?>'
                    });
                </script>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Virtual Machines Section -->
        <?php if ($vms && isset($vms['value'])): ?>
            <h2>Virtual Machines</h2>
            <button class="create-vm-btn" onclick="showCreateVMModal()">Create New VM</button>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Power State</th>
                    <th>CPU Count</th>
                    <th>Memory (GB)</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($vms['value'] as $vm): ?>
                    <tr>
                        <td>
                            <a href="view.php?vm_id=<?php echo htmlspecialchars($vm['vm']); ?>&vm_name=<?php echo htmlspecialchars($vm['name']); ?>" class="vm-name">
                                <?php echo htmlspecialchars($vm['name']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="vm-status status-<?php echo strtolower($vm['power_state']); ?>">
                                <?php echo htmlspecialchars($vm['power_state']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($vm['cpu_count']); ?></td>
                        <td><?php echo htmlspecialchars(round($vm['memory_size_MiB'] / 1024, 2)); ?></td>
                        <td>
                            <button class="edit-btn" onclick="showEditVMModal('<?php echo $vm['vm']; ?>', '<?php echo $vm['name']; ?>')">Edit</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- ESXi Hosts Section -->
        <?php if ($data['hosts'] && isset($data['hosts']['value'])): ?>
            <h2>ESXi Hosts</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Connection State</th>
                    <th>Power State</th>
                    <th>CPU Cores</th>
                    <th>Memory (GB)</th>
                </tr>
                <?php foreach ($data['hosts']['value'] as $host): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($host['name']); ?></td>
                        <td>
                            <span class="host-status status-<?php echo strtolower($host['connection_state']); ?>">
                                <?php echo htmlspecialchars($host['connection_state']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="host-status status-<?php echo strtolower($host['power_state']); ?>">
                                <?php echo htmlspecialchars($host['power_state']); ?>
                            </span>
                        </td>
                        <td><?php echo isset($host['cpu_count']) ? htmlspecialchars($host['cpu_count']) : 'N/A'; ?></td>
                        <td><?php echo isset($host['memory_size_MiB']) ? htmlspecialchars(round($host['memory_size_MiB'] / 1024, 2)) : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- Datacenters Section -->
        <?php if ($data['datacenters'] && isset($data['datacenters']['value'])): ?>
            <h2>Datacenters</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($data['datacenters']['value'] as $datacenter): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($datacenter['name']); ?></td>
                        <td>
                            <span class="datacenter-status">
                                <?php echo isset($datacenter['status']) ? htmlspecialchars($datacenter['status']) : 'Available'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- Clusters Section -->
        <?php if ($data['clusters'] && isset($data['clusters']['value'])): ?>
            <h2>Clusters</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>HA Enabled</th>
                    <th>DRS Enabled</th>
                    <th>Host Count</th>
                </tr>
                <?php foreach ($data['clusters']['value'] as $cluster): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cluster['name']); ?></td>
                        <td>
                            <span class="feature-status status-<?php echo isset($cluster['ha_enabled']) && $cluster['ha_enabled'] ? 'enabled' : 'disabled'; ?>">
                                <?php echo isset($cluster['ha_enabled']) && $cluster['ha_enabled'] ? 'Yes' : 'No'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="feature-status status-<?php echo isset($cluster['drs_enabled']) && $cluster['drs_enabled'] ? 'enabled' : 'disabled'; ?>">
                                <?php echo isset($cluster['drs_enabled']) && $cluster['drs_enabled'] ? 'Yes' : 'No'; ?>
                            </span>
                        </td>
                        <td><?php echo isset($cluster['host_count']) ? htmlspecialchars($cluster['host_count']) : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- Create VM Modal -->
        <div id="createVMModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideCreateVMModal()">&times;</span>
                <h2>Create New Virtual Machine</h2>
                <?php include 'vm_form.php'; ?>
            </div>
        </div>

        <!-- Edit VM Modal -->
        <div id="editVMModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideEditVMModal()">&times;</span>
                <h2>Edit Virtual Machine</h2>
                <?php include 'vm_form.php'; ?>
            </div>
        </div>
    </div>
</body>
</html>