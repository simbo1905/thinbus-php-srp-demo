# Thinbus SRP PHP Demo

Copyright (c) Simon Massey, 2015

Demo of Secure Remote Password (SRP-6a) protocol implementation of a browser authenticating to a PHP server using the [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) Javascript library. 
This work is based on [Ruslan Zazvacky's SRP PHP demo](https://github.com/RuslanZavacky/srp-6a-demo) and registers users into a SQLite database. 

The core PHP library files are in the `thinbus` folder:

* `thinbus/thinbus-srp-config.php` SRP configuration global variables. Must be included before the thinbus library code. Must match the values configured in the JavaScript. 
* `thinbus/thinbus-srp.php` PHP port of the Thinbus SRP6JavaClientSession based on code by [Ruslan Zavacky](https://github.com/RuslanZavacky/srp-6a-demo).
* `thinbus/BigInteger.php` pear.php.net [BigInteger math package](http://pear.php.net/package/BigInteger).
* `thinbus/srand.php` strong random numbers from [George Argyros](https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP) avoiding known buggy versions of random libraries. 

The file `thinbus-srp-config.php` contains the SRP constants which looks something like: 

```
$SRP6CryptoParams = [
    "N_base10" => "19502997308..."
    "g_base10" => "2",
    "k_base16" => "1a3d1769e1d..."
    "H" => "sha256"
];
```

The numeric constants must match the values configured in the JavaScript; see the [Thinbus documentation](https://bitbucket.org/simon_massey/thinbus-srp-js). Consider creating your own large safe prime values using openssl using the Thinbus instructions. 

The demo application comprises of the following top level php files. It saves user SRP data in a [SQLite](http://php.net/manual/en/book.sqlite.php) flat file database at `/tmp/srp_db.txt` as configured in the file `require.php`: 

* `require.php` a fragment to pull in the SRP constants, Thinbus library, RedBean library. It also initialises the SQLite database. 
* `rb.php` [RedBeanPHP](http://redbeanphp.com) "an easy-to-use, on-the-fly ORM for PHP" used to abstract the database solely from the convenience of the demo.   
* `register.php` saves the user email, salt and verifier.  
* `login.php` loads the user salt and verifier to perform the SRP6a protocol to authenticated the user. 

To authenticated a user the browser first uses AJAX to fetch the salt `s` and the server challenge `B` using ajax. 
It then generates a random `A` and computes the password proof `M1` which are concated together and used to login. 
If the user password proof is correct the `login.php` code sets two session variables:

* `SRP_USER_ID` the authenticated user id
* `SRP_SESSION_KEY` a strong shared session `K=H(s)` which could be used for further cryptography

You can use the authenticated `SRP_USER_ID` variable to protect senstive pages with something like the following: 

```
if( empty($_SESSION['SRP_USER_ID'] ) ) {
    // user is not authenticated
    exit();
} else {
    $msg = "Hello ".$_SESSION['SRP_USER_ID']."!";
    echo $msg;
}
```

Please read the recommendations in the main thinbus documentation and takes additional steps such as using HTTPS and encrypting the password verifier in the database which are not covered in this demo. 

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