# blind-engine
Not replacing, but helps you control your blinds


Implemented on **Rocky Linux 9**<BR>
Your blinds have to have the low and high position set from the app (not available in app store) :(<BR>
A name to each device ... will help too<BR>

**Bluetooth development libraries:**<BR>

sudo dnf install bluez bluez-libs python3-devel<BR>
bluez: The official Linux Bluetooth protocol stack.<BR>
python3-devel: Necessary if pip needs to compile any part of the Bluetooth library for your specific kernel.<BR>


**Python Packages:**<BR><BR>
pip install bleak dbus-fast<BR>
bleak (Bluetooth Low Energy Analysis and Keeping): This is the primary library used in blinds.py and sensor.py. It handles the connection, discovery, and notifications for your Brunt motors and the Xiaomi sensor.<BR>
dbus-fast: This is a high-performance library that bleak uses on Linux to talk to the system's Bluetooth daemon (BlueZ). Installing it explicitly often fixes the EOFError or connection drops you saw earlier.<BR>


**run in console:**<BR>
bluetoothctl<BR>
scan on<BR>

you will get something like:<BR>
[NEW] Device 02:0B:B9:5F:CD:E4 Living<BR>
[NEW] Device 02:EE:7D:97:72:A0 Balcon<BR>
[NEW] Device 02:B2:00:F2:16:A0 Blind<BR>

Put your mac addresses into blinds.py<BR>
Use blinds.php on you web server. Adjust the location for blinds.py<BR>
blinds.py<BR>

In blinds.php, there is a small console that will show you what it is executed.<BR>

If you did all the above but the scripts does not work, use blinds_discover.py (set you device mac address inside) to get your device<BR>
UUID_NOTIFY = "0000fe51-0000-1000-8000-00805f9b34fb" # Handle 13<BR>

**blinds.py works from command line too:**<BR>
python3 /var/www/html/blinds.py living battery<BR>
python3 /var/www/html/blinds.py living move 47<BR>
