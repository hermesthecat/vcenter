<?php
require_once 'backend.php';

// Initialize vCenter connection and get data
$vcenter_data = initializeVCenter();
$session_id = $vcenter_data['session_id'];
$alerts = $vcenter_data['alerts'];
$data = $vcenter_data['data'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alerts = array_merge($alerts, handleFormSubmission($session_id));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>vCenter VM Creation</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="app.js"></script>
</head>
<body>
    <div class="container">
        <?php if (!empty($alerts)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php foreach ($alerts as $alert): ?>
                        Swal.fire({
                            title: '<?php echo addslashes($alert['alert_title']); ?>',
                            text: '<?php echo addslashes($alert['alert_message']); ?>',
                            icon: '<?php echo $alert['alert_type']; ?>',
                            confirmButtonColor: '<?php echo $alert['alert_type'] === 'error' ? '#d33' : ($alert['alert_type'] === 'warning' ? '#f8bb86' : '#4CAF50'); ?>'
                        });
                    <?php endforeach; ?>
                });
            </script>
        <?php endif; ?>

        <?php if ($session_id): ?>
            <div style="margin-bottom: 20px;">
                <button class="create-button" onclick="showCreateVMModal()">Create New VM</button>
            </div>

            <?php if ($vms && isset($vms['value'])): ?>
                <h2>Virtual Machines</h2>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Power State</th>
                        <th>CPU Count</th>
                        <th>Memory Size (MB)</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($vms['value'] as $vm): ?>
                        <tr>
                            <td>
                                <a href="view.php?vm_id=<?php echo urlencode($vm['vm']); ?>&vm_name=<?php echo urlencode($vm['name']); ?>" class="vm-name">
                                    <?php echo htmlspecialchars($vm['name']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="vm-status status-<?php echo strtolower($vm['power_state']); ?>">
                                    <?php echo htmlspecialchars($vm['power_state']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($vm['cpu_count']); ?></td>
                            <td><?php echo htmlspecialchars($vm['memory_size_MiB']); ?></td>
                            <td>
                                <a href="view.php?vm_id=<?php echo urlencode($vm['vm']); ?>&vm_name=<?php echo urlencode($vm['name']); ?>" class="btn-small">
                                    View Details
                                </a>
                                <button class="btn-small edit-btn" onclick="showEditVMModal('<?php echo urlencode($vm['vm']); ?>', '<?php echo htmlspecialchars($vm['name'], ENT_QUOTES); ?>')">
                                    Edit VM
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <?php if ($templates): ?>
                <h2>Available Templates</h2>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Power State</th>
                        <th>CPU Count</th>
                        <th>Memory Size (MB)</th>
                    </tr>
                    <?php foreach ($templates['value'] as $template): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($template['name']); ?></td>
                            <td><?php echo htmlspecialchars($template['power_state']); ?></td>
                            <td><?php echo htmlspecialchars($template['cpu_count']); ?></td>
                            <td><?php echo htmlspecialchars($template['memory_size_MiB']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <!-- Create VM Modal -->
            <div id="createVMModal" class="create-vm-modal">
                <div class="create-vm-modal-content">
                    <span class="close-modal" onclick="hideCreateVMModal()">&times;</span>
                    <h2>Create New VM from Template</h2>
                    <form method="POST" id="createVMForm">
                        <?php include 'vm_form.php'; ?>
                    </form>
                </div>
            </div>

            <!-- Edit VM Modal -->
            <div id="editVMModal" class="create-vm-modal">
                <div class="create-vm-modal-content">
                    <span class="close-modal" onclick="hideEditVMModal()">&times;</span>
                    <h2>Edit Virtual Machine</h2>
                    <form method="POST" id="editVMForm">
                        <input type="hidden" name="action" value="edit_vm">
                        <input type="hidden" name="vm_id" id="edit_vm_id">
                        <?php include 'vm_form.php'; ?>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <script>
                Swal.fire({
                    title: 'Authentication Error',
                    text: 'Failed to authenticate with vCenter',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>