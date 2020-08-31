# Travian Crop Finder

This script first logs in with your account and gets all cookies and csrf values. Then it finds crops around you with the information specified in config.

## Run
    php crop_finder.php

## Config

#### Server
    $server = 'ts4.travian.de';
    
#### Login info
    $username = 'xxx';
    $password = 'xxx';

#### Location
    $where = [42, -33];
    //        x    y
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
