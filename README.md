# Travian Crop Finder

## Run
    php crop_finder.php

## Config

#### Server
    $server = 'ts4.travian.de';
    
#### API Key
    $api_key = 'xxx';
Get the api key by looking at ```Authorization``` header in travian ajax requests for example in maps page

#### Location
    $where = [42, -33];
Location of your village

#### Distance
    $distance = 10;
How far the crops can be
    
#### Type of village to find
    $crop = 15;
15c or 9c

#### Proxy
    $use_proxy = false;
    $proxy_ip = '127.0.0.1';
    $proxy_port = '9150';
