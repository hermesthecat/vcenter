<?php
// This file contains the VM creation form fields
?>
<form method="POST" id="vmForm">
    <input type="hidden" name="action" value="create_vm" id="form_action">
    <input type="hidden" name="vm_id" id="vm_id">

    <div class="form-group">
        <label for="vm_name">VM Name *</label>
        <input type="text" id="vm_name" name="vm_name" required>
    </div>

    <div class="form-group">
        <label for="template">Template *</label>
        <select id="template" name="template" required>
            <option value="">Select Template</option>
            <?php if ($templates && isset($templates['value'])): ?>
                <?php foreach ($templates['value'] as $template): ?>
                    <option value="<?php echo htmlspecialchars($template['template']); ?>">
                        <?php echo htmlspecialchars($template['name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="datacenter">Datacenter *</label>
        <select id="datacenter" name="datacenter" required onchange="updateClusters()">
            <option value="">Select Datacenter</option>
            <?php if ($data['datacenters'] && isset($data['datacenters']['value'])): ?>
                <?php foreach ($data['datacenters']['value'] as $datacenter): ?>
                    <option value="<?php echo htmlspecialchars($datacenter['datacenter']); ?>">
                        <?php echo htmlspecialchars($datacenter['name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="cluster">Cluster *</label>
        <select id="cluster" name="cluster" required onchange="updateResourcePools()">
            <option value="">Select Cluster</option>
        </select>
    </div>

    <div class="form-group">
        <label for="resource_pool">Resource Pool *</label>
        <select id="resource_pool" name="resource_pool" required>
            <option value="">Select Resource Pool</option>
        </select>
    </div>

    <div class="form-group">
        <label for="network">Network *</label>
        <select id="network" name="network" required>
            <option value="">Select Network</option>
            <?php if ($data['networks'] && isset($data['networks']['value'])): ?>
                <?php foreach ($data['networks']['value'] as $network): ?>
                    <option value="<?php echo htmlspecialchars($network['network']); ?>">
                        <?php echo htmlspecialchars($network['name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="storage_policy">Storage Policy *</label>
        <select id="storage_policy" name="storage_policy" required>
            <option value="">Select Storage Policy</option>
            <?php if ($data['storage_policies'] && isset($data['storage_policies']['value'])): ?>
                <?php foreach ($data['storage_policies']['value'] as $policy): ?>
                    <option value="<?php echo htmlspecialchars($policy['policy']); ?>">
                        <?php echo htmlspecialchars($policy['name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="ram">RAM (GB) *</label>
        <input type="number" id="ram" name="ram" min="1" required>
    </div>

    <div class="form-group">
        <label for="cpu_count">CPU Count *</label>
        <input type="number" id="cpu_count" name="cpu_count" min="1" required>
    </div>

    <div class="form-group">
        <label for="cores_per_socket">Cores Per Socket *</label>
        <input type="number" id="cores_per_socket" name="cores_per_socket" min="1" required>
    </div>

    <div class="form-group">
        <label for="disk">Disk Size (GB) *</label>
        <input type="number" id="disk" name="disk" min="1" required>
    </div>

    <div class="form-group">
        <label for="disk_provisioning">Disk Provisioning *</label>
        <select id="disk_provisioning" name="disk_provisioning" required>
            <option value="thin">Thin</option>
            <option value="thick">Thick</option>
            <option value="eagerZeroedThick">Eager Zeroed Thick</option>
        </select>
    </div>

    <div class="form-group">
        <label for="mac_type">MAC Address Type *</label>
        <select id="mac_type" name="mac_type" required onchange="toggleMacAddress()">
            <option value="GENERATED">Generated</option>
            <option value="MANUAL">Manual</option>
        </select>
    </div>

    <div class="form-group" id="mac_address_group" style="display: none;">
        <label for="mac_address">MAC Address</label>
        <input type="text" id="mac_address" name="mac_address" pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$">
        <small>Format: XX:XX:XX:XX:XX:XX</small>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" id="show_command" onchange="toggleCommand()">
            Execute Command After Creation
        </label>
    </div>

    <div id="command_options" style="display: none;">
        <div class="form-group">
            <label for="os_type">Guest OS Type</label>
            <select id="os_type" name="os_type">
                <option value="linux">Linux</option>
                <option value="windows">Windows</option>
            </select>
        </div>

        <div class="form-group">
            <label for="guest_username">Guest Username</label>
            <input type="text" id="guest_username" name="guest_username">
        </div>

        <div class="form-group">
            <label for="guest_password">Guest Password</label>
            <input type="password" id="guest_password" name="guest_password">
        </div>

        <div class="form-group">
            <label for="post_creation_command">Command to Execute</label>
            <textarea id="post_creation_command" name="post_creation_command"></textarea>
        </div>

        <div class="form-group">
            <label for="command_timeout">Command Timeout (seconds)</label>
            <input type="number" id="command_timeout" name="command_timeout" value="60" min="1">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-submit">Submit</button>
        <button type="button" class="btn-cancel" onclick="hideModal()">Cancel</button>
    </div>
</form> 