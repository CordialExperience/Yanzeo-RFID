# Yanzeo-RFID

## Objective
This codebase demonstrates an ability to track a physical thing and generate 
events on Cordial's platform related to the physical item.

## Physical Bits
This code was built to run on Raspberry Pi 3 B+.  It likely functions on 
other hardware, but is untested.  Here's a list of bits we acquired to make
this project a reality:
- Raspberry Pi 3 B+ 
- Micro Connectors Raspberry Pi Case
- Sabrent USB-to-Serial Adapter (Prolific chipset)
- Miscelaneous power cords


## Software Bits
- PHP 7
    - `dio` PECL extension
- Appropriate USB-to-Serial driver (typically included in Linux now)
- _Optional:_ `supervisord` to manage and start the reader automatically on boot

## Install & Run
We used Raspbian Lite as the starting point.
#### Install PHP CLI and development tools
```bash
    $ apt-get update
    $ apt-get install php-dev php-cli
```
#### Install the Direct IO Extension
```bash
    $ pecl install channel://pecl.php.net/dio-0.1.0
    $ echo "extension=dio.so" > /etc/php/7.0/mods-available/dio.ini
    $ phpenmod dio
```    

#### Configure serial port (may or may not be required)
```bash
    # This may be different for your system
    $ ln -b /dev/ttyUSB0 /dev/ttyS0  
```  

#### Get the code
```bash 
    $ git clone https://github.com/CordialExperience/Yanzeo-RFID
```

#### Run the code
```bash
    $ cd Yanzeo-RFID
    $ CORDIAL_API_KEY=<insert_api_key> ./bin/read /dev/ttyS0 <event_name>
```