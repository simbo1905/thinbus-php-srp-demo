# Thinbus SRP PHP Demo

Copyright (c) Simon Massey, 2015

Demo of Secure Remote Password (SRP-6a) protocol implementation of a browser authenticating to a PHP server using the [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) Javascript library. 

This work is based on [Ruslan Zazvacky's SRP PHP demo](https://github.com/RuslanZavacky/srp-6a-demo). 

## Configuration

The file `srp-config.php` contains the SRP constants: 

```
$SRP6CryptoParams = [
    "N_base10" => "19502997308..."
    "g_base10" => "2",
    "k_base16" => "1a3d1769e1d..."
    "H" => "sha256"
];
```

The values used must match the values configured in the javascript.  

See the thinbus documentation including the java tool to create your own large safe prime values using openssl. 

## Code

```
git clone https://simon_massey@bitbucket.org/simon_massey/thinbus-php.git
cd thinbus-php
```

## License

```
   Copyright 2015 Simon Massey

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
```
   
End.