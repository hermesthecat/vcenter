<?php
// vCenter connection details
$vcenter_host = "YOUR_VCENTER_SERVER";
$username = "YOUR_USERNAME";
$password = "YOUR_PASSWORD";

// Disable SSL verification (for testing only - enable in production)
$curl_opts = array(
    CURLOPT_SSL_VERIFYHOST => 0,
    CURLOPT_SSL_VERIFYPEER => 0
);

// Function to show error/success messages
function showError($message, $type = 'error')
{
    return [
        'show_alert' => true,
        'alert_type' => $type,
        'alert_title' => ucfirst($type),
        'alert_message' => $message
    ];
}

// Function to get session ID
function getSessionId($vcenter_host, $username, $password, $curl_opts)
{
    $url = "https://$vcenter_host/rest/com/vmware/cis/session";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $response_data = json_decode($response, true);
        return $response_data['value'];
    }
    return null;
}

// Function to get templates
function getTemplates($vcenter_host, $session_id, $curl_opts)
{
    $url = "https://$vcenter_host/rest/vcenter/vm?filter.resource_pool_types=TEMPLATE";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get datacenters
function getDatacenters($vcenter_host, $session_id, $curl_opts)
{
    $url = "https://$vcenter_host/rest/vcenter/datacenter";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get networks
function getNetworks($vcenter_host, $session_id, $curl_opts)
{
    $url = "https://$vcenter_host/rest/vcenter/network";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get clusters
function getClusters($vcenter_host, $session_id, $curl_opts, $datacenter = null)
{
    $url = "https://$vcenter_host/rest/vcenter/cluster";
    if ($datacenter) {
        $url .= "?filter.datacenters=$datacenter";
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get resource pools
function getResourcePools($vcenter_host, $session_id, $curl_opts, $cluster = null)
{
    $url = "https://$vcenter_host/rest/vcenter/resource-pool";
    if ($cluster) {
        $url .= "?filter.clusters=$cluster";
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get storage policies
function getStoragePolicies($vcenter_host, $session_id, $curl_opts)
{
    $url = "https://$vcenter_host/rest/vcenter/storage/policies";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to create VM from template
function createVMFromTemplate($vcenter_host, $session_id, $curl_opts, $template_id, $vm_data)
{
    $url = "https://$vcenter_host/rest/vcenter/vm/template/{$template_id}/guest-customization";

    // Prepare network configuration based on MAC address choice
    $network_config = array(
        "key" => "0",
        "network" => $vm_data['network']
    );

    if ($vm_data['mac_type'] === 'MANUAL') {
        $network_config["mac_address"] = $vm_data['mac_address'];
        $network_config["mac_type"] = "MANUAL";
    } else {
        $network_config["mac_type"] = "GENERATED";
    }

    $data = array(
        "spec" => array(
            "name" => $vm_data['name'],
            "placement" => array(
                "datacenter" => $vm_data['datacenter'],
                "cluster" => $vm_data['cluster'],
                "resource_pool" => $vm_data['resource_pool'],
                "folder" => null
            ),
            "hardware_customization" => array(
                "memory" => array(
                    "size_MiB" => intval($vm_data['ram']) * 1024
                ),
                "cpu" => array(
                    "count" => intval($vm_data['cpu_count'] ?? 1),
                    "cores_per_socket" => intval($vm_data['cores_per_socket'] ?? 1),
                    "reservation" => !empty($vm_data['cpu_reservation']) ? intval($vm_data['cpu_reservation']) : null,
                    "limit" => !empty($vm_data['cpu_limit']) ? intval($vm_data['cpu_limit']) : null
                ),
                "disks" => array(
                    array(
                        "size" => intval($vm_data['disk']) * 1024 * 1024 * 1024,
                        "provisioning" => $vm_data['disk_provisioning']
                    )
                )
            ),
            "network_customization" => array(
                "nics" => array($network_config)
            ),
            "storage_policy" => array(
                "policy" => $vm_data['storage_policy']
            )
        )
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to execute command in VM
function executeCommandInVM($vcenter_host, $session_id, $curl_opts, $vm_id, $command, $guest_username, $guest_password, $os_type)
{
    // First, create credentials for guest operations
    $url = "https://$vcenter_host/rest/vcenter/vm/$vm_id/guest/operations";

    $credentials = array(
        "type" => "USERNAME_PASSWORD",
        "username" => $guest_username,
        "password" => $guest_password
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($credentials));

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    curl_close($ch);

    // Now execute the command
    $url = "https://$vcenter_host/rest/vcenter/vm/$vm_id/guest/commands";

    // Configure command based on OS type
    if ($os_type === 'windows') {
        $command_data = array(
            "path" => "cmd.exe",
            "arguments" => array("/c", $command),
            "working_directory" => "C:\\Windows\\Temp"
        );
    } else { // linux
        $command_data = array(
            "path" => "/bin/bash",
            "arguments" => array("-c", $command),
            "working_directory" => "/tmp"
        );
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($command_data));

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get all VMs
function getVMs($vcenter_host, $session_id, $curl_opts)
{
    $url = "https://$vcenter_host/rest/vcenter/vm";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get VM metrics
function getVMMetrics($vcenter_host, $session_id, $curl_opts, $vm_id)
{
    $url = "https://$vcenter_host/rest/vcenter/vm/$vm_id/stats";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get VM details
function getVMDetails($vcenter_host, $session_id, $curl_opts, $vm_id)
{
    $url = "https://$vcenter_host/rest/vcenter/vm/$vm_id";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to get hosts
function getHosts($vcenter_host, $session_id, $curl_opts = array()) {
    $url = "https://$vcenter_host/rest/vcenter/host";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to initialize vCenter connection and get initial data
function initializeVCenter($vcenter_host, $username, $password) {
    global $curl_opts;
    
    $session_id = getSessionId($vcenter_host, $username, $password, $curl_opts);
    if ($session_id) {
        $data = array();
        $data['vms'] = getVMs($vcenter_host, $session_id, $curl_opts);
        $data['hosts'] = getHosts($vcenter_host, $session_id, $curl_opts);
        $data['datacenters'] = getDatacenters($vcenter_host, $session_id, $curl_opts);
        $data['clusters'] = getClusters($vcenter_host, $session_id, $curl_opts);
        $data['templates'] = getTemplates($vcenter_host, $session_id, $curl_opts);
        return $data;
    }
    return null;
}

// Function to check if session is valid
function isSessionValid($vcenter_host, $session_id, $curl_opts)
{
    if (!$session_id) return false;

    $url = "https://$vcenter_host/rest/com/vmware/cis/session?~action=get";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $http_code === 200;
}

// Function to get or refresh session
function getOrRefreshSession($vcenter_host, $username, $password, $curl_opts, $current_session_id = null)
{
    if ($current_session_id && isSessionValid($vcenter_host, $current_session_id, $curl_opts)) {
        return $current_session_id;
    }

    return getSessionId($vcenter_host, $username, $password, $curl_opts);
}

// Direct AJAX request handling
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    try {
        // Get session ID from request header if available
        $current_session_id = $_SERVER['HTTP_X_VMWARE_SESSION_ID'] ?? null;

        // Get or refresh session
        $session_id = getOrRefreshSession($vcenter_host, $username, $password, $curl_opts, $current_session_id);

        if (!$session_id) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication failed']);
            exit;
        }

        // Return the session ID in response header
        header('X-VMware-Session-ID: ' . $session_id);

        switch ($_GET['action']) {
            case 'get_resource_pools':
                if (!isset($_GET['cluster'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Cluster ID is required']);
                    exit;
                }

                $resource_pools = getResourcePools($vcenter_host, $session_id, $curl_opts, $_GET['cluster']);
                if (!$resource_pools) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to fetch resource pools']);
                    exit;
                }

                echo json_encode($resource_pools['value']);
                break;

            case 'get_vm_details':
                if (!isset($_GET['vm_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'VM ID is required']);
                    exit;
                }

                $details = getVMDetails($vcenter_host, $session_id, $curl_opts, $_GET['vm_id']);
                if ($details) {
                    echo json_encode([
                        'success' => true,
                        'vm' => [
                            'name' => $details['value']['name'],
                            'cpu_count' => $details['value']['cpu']['count'],
                            'cores_per_socket' => $details['value']['cpu']['cores_per_socket'],
                            'memory_size_MiB' => $details['value']['memory']['size_MiB'],
                            'cluster' => $details['value']['cluster'],
                            'resource_pool' => $details['value']['resource_pool'],
                            'network' => $details['value']['network'],
                            'storage_policy' => $details['value']['storage_policy']
                        ]
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to fetch VM details']);
                }
                break;

            case 'get_clusters':
                if (isset($_GET['datacenter'])) {
                    $clusters = getClusters($vcenter_host, $session_id, $curl_opts, $_GET['datacenter']);
                } else {
                    $clusters = getClusters($vcenter_host, $session_id, $curl_opts);
                }

                if (!$clusters) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to fetch clusters']);
                    exit;
                }

                echo json_encode($clusters['value']);
                break;

            case 'get_networks':
                $networks = getNetworks($vcenter_host, $session_id, $curl_opts);
                if (!$networks) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to fetch networks']);
                    exit;
                }

                echo json_encode($networks['value']);
                break;

            case 'get_storage_policies':
                $policies = getStoragePolicies($vcenter_host, $session_id, $curl_opts);
                if (!$policies) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to fetch storage policies']);
                    exit;
                }

                echo json_encode($policies['value']);
                break;

            case 'get_snapshots':
                if (!isset($_GET['vm_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'VM ID is required']);
                    exit;
                }

                $snapshots = getVMSnapshots($vcenter_host, $session_id, $curl_opts, $_GET['vm_id']);
                if ($snapshots) {
                    echo json_encode([
                        'success' => true,
                        'snapshots' => $snapshots['value']
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to fetch snapshots']);
                }
                break;

            case 'create_snapshot':
                if (!isset($_POST['vm_id']) || !isset($_POST['name'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'VM ID and snapshot name are required']);
                    exit;
                }

                $result = createVMSnapshot($vcenter_host, $session_id, $curl_opts, $_POST['vm_id'], $_POST);
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => $result['message']
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'error' => $result['message']
                    ]);
                }
                break;

            case 'edit_vm':
                if (!isset($_POST['vm_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'VM ID is required']);
                    exit;
                }

                $result = updateExistingVM($vcenter_host, $session_id, $curl_opts, $_POST['vm_id'], $_POST);
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'VM updated successfully'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to update VM']);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                exit;
        }
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Function to update existing VM
function updateExistingVM($vcenter_host, $session_id, $curl_opts, $vm_id, $vm_data) {
    $url = "https://$vcenter_host/rest/vcenter/vm/$vm_id";
    
    $update_data = [
        'spec' => [
            'name' => $vm_data['vm_name'],
            'cpu' => [
                'count' => (int)$vm_data['cpu_count'],
                'cores_per_socket' => (int)$vm_data['cores_per_socket']
            ],
            'memory' => [
                'size_MiB' => (int)$vm_data['memory_size']
            ]
        ]
    ];
    
    if (isset($vm_data['network']) && !empty($vm_data['network'])) {
        $update_data['spec']['network'] = $vm_data['network'];
    }
    
    if (isset($vm_data['storage_policy']) && !empty($vm_data['storage_policy'])) {
        $update_data['spec']['storage_policy'] = $vm_data['storage_policy'];
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code === 204;
}

// Update handleFormSubmission function to handle both creation and editing
function handleFormSubmission($session_id) {
    global $vcenter_host, $curl_opts;
    $alerts = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'edit_vm') {
            // Handle VM edit
            if (!isset($_POST['vm_id'])) {
                $alerts[] = showError('VM ID is required');
                return $alerts;
            }

            $result = updateExistingVM($vcenter_host, $session_id, $curl_opts, $_POST['vm_id'], $_POST);
            
            if ($result) {
                $alerts[] = showError('VM updated successfully!', 'success');
            } else {
                $alerts[] = showError('Failed to update VM');
            }
        } else {
            // Handle VM creation (existing code)
            if (empty($_POST['vm_name'])) {
                $alerts[] = showError('VM name is required');
            } else {
                $vm_data = array(
                    'name' => $_POST['vm_name'],
                    'template' => $_POST['template'],
                    'datacenter' => $_POST['datacenter'],
                    'cluster' => $_POST['cluster'],
                    'resource_pool' => $_POST['resource_pool'],
                    'storage_policy' => $_POST['storage_policy'],
                    'ram' => $_POST['ram'],
                    'cpu_count' => $_POST['cpu_count'],
                    'cores_per_socket' => $_POST['cores_per_socket'],
                    'cpu_reservation' => $_POST['cpu_reservation'],
                    'cpu_limit' => $_POST['cpu_limit'],
                    'disk' => $_POST['disk'],
                    'disk_provisioning' => $_POST['disk_provisioning'],
                    'network' => $_POST['network'],
                    'mac_type' => $_POST['mac_type'],
                    'mac_address' => isset($_POST['mac_address']) ? $_POST['mac_address'] : null,
                    'post_creation_command' => isset($_POST['post_creation_command']) ? $_POST['post_creation_command'] : null,
                    'guest_username' => isset($_POST['guest_username']) ? $_POST['guest_username'] : null,
                    'guest_password' => isset($_POST['guest_password']) ? $_POST['guest_password'] : null,
                    'command_timeout' => isset($_POST['command_timeout']) ? intval($_POST['command_timeout']) : 60,
                    'os_type' => isset($_POST['os_type']) ? $_POST['os_type'] : 'linux'
                );

                $result = createVMFromTemplate($vcenter_host, $session_id, $curl_opts, $vm_data['template'], $vm_data);
                if ($result) {
                    $alerts[] = showError('VM creation started successfully!', 'success');

                    if (!empty($vm_data['post_creation_command']) && !empty($vm_data['guest_username']) && !empty($vm_data['guest_password'])) {
                        sleep($vm_data['command_timeout']);

                        $command_result = executeCommandInVM(
                            $vcenter_host,
                            $session_id,
                            $curl_opts,
                            $result['vm'],
                            $vm_data['post_creation_command'],
                            $vm_data['guest_username'],
                            $vm_data['guest_password'],
                            $vm_data['os_type']
                        );

                        if ($command_result) {
                            $alerts[] = showError('Command execution started!', 'success');
                        } else {
                            $alerts[] = showError('VM created but command execution failed.', 'warning');
                        }
                    }
                } else {
                    $alerts[] = showError('Failed to create VM');
                }
            }
        }
    }
    
    return $alerts;
}

// Function to get VM alerts
function getVMAlerts($vcenter_host, $session_id, $curl_opts, $vm_id) {
    $url = "https://$vcenter_host/rest/appliance/monitoring/alerts?object_type=VirtualMachine&object_id=$vm_id&limit=50";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

function handleAjaxRequests() {
    global $vcenter_host, $session_id, $curl_opts;
    
    if (!isset($_GET['action'])) {
        return;
    }

    $action = $_GET['action'];
    $response = ['success' => false];

    switch ($action) {
        case 'get_resource_pools':
            // ... existing code ...
            break;

        case 'get_clusters':
            // ... existing code ...
            break;

        case 'get_networks':
            // ... existing code ...
            break;

        case 'get_storage_policies':
            // ... existing code ...
            break;

        case 'get_vm_details':
            if (!isset($_GET['vm_id'])) {
                $response['message'] = 'VM ID is required';
                break;
            }
            $vm_details = getVMDetails($vcenter_host, $session_id, $curl_opts, $_GET['vm_id']);
            if ($vm_details) {
                $response['success'] = true;
                $response['vm'] = $vm_details;
            } else {
                $response['message'] = 'Failed to fetch VM details';
            }
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Function to get VM snapshots
function getVMSnapshots($vcenter_host, $session_id, $curl_opts, $vm_id) {
    $url = "https://$vcenter_host/rest/vcenter/vm/$vm_id/snapshots";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return json_decode($response, true);
    }
    return null;
}

// Function to create VM snapshot
function createVMSnapshot($vcenter_host, $session_id, $curl_opts, $vm_id, $snapshot_data) {
    // Önce mevcut snapshot sayısını kontrol et
    $snapshots = getVMSnapshots($vcenter_host, $session_id, $curl_opts, $vm_id);
    if ($snapshots && count($snapshots['value']) >= 5) {
        return [
            'success' => false,
            'message' => 'Snapshot limit exceeded. Maximum 5 snapshots allowed.'
        ];
    }

    $url = "https://$vcenter_host/rest/vcenter/vm/$vm_id/snapshots";
    
    $data = [
        'name' => $snapshot_data['name'],
        'description' => $snapshot_data['description'] ?? '',
        'memory' => isset($snapshot_data['memory']) ? (bool)$snapshot_data['memory'] : false,
        'quiesce' => isset($snapshot_data['quiesce']) ? (bool)$snapshot_data['quiesce'] : false
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "vmware-api-session-id: $session_id"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    foreach ($curl_opts as $key => $value) {
        curl_setopt($ch, $key, $value);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 201) {
        return [
            'success' => true,
            'message' => 'Snapshot created successfully'
        ];
    }
    return [
        'success' => false,
        'message' => 'Failed to create snapshot: ' . $response
    ];
}
