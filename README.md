# Thinbus SRP PHP Demo

Copyright (c) Simon Massey, 2015

Demo of Secure Remote Password (SRP-6a) protocol implementation of a browser authenticating to a PHP server using the [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) Javascript library. 

This work is based on [Ruslan Zazvacky's SRP PHP demo](https://github.com/RuslanZavacky/srp-6a-demo). 

## Code

```
git clone https://simon_massey@bitbucket.org/simon_massey/thinbus-php.git
cd thinbus-php
```

# Usage Guide

The core PHP library files are in the `/thinbus` folder:

```
thinbus/BigInteger.php
thinbus/srand.php
thinbus/thinbus-srp-config.php
thinbus/thinbus-srp.php
```

The file `thinbus-srp-config.php` contains the SRP constants: 

```
$SRP6CryptoParams = [
    "N_base10" => "19502997308..."
    "g_base10" => "2",
    "k_base16" => "1a3d1769e1d..."
    "H" => "sha256"
];
```

The values used must match the values configured in the JavaScript; see the [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) including the java commandline tool to create your own large safe prime values using openssl. 

The `BigIntger.php` script is the [pear.php.net BigInteger](http://pear.php.net/package/BigInteger) math package. The library `srand.php` is by [George Argyros](https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP) which supplies strong random numbers avoiding known buggy versions of random libraries. 

The script `thinbus-srp.php` is a port of the Thinbus SRP6JavaClientSession class based on the SRP PHP code by [Ruslan Zavacky](https://github.com/RuslanZavacky/srp-6a-demo). 

The demo application comprises of the following top level php files. It saves user SRP data in a [SQLite](http://php.net/manual/en/book.sqlite.php) flat file database at `/tmp/srp_db.txt` which can be changed in the file `require.php`. 

```
require.php // includes the SRP config, Thinbus, RedBean library, and initialises the SQLite database. 
RedBean.php // http://redbeanphp.com "RedBeanPHP is an easy-to-use, on-the-fly ORM for PHP. It's 'zero config', relying on strict conventions instead." 
register.php // saves the user email, salt and verifier into the flat file database. 
login.php // performs the SRP6a protocol. first it AJAX posts the email and gets back the salt 's' and a server challenge 'B'. second it posts 'A'+'M' as the password proof.  
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