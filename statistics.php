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
$vcenter_data = initializeVCenter();
$session_id = $vcenter_data['session_id'];
$alerts = $vcenter_data['alerts'];

// Get VM details and metrics
$details = getVMDetails($vcenter_host, $session_id, $curl_opts, $vm_id);
$metrics = getVMMetrics($vcenter_host, $session_id, $curl_opts, $vm_id);

// Get VM alerts
$alerts = getVMAlerts($vcenter_host, $session_id, $curl_opts, $vm_id);

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

        <?php if ($alerts && isset($alerts['value'])): ?>
            <div class="alerts-section">
                <h2>Recent Alerts</h2>
                <table class="alerts-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Severity</th>
                            <th>Message</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts['value'] as $alert): ?>
                            <tr>
                                <td class="alert-time">
                                    <?php echo date('Y-m-d H:i:s', strtotime($alert['creation_time'])); ?>
                                </td>
                                <td>
                                    <span class="alert-severity-<?php echo strtolower($alert['severity']); ?>">
                                        <?php echo htmlspecialchars($alert['severity']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($alert['message']); ?></td>
                                <td><?php echo htmlspecialchars($alert['resolution_state']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>