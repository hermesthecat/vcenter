<?php
// This file contains the VM creation form fields
?>
<div class="form-group">
    <label>VM Name:</label>
    <input type="text" name="vm_name" class="form-control" required>
</div>

<div class="form-group">
    <label>Template:</label>
    <select name="template" class="form-control" required>
        <?php foreach ($templates['value'] as $template): ?>
            <option value="<?php echo htmlspecialchars($template['vm']); ?>">
                <?php echo htmlspecialchars($template['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label>Datacenter:</label>
    <select name="datacenter" class="form-control" required>
        <?php foreach ($datacenters['value'] as $datacenter): ?>
            <option value="<?php echo htmlspecialchars($datacenter['datacenter']); ?>">
                <?php echo htmlspecialchars($datacenter['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label>Cluster:</label>
    <select name="cluster" id="cluster" class="form-control" required onchange="updateResourcePools()">
        <?php foreach ($clusters['value'] as $cluster): ?>
            <option value="<?php echo htmlspecialchars($cluster['cluster']); ?>">
                <?php echo htmlspecialchars($cluster['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label>Resource Pool:</label>
    <select name="resource_pool" id="resource_pool" class="form-control" required>
        <?php foreach ($resource_pools['value'] as $pool): ?>
            <option value="<?php echo htmlspecialchars($pool['resource_pool']); ?>">
                <?php echo htmlspecialchars($pool['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label>CPU Configuration:</label>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div>
            <label>CPU Count:</label>
            <input type="number" name="cpu_count" class="form-control" required min="1" value="1">
        </div>
        <div>
            <label>Cores Per Socket:</label>
            <input type="number" name="cores_per_socket" class="form-control" required min="1" value="1">
        </div>
    </div>
</div>

<div class="form-group">
    <label>CPU Resource Limits:</label>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div>
            <label>CPU Reservation (MHz):</label>
            <input type="number" name="cpu_reservation" class="form-control" min="0" placeholder="Optional">
            <div class="help-text">Guaranteed minimum CPU allocation</div>
        </div>
        <div>
            <label>CPU Limit (MHz):</label>
            <input type="number" name="cpu_limit" class="form-control" min="0" placeholder="Optional">
            <div class="help-text">Maximum CPU allocation (0 = unlimited)</div>
        </div>
    </div>
</div>

<div class="form-group">
    <label>RAM (GB):</label>
    <input type="number" name="ram" class="form-control" required min="1" value="4">
</div>

<div class="form-group">
    <label>Disk Size (GB):</label>
    <input type="number" name="disk" class="form-control" required min="10" value="40">
</div>

<div class="form-group">
    <label>Disk Provisioning Type:</label>
    <select name="disk_provisioning" class="form-control" required>
        <option value="THIN">Thin Provision</option>
        <option value="THICK_LAZY_ZEROED">Thick Provision Lazy Zeroed</option>
        <option value="THICK_EAGER_ZEROED">Thick Provision Eager Zeroed</option>
    </select>
    <div class="help-text">
        <ul>
            <li><strong>Thin:</strong> Allocates storage on demand (best for storage saving)</li>
            <li><strong>Thick Lazy:</strong> Allocates all space immediately but zeroes on demand</li>
            <li><strong>Thick Eager:</strong> Allocates and zeroes all space immediately (best for performance)</li>
        </ul>
    </div>
</div>

<div class="form-group">
    <label>Network:</label>
    <select name="network" class="form-control" required>
        <?php foreach ($networks['value'] as $network): ?>
            <option value="<?php echo htmlspecialchars($network['network']); ?>">
                <?php echo htmlspecialchars($network['name']) .
                    (isset($network['type']) ? " (" . htmlspecialchars($network['type']) . ")" : ""); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="help-text">
        Network types: STANDARD_PORTGROUP (vSwitch), DISTRIBUTED_PORTGROUP (Distributed vSwitch)
    </div>
</div>

<div class="form-group">
    <label>MAC Address Type:</label>
    <select name="mac_type" id="mac_type" class="form-control" onchange="toggleMacAddress()">
        <option value="GENERATED">Auto-generated</option>
        <option value="MANUAL">Manual Entry</option>
    </select>
</div>

<div class="form-group" id="mac_address_div" style="display: none;">
    <label>MAC Address:</label>
    <input type="text" name="mac_address" class="form-control"
        pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$"
        placeholder="00:11:22:33:44:55">
    <div class="help-text">Format: XX:XX:XX:XX:XX:XX or XX-XX-XX-XX-XX-XX</div>
</div>

<div class="form-group">
    <label>Guest OS Type:</label>
    <select name="os_type" id="os_type" class="form-control" onchange="updateCommandPlaceholder()">
        <option value="linux">Linux</option>
        <option value="windows">Windows</option>
    </select>
</div>

<div class="form-group">
    <label>Execute Command After Creation:</label>
    <textarea name="post_creation_command" id="command_textarea" class="form-control" rows="5"
        style="font-family: monospace;"></textarea>
    <div class="help-text" id="command_example"></div>
</div>

<div class="form-group command-options" style="display: none;">
    <h3>Command Execution Settings</h3>
    <div class="form-group">
        <label>Guest OS Username:</label>
        <input type="text" name="guest_username" class="form-control">
    </div>

    <div class="form-group">
        <label>Guest OS Password:</label>
        <input type="password" name="guest_password" class="form-control">
    </div>

    <div class="form-group">
        <label>Command Execution Timeout (seconds):</label>
        <input type="number" name="command_timeout" class="form-control"
            value="60" min="30" max="300">
        <div class="help-text">Time to wait for VM to be ready (30-300 seconds)</div>
    </div>
</div>

<div class="form-group">
    <label>Storage Policy:</label>
    <select name="storage_policy" class="form-control" required>
        <option value="">Select Storage Policy</option>
        <?php if (isset($storage_policies['value'])): ?>
            <?php foreach ($storage_policies['value'] as $policy): ?>
                <option value="<?php echo htmlspecialchars($policy['policy']); ?>">
                    <?php echo htmlspecialchars($policy['name']); ?>
                    <?php if (isset($policy['description'])): ?>
                        (<?php echo htmlspecialchars($policy['description']); ?>)
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
    <div class="help-text">
        Select a storage policy to apply to the VM's disks
    </div>
</div>

<button type="submit" class="btn">Create VM</button> 