import asyncio
from bleak import BleakClient

MAC_ADDRESS = "02:EE:7D:97:72:A0"

async def discover():
    print(f"Connecting to {MAC_ADDRESS} to map services...")
    async with BleakClient(MAC_ADDRESS) as client:
        print("Connected! Listing all characteristics:")
        for service in client.services:
            print(f"\nService: {service.uuid}")
            for char in service.characteristics:
                print(f"  - Characteristic: {char.uuid} | Handle: {char.handle} | Props: {char.properties}")

if __name__ == "__main__":
    asyncio.run(discover())
