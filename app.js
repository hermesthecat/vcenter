// Store session ID
let vmwareSessionId = null;

// Utility function for making AJAX requests
async function fetchFromAPI(action, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = `backend.php?action=${action}${queryString ? '&' + queryString : ''}`;

    const headers = {
        'Content-Type': 'application/json'
    };

    // Add session ID to headers if available
    if (vmwareSessionId) {
        headers['X-VMware-Session-ID'] = vmwareSessionId;
    }

    try {
        const response = await fetch(url, {
            headers
        });

        // Get new session ID from response header
        const newSessionId = response.headers.get('X-VMware-Session-ID');
        if (newSessionId) {
            vmwareSessionId = newSessionId;
        }

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'API request failed');
        }
        return await response.json();
    } catch (error) {
        // If session expired, clear stored session ID
        if (error.message.includes('Authentication failed')) {
            vmwareSessionId = null;
        }

        Swal.fire({
            title: 'Error!',
            text: error.message,
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        throw error;
    }
}

// Modal functions
function showCreateVMModal() {
    document.getElementById('createVMModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function hideCreateVMModal() {
    document.getElementById('createVMModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Restore background scrolling
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('createVMModal');
    if (event.target == modal) {
        hideCreateVMModal();
    }
}

// Function to update resource pools based on selected cluster
async function updateResourcePools() {
    const clusterId = document.getElementById('cluster').value;
    const resourcePoolSelect = document.getElementById('resource_pool');

    try {
        const data = await fetchFromAPI('get_resource_pools', {
            cluster: clusterId
        });
        resourcePoolSelect.innerHTML = '';
        data.forEach(pool => {
            const option = document.createElement('option');
            option.value = pool.resource_pool;
            option.textContent = pool.name;
            resourcePoolSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Failed to update resource pools:', error);
    }
}

// Function to update clusters based on selected datacenter
async function updateClusters(datacenterId) {
    const clusterSelect = document.getElementById('cluster');

    try {
        const data = await fetchFromAPI('get_clusters', {
            datacenter: datacenterId
        });
        clusterSelect.innerHTML = '';
        data.forEach(cluster => {
            const option = document.createElement('option');
            option.value = cluster.cluster;
            option.textContent = cluster.name;
            clusterSelect.appendChild(option);
        });
        await updateResourcePools();
    } catch (error) {
        console.error('Failed to update clusters:', error);
    }
}

// Function to toggle MAC address input visibility
function toggleMacAddress() {
    const macType = document.getElementById('mac_type').value;
    const macAddressDiv = document.getElementById('mac_address_div');
    const macAddressInput = document.getElementsByName('mac_address')[0];

    if (macType === 'MANUAL') {
        macAddressDiv.style.display = 'block';
        macAddressInput.required = true;
    } else {
        macAddressDiv.style.display = 'none';
        macAddressInput.required = false;
    }
}

// Function to update command placeholder based on OS type
function updateCommandPlaceholder() {
    const osType = document.getElementById('os_type').value;
    const textarea = document.getElementById('command_textarea');
    const example = document.getElementById('command_example');

    if (osType === 'windows') {
        textarea.placeholder = '@echo off\r\n' +
            'REM Enter your Windows commands here\r\n' +
            'REM Example:\r\n' +
            'powershell -Command "Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072"\r\n' +
            'powershell -Command "iex ((New-Object System.Net.WebClient).DownloadString(\'https://chocolatey.org/install.ps1\'))"\r\n' +
            'choco install nginx -y';
        example.innerHTML = 'Enter Windows commands (CMD or PowerShell) to execute after VM creation';
    } else {
        textarea.placeholder = '#!/bin/bash\n' +
            '# Enter your Linux commands here\n' +
            '# Example:\n' +
            'apt-get update\n' +
            'apt-get install -y nginx';
        example.innerHTML = 'Enter Linux shell commands to execute after VM creation';
    }
}

// Function to show/hide command execution options
function toggleCommandOptions() {
    const command = document.getElementsByName('post_creation_command')[0].value.trim();
    const commandOptions = document.querySelector('.command-options');
    const guestUsername = document.getElementsByName('guest_username')[0];
    const guestPassword = document.getElementsByName('guest_password')[0];

    if (command !== '') {
        commandOptions.style.display = 'block';
        guestUsername.required = true;
        guestPassword.required = true;
    } else {
        commandOptions.style.display = 'none';
        guestUsername.required = false;
        guestPassword.required = false;
    }
}

// Function to validate form before submission
function validateVMForm(form) {
    // Required fields validation
    const requiredFields = {
        'vm_name': 'VM Name',
        'template': 'Template',
        'datacenter': 'Datacenter',
        'cluster': 'Cluster',
        'resource_pool': 'Resource Pool'
    };

    let missingFields = [];
    for (let [fieldName, fieldLabel] of Object.entries(requiredFields)) {
        const field = form.elements[fieldName];
        if (!field || !field.value.trim()) {
            missingFields.push(fieldLabel);
        }
    }

    if (missingFields.length > 0) {
        Swal.fire({
            title: 'Required Fields Missing',
            html: `The following fields are required by vCenter API:<br><br>` +
                `<ul style="text-align: left; display: inline-block;">` +
                missingFields.map(field => `<li>${field}</li>`).join('') +
                `</ul>`,
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        return false;
    }

    // Hardware validation
    const ram = parseInt(form.elements['ram'].value);
    const disk = parseInt(form.elements['disk'].value);
    const cpuCount = parseInt(form.elements['cpu_count'].value);
    const coresPerSocket = parseInt(form.elements['cores_per_socket'].value);

    if (ram < 1) {
        Swal.fire({
            title: 'Invalid RAM',
            text: 'RAM must be at least 1 GB',
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        return false;
    }

    if (disk < 1) {
        Swal.fire({
            title: 'Invalid Disk Size',
            text: 'Disk size must be at least 1 GB',
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        return false;
    }

    if (cpuCount < 1) {
        Swal.fire({
            title: 'Invalid CPU Count',
            text: 'CPU count must be at least 1',
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        return false;
    }

    if (coresPerSocket < 1) {
        Swal.fire({
            title: 'Invalid Cores Per Socket',
            text: 'Cores per socket must be at least 1',
            icon: 'error',
            confirmButtonColor: '#d33'
        });
        return false;
    }

    // MAC address validation if manual mode
    const macType = form.elements['mac_type'].value;
    if (macType === 'MANUAL') {
        const macAddress = form.elements['mac_address'].value;
        const macRegex = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
        if (!macRegex.test(macAddress)) {
            Swal.fire({
                title: 'Invalid MAC Address',
                text: 'Please enter a valid MAC address in format XX:XX:XX:XX:XX:XX or XX-XX-XX-XX-XX-XX',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
            return false;
        }
    }

    // Command execution validation
    const command = form.elements['post_creation_command'].value.trim();
    if (command) {
        const username = form.elements['guest_username'].value.trim();
        const password = form.elements['guest_password'].value.trim();

        if (!username || !password) {
            Swal.fire({
                title: 'Missing Credentials',
                text: 'Guest OS username and password are required for command execution',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
            return false;
        }
    }

    // Show loading state
    Swal.fire({
        title: 'Creating VM...',
        text: 'Please wait while we create your VM',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    return true;
}

// Statistics page functions
function initializeCharts(metrics) {
    if (!metrics) return;

    // CPU Usage Chart
    new Chart(document.getElementById('cpuChart'), {
        type: 'line',
        data: {
            labels: metrics.cpu_timestamps ?? [],
            datasets: [{
                label: 'CPU Usage (%)',
                data: metrics.cpu_usage ?? [],
                borderColor: '#4CAF50',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'CPU Usage'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Memory Usage Chart
    new Chart(document.getElementById('memoryChart'), {
        type: 'line',
        data: {
            labels: metrics.memory_timestamps ?? [],
            datasets: [{
                label: 'Memory Usage (%)',
                data: metrics.memory_usage ?? [],
                borderColor: '#2196F3',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Memory Usage'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Disk Usage Chart
    new Chart(document.getElementById('diskChart'), {
        type: 'line',
        data: {
            labels: metrics.disk_timestamps ?? [],
            datasets: [{
                label: 'Disk Usage (%)',
                data: metrics.disk_usage ?? [],
                borderColor: '#FF9800',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Disk Usage'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Network Usage Chart
    new Chart(document.getElementById('networkChart'), {
        type: 'line',
        data: {
            labels: metrics.network_timestamps ?? [],
            datasets: [{
                label: 'Network Usage (Mbps)',
                data: metrics.network_usage ?? [],
                borderColor: '#9C27B0',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Network Usage'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Edit VM Modal Functions
function showEditVMModal(vmId, vmName) {
    document.getElementById('editVMModal').style.display = 'block';
    document.getElementById('edit_vm_id').value = vmId;
    
    // Fetch VM details and populate form
    fetchFromAPI('backend.php', {
        action: 'get_vm_details',
        vm_id: vmId
    })
    .then(data => {
        if (data.success) {
            const vm = data.vm;
            // Populate form fields with VM data
            document.querySelector('#editVMForm input[name="vm_name"]').value = vm.name;
            document.querySelector('#editVMForm input[name="cpu_count"]').value = vm.cpu_count;
            document.querySelector('#editVMForm input[name="cores_per_socket"]').value = vm.cores_per_socket;
            document.querySelector('#editVMForm input[name="memory_size"]').value = vm.memory_size_MiB;
            
            // Update dropdowns
            updateClusters(vm.cluster);
            updateResourcePools(vm.cluster, vm.resource_pool);
            updateNetworks(vm.network);
            updateStoragePolicies(vm.storage_policy);
        } else {
            Swal.fire({
                title: 'Error',
                text: 'Failed to fetch VM details',
                icon: 'error'
            });
        }
    });
}

function hideEditVMModal() {
    document.getElementById('editVMModal').style.display = 'none';
    document.getElementById('editVMForm').reset();
}

// Update form submission to handle both create and edit
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.getElementById('createVMForm');
    const editForm = document.getElementById('editVMForm');

    if (createForm) {
        createForm.addEventListener('submit', handleFormSubmit);
    }
    
    if (editForm) {
        editForm.addEventListener('submit', handleFormSubmit);
    }
});

function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const isEdit = form.id === 'editVMForm';

    // Validate form fields
    if (!validateVMForm(form)) {
        return;
    }

    // Submit form data
    fetch('backend.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success',
                text: isEdit ? 'VM updated successfully' : 'VM created successfully',
                icon: 'success'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'An error occurred',
                icon: 'error'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while processing your request',
            icon: 'error'
        });
    });
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // VM Form event listeners
    const createVMForm = document.getElementById('createVMForm');
    if (createVMForm) {
        // Initial calls
        updateResourcePools();

        // Add event listener for datacenter change
        const datacenterSelect = document.getElementsByName('datacenter')[0];
        if (datacenterSelect) {
            datacenterSelect.addEventListener('change', function() {
                updateClusters(this.value);
            });
        }

        // Add event listener for cluster change
        const clusterSelect = document.getElementById('cluster');
        if (clusterSelect) {
            clusterSelect.addEventListener('change', updateResourcePools);
        }

        // Add event listener for MAC address type change
        const macTypeSelect = document.getElementById('mac_type');
        if (macTypeSelect) {
            macTypeSelect.addEventListener('change', toggleMacAddress);
        }

        // Add event listener for OS type change
        const osTypeSelect = document.getElementById('os_type');
        if (osTypeSelect) {
            osTypeSelect.addEventListener('change', updateCommandPlaceholder);
        }

        // Add event listener for post-creation command
        const commandTextarea = document.getElementsByName('post_creation_command')[0];
        if (commandTextarea) {
            commandTextarea.addEventListener('input', toggleCommandOptions);
        }

        // Add form validation
        createVMForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateVMForm(this)) {
                this.submit();
            }
        });
    }

    // Initialize charts if on statistics page
    const metrics = window.vmMetrics; // This will be set in statistics.php
    if (metrics) {
        initializeCharts(metrics);
    }
}); 