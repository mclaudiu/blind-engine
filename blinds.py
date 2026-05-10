import asyncio
import sys
import json
from bleak import BleakClient

DEVICES = {
    "living": "02:0B:B9:5F:CD:E4",
    "balcon": "02:EE:7D:97:72:A0",
    "blind":  "02:B2:00:F2:16:A0"
}

# Based on your discovery script:
UUID_NOTIFY = "0000fe51-0000-1000-8000-00805f9b34fb" # Handle 13
UUID_WRITE  = "0000fe51-0000-1000-8000-00805f9b34fb" # Standard command door

battery_level = "Unknown"

def calculate_checksum(data):
    checksum = 0
    for byte in data:
        checksum ^= byte
    return checksum

def notification_handler(sender, data):
    global battery_level
    # Convert bytes to hex for the PHP console log
    print(f"DEBUG_RAW_HEX: {data.hex()}")
    
    # Check for battery packet (starts with 9a a2)
    if len(data) >= 8 and data[1] == 0xa2:
        if data[2] == 0x05: # Brunt 5-byte format
            battery_level = f"{data[7]}%"
        else: # Standard 1-byte format
            battery_level = f"{data[3]}%"

async def run_blind(name, action, val=None):
    global battery_level
    mac = DEVICES[name]
    
    try:
        # Increased timeout for Rocky Linux Bluetooth stability
        async with BleakClient(mac, timeout=15.0) as client:
            if action == "move":
                cmd = [0x9a, 0x0d, 0x01, int(val)]
                packet = bytearray(cmd + [calculate_checksum(cmd)])
                await client.write_gatt_char(UUID_WRITE, packet, response=False)
                return f"SUCCESS: {name} moved to {val}%"
            
            elif action == "battery":
                # Start listening first
                await client.start_notify(UUID_NOTIFY, notification_handler)
                
                # Send Query: 9a a2 01 01 (Check Battery)
                query = [0x9a, 0xa2, 0x01, 0x01]
                packet = bytearray(query + [calculate_checksum(query)])
                await client.write_gatt_char(UUID_WRITE, packet, response=False)
                
                # Wait for the motor to reply
                await asyncio.sleep(4.0)
                
                try:
                    await client.stop_notify(UUID_NOTIFY)
                except:
                    pass # Ignore errors during disconnect
                    
                return f"BATTERY_{name.upper()}:{battery_level}"

    except Exception as e:
        # If we got the battery but a timeout/disconnect error happened at the end, 
        # we still want to report the battery to the PHP page.
        if action == "battery" and battery_level != "Unknown":
            return f"BATTERY_{name.upper()}:{battery_level}"
        return f"ERROR: {str(e)}"

if __name__ == "__main__":
    if len(sys.argv) < 3:
        sys.exit(1)
    
    device_name = sys.argv[1].lower()
    command_action = sys.argv[2].lower()
    value = sys.argv[3] if len(sys.argv) > 3 else None
    
    result = asyncio.run(run_blind(device_name, command_action, value))
    print(result)