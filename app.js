// Store session ID
let vmwareSessionId = null;

// Utility function for making AJAX requests
async function fetchFromAPI(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// Function to update resource pools based on selected cluster
async function updateResourcePools() {
    const clusterId = document.getElementById('cluster').value;
    if (!clusterId) return;

    try {
        const data = await fetchFromAPI(`backend.php?action=get_resource_pools&cluster=${clusterId}`);
        const resourcePoolSelect = document.getElementById('resource_pool');
        resourcePoolSelect.innerHTML = '<option value="">Select Resource Pool</option>';
        
        data.forEach(pool => {
            const option = document.createElement('option');
            option.value = pool.resource_pool;
            option.textContent = pool.name;
            resourcePoolSelect.appendChild(option);
        });
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'Failed to fetch resource pools',
            icon: 'error'
        });
    }
}

// Function to update clusters based on selected datacenter
async function updateClusters() {
    const datacenterId = document.getElementById('datacenter').value;
    if (!datacenterId) return;

    try {
        const data = await fetchFromAPI(`backend.php?action=get_clusters&datacenter=${datacenterId}`);
        const clusterSelect = document.getElementById('cluster');
        clusterSelect.innerHTML = '<option value="">Select Cluster</option>';
        
        data.forEach(cluster => {
            const option = document.createElement('option');
            option.value = cluster.cluster;
            option.textContent = cluster.name;
            clusterSelect.appendChild(option);
        });
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'Failed to fetch clusters',
            icon: 'error'
        });
    }
}

// Function to toggle MAC address input visibility
function toggleMacAddress() {
    const macType = document.getElementById('mac_type').value;
    const macAddressGroup = document.getElementById('mac_address_group');
    macAddressGroup.style.display = macType === 'MANUAL' ? 'block' : 'none';
}

// Function to toggle command options visibility
function toggleCommand() {
    const showCommand = document.getElementById('show_command').checked;
    const commandOptions = document.getElementById('command_options');
    commandOptions.style.display = showCommand ? 'block' : 'none';
}

// Function to show create VM modal
function showCreateVMModal() {
    const modal = document.getElementById('createVMModal');
    const form = document.getElementById('vmForm');
    form.reset();
    form.querySelector('#form_action').value = 'create_vm';
    modal.style.display = 'block';
}

// Function to show edit VM modal
async function showEditVMModal(vmId, vmName) {
    try {
        const data = await fetchFromAPI(`backend.php?action=get_vm_details&vm_id=${vmId}`);
        const modal = document.getElementById('editVMModal');
        const form = document.getElementById('editVMForm');
        
        if (data.success && data.vm) {
            const vm = data.vm;
            form.querySelector('input[name="vm_name"]').value = vm.name;
            form.querySelector('input[name="cpu_count"]').value = vm.cpu_count;
            form.querySelector('input[name="cores_per_socket"]').value = vm.cores_per_socket;
            form.querySelector('input[name="memory_size"]').value = vm.memory_size_MiB;
        }
        
        modal.style.display = 'block';
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'Failed to fetch VM details',
            icon: 'error'
        });
    }
}

// Function to hide modals
function hideModal() {
    document.getElementById('createVMModal').style.display = 'none';
    document.getElementById('editVMModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const createModal = document.getElementById('createVMModal');
    const editModal = document.getElementById('editVMModal');
    if (event.target === createModal || event.target === editModal) {
        hideModal();
    }
}

// Form submission handler
document.getElementById('vmForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    // Validate required fields
    const requiredFields = [
        'vm_name', 'template', 'datacenter', 'cluster', 'resource_pool',
        'network', 'storage_policy', 'ram', 'cpu_count', 'cores_per_socket', 'disk'
    ];
    
    const missingFields = [];
    requiredFields.forEach(field => {
        const element = document.getElementById(field);
        if (!element.value) {
            missingFields.push(element.previousElementSibling.textContent.replace(' *', ''));
        }
    });
    
    if (missingFields.length > 0) {
        Swal.fire({
            title: 'Validation Error',
            text: `Please fill in the following required fields: ${missingFields.join(', ')}`,
            icon: 'error'
        });
        return;
    }
    
    // Additional validation for MAC address if manual type is selected
    const macType = document.getElementById('mac_type').value;
    if (macType === 'MANUAL') {
        const macAddress = document.getElementById('mac_address').value;
        const macRegex = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
        if (!macRegex.test(macAddress)) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please enter a valid MAC address in the format XX:XX:XX:XX:XX:XX',
                icon: 'error'
            });
            return;
        }
    }
    
    // Submit form
    try {
        const formData = new FormData(this);
        const response = await fetch('backend.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            Swal.fire({
                title: 'Success',
                text: result.message || 'Operation completed successfully',
                icon: 'success'
            }).then(() => {
                hideModal();
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: result.message || 'Operation failed',
                icon: 'error'
            });
        }
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while processing your request',
            icon: 'error'
        });
    }
});

// Snapshot Modal Functions
function showCreateSnapshotModal() {
    const modal = document.getElementById('createSnapshotModal');
    modal.style.display = 'block';
}

function hideCreateSnapshotModal() {
    const modal = document.getElementById('createSnapshotModal');
    modal.style.display = 'none';
}

// Handle snapshot form submission
document.getElementById('createSnapshotForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    try {
        const formData = new FormData(this);
        formData.append('action', 'create_snapshot');
        
        const response = await fetch('backend.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            Swal.fire({
                title: 'Success',
                text: 'Snapshot created successfully',
                icon: 'success'
            }).then(() => {
                hideCreateSnapshotModal();
                loadSnapshots(); // Reload snapshots list
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: result.message || 'Failed to create snapshot',
                icon: 'error'
            });
        }
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while creating the snapshot',
            icon: 'error'
        });
    }
});

// Edit VM form submission handler
document.getElementById('editVMForm')?.addEventListener('submit', async function(event) {
    event.preventDefault();
    
    try {
        const formData = new FormData(this);
        const response = await fetch('backend.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        if (result.success) {
            Swal.fire({
                title: 'Success',
                text: result.message || 'VM updated successfully',
                icon: 'success'
            }).then(() => {
                hideModal();
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: result.message || 'Failed to update VM',
                icon: 'error'
            });
        }
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: 'An error occurred while updating the VM',
            icon: 'error'
        });
    }
}); 