<?php
// Handle AJAX requests from the browser
if (isset($_GET['action'])) {
    $dev = $_GET['device'];
    $act = $_GET['action']; // 'move' or 'battery'
    $val = isset($_GET['val']) ? $_GET['val'] : '0';
    
    // We execute and catch the output to display in the console
    // Ensure the path to blinds.py is correct for your Rocky Linux setup
    $output = shell_exec("python3 /var/www/html/blinds.py $dev $act $val 2>&1");
    echo $output; 
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Blinds Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background-color: #336699; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; text-align: center; padding: 20px; }
        .card { background: rgba(0,0,0,0.25); border-radius: 15px; padding: 20px; margin: 15px auto; max-width: 450px; border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        h2 { margin: 0; text-transform: capitalize; font-weight: 400; }
        .battery-text { font-size: 1.1em; color: #00ffcc; font-weight: bold; text-shadow: 0 0 5px rgba(0,255,204,0.3); }
        
        .console { background: #000; color: #00ff00; font-family: 'Courier New', monospace; padding: 10px; margin: 20px auto; max-width: 600px; height: 160px; overflow-y: scroll; text-align: left; border-radius: 8px; font-size: 12px; border: 2px solid #224466; line-height: 1.4; }
        
        button { background: #1a334d; color: white; border: 1px solid #5588aa; padding: 10px 18px; border-radius: 6px; cursor: pointer; transition: 0.2s; font-size: 14px; }
        button:hover { background: #2a4d70; border-color: #fff; }
        button:active { background: #0d1a26; transform: translateY(1px); }
        
        .btn-battery { padding: 4px 10px; font-size: 11px; margin-left: 10px; background: #224466; border-color: #4477aa; }

        .slider { -webkit-appearance: none; width: 100%; height: 10px; border-radius: 5px; background: #112233; outline: none; margin: 25px 0; border: 1px solid #224466; }
        .slider::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 26px; height: 26px; border-radius: 50%; background: #ffffff; cursor: pointer; border: 2px solid #336699; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
    </style>
</head>
<body>

    <h1 style="font-weight: 300; letter-spacing: 2px;">🏠 BLIND CONTROL</h1>

    <?php 
    // The list of your devices
    $devices = ['living', 'balcon', 'blind']; 
    foreach ($devices as $d): 
    ?>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
            <h2><?php echo $d; ?></h2>
            <div style="display: flex; align-items: center;">
                <span class="battery-text" id="bat-<?php echo $d; ?>">--%</span>
                <button class="btn-battery" onclick="getBattery('<?php echo $d; ?>')">CHECK ⚡</button>
            </div>
        </div>
        
        <input type="range" min="0" max="100" value="50" class="slider" id="slide-<?php echo $d; ?>" 
               onchange="runCommand('<?php echo $d; ?>', 'move', this.value)">
        
        <div style="display: flex; justify-content: space-between; gap: 10px;">
            <button style="flex: 1;" onclick="updateUI('<?php echo $d; ?>', 0)">OPEN (0)</button>
            <button style="flex: 1;" onclick="updateUI('<?php echo $d; ?>', 100)">CLOSE (100)</button>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="console" id="log">--- System Ready ---<br></div>

    <script>
    // 1. Log messages to the green console window
    function log(msg) {
        const div = document.getElementById('log');
        div.innerHTML += `> ${msg}<br>`;
        div.scrollTop = div.scrollHeight;
    }

    // 2. Synchronize the slider position when buttons are pressed
    function updateUI(device, val) {
        document.getElementById('slide-' + device).value = val;
        runCommand(device, 'move', val);
    }

    // 3. Send Move commands to the Python script
    function runCommand(device, action, val) {
        log(`Executing: <strong>${action}</strong> on <strong>${device}</strong> (Target: ${val}%)...`);
        fetch(`?action=${action}&device=${device}&val=${val}`)
            .then(r => r.text())
            .then(data => {
                log(data); // Displays the raw output from Python
            })
            .catch(err => log("Fetch Error: " + err));
    }

    // 4. Send Battery query and parse the result using Regex
    function getBattery(device) {
        log(`Querying battery for <strong>${device}</strong>...`);
        fetch(`?action=battery&device=${device}`)
            .then(r => r.text())
            .then(data => {
                log(data);
                
                // Regular Expression: Looks for BATTERY_NAME:XX%
                const regex = new RegExp(`BATTERY_${device.toUpperCase()}:(\\d+%)`);
                const match = data.match(regex);
                
                if (match && match[1]) {
                    document.getElementById('bat-' + device).innerText = match[1];
                    log(`<span style="color: #00ffcc;">Success: ${device} battery is ${match[1]}</span>`);
                } else {
                    log(`<span style="color: #ff9900;">Notice: Query finished, but no percentage found.</span>`);
                }
            })
            .catch(err => log("Fetch Error: " + err));
    }
    </script>
</body>
</html>