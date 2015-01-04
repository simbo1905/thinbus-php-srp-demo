# Thinbus SRP PHP Demo

Copyright (c) Simon Massey, 2015

Demo of Secure Remote Password (SRP-6a) protocol implementation of a browser authenticating to a PHP server using the [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) Javascript library. This work is based on [Ruslan Zazvacky's SRP PHP demo](https://github.com/RuslanZavacky/srp-6a-demo). 

The core PHP library files are in the `/thinbus` folder:

* `thinbus/thinbus-srp-config.php` SRP configuration global variables. Must be included before the thinbus library code.  
* `thinbus/BigInteger.php` pear.php.net [BigInteger math package](http://pear.php.net/package/BigInteger)
* `thinbus/srand.php` strong random numbers from [George Argyros](https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP) avoiding known buggy versions of random libraries. 
* `thinbus/thinbus-srp.php` PHP port of the Thinbus SRP6JavaClientSession based on code by [Ruslan Zavacky](https://github.com/RuslanZavacky/srp-6a-demo)

The file `thinbus-srp-config.php` contains the SRP constants which looks something like: 

```
$SRP6CryptoParams = [
    "N_base10" => "19502997308..."
    "g_base10" => "2",
    "k_base16" => "1a3d1769e1d..."
    "H" => "sha256"
];
```

The numeric constants must match the values configured in the JavaScript; see the [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js). Consider create your own large safe prime values using openssl using the Thinbus instructions. 

The demo application comprises of the following top level php files. It saves user SRP data in a [SQLite](http://php.net/manual/en/book.sqlite.php) flat file database at `/tmp/srp_db.txt` which can be changed in the file `require.php`. 

* `require.php` a fragment to pull in the SRP constants, Thinbus library, RedBean library. It initialises the SQLite database. 
* `RedBean.php` [RedBeanPHP](http://redbeanphp.com) "an easy-to-use, on-the-fly ORM for PHP. It's 'zero config', relying on strict conventions instead."
* `register.php` saves the user email, salt and verifier into the flat file database using RedBean  
* `login.php` loads the user salt and verifier using RedBean to perform the SRP6a protocol  

To login the browser first uses the email to fetch the salt `s` and the server challenge `B` using ajax. It then generates a random `A` and computes the password proof `B` which are posted together with the email as the users credentials. 

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