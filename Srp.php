<?php
/*
 * Copyright 2014 Ruslan Zavacky <ruslan.zavackiy@gmail.com>
 * Copyright 2015 Simon Massey
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class Srp
{

    /**
     * @var \BigInteger Password salt
     */
    protected $salt;

    /**
     * @var \BigInteger|string
     */
    protected $N;

    protected $g;

    protected $k;

    protected $v;

    protected $userID;

    protected $A;

    protected $Ahex;

    /**
     * @var \BigInteger|null Secure Random Number
     */
    protected $b = null;

    /**
     * @var \BigInteger|null
     */
    protected $B = null;

    protected $Bhex;

    protected $M;

    protected $HAMK;
    
    /**
     * name of the hashing algorith e.g. "sha256"
     * @var string
     */
    protected $H;

    public function stripLeadingZeros($str) {
        return ltrim($str, '0');
    }
    
    /**
     * @param string $N_base10str
     *            The N crypto parameter as string in base 10. Must match the parameter the client is using.
     * @param string $g_base10str
     *            The g crypto parameter as string in base 10. Must match the parameter the client is using.
     * @param string $k_base16str
     *            The k value as string in base 16. Must match the parameter that the client is using (signed bits and binary padding means Java libs create a specific value).
     * @param string $Hstr
     *            The name of the hashing algorith to use e.g. 'sha256'
     */
    public function __construct($N_base10str, $g_base10str, $k_base16str, $Hstr)
    {
        $this->N = new BigInteger($N_base10str, 10);
        $this->g = new BigInteger($g_base10str, 10);
        $this->k = new BigInteger($k_base16str, 16);
        $this->H = $Hstr;
    }
    /**
     *
     * @param unknown $userID The user id 'I'
     * @param unknown $salt_base16str The user salt 's'
     * @param unknown $v_base16str The user verifier 'v'
     * @return string The server challenge 'B'
     */
    public function step1($userID, $salt_base16str, $v_base16str)
    {
        $this->salt = $salt_base16str;
        $this->v = new BigInteger($v_base16str, 16);
        $this->userID = $userID;
        
        while (! $this->B || $this->B->powMod(new BigInteger(1), $this->N) === 0) {
            $this->b = $this->createRandomBigIntegerInRange();
            $gPowed = $this->g->powMod($this->b, $this->N);
             $this->B = $this->k->multiply($this->v)
                 ->add($gPowed)
                 ->powMod(new BigInteger(1), $this->N);
        }
        
        $this->Bhex =  $this->stripLeadingZeros($this->B->toHex());
        
        return $this->Bhex;
    }

    /**
     *
     * @param string $Ahex The client ephemerial key 'A'
     * @param string $M1hex The client password proof 'M1'
     * @throws \Exception If the password proof fails
     * @return string The server proof of the shared key 'S' and verifier 'M2'
     */
    public function step2($Ahex, $M1hex)
    {
        $this->Ahex = $this->stripLeadingZeros($Ahex);
        $this->A = new BigInteger($Ahex, 16);
        
        if ($this->A->powMod(new BigInteger(1), $this->N) === 0) {
            throw new \Exception('Client sent invalid key: A mod N == 0.');
        }
        
        $u = new BigInteger($this->hash($this->Ahex . $this->Bhex), 16);
        $avu = $this->A->multiply($this->v->powMod($u, $this->N));
        
        $this->S = $avu->modPow($this->b, $this->N);
        
        $Shex = $this->stripLeadingZeros($this->S->toHex());
        $this->M = $this->hash($this->Ahex . $this->Bhex . $Shex);
        
        if( $M1hex != $this->M) {
            throw new \Exception('Client M1 does not match Server M1.');
        }
        
        $this->HAMK = $this->hash($this->Ahex . $this->M . $Shex);
        
        return $this->HAMK;
    }
  
    /**
     * @return string 'M1' the servers calculation of what the password proof should be
     */
    public function getM()
    {
        return $this->M;
    }

    /**
     * @return string 'M2' the server proof of the shard key 'S' and that it has the verifier 'v'.
     */
    public function getHAMK()
    {
        return $this->HAMK;
    }

    public function hash($x)
    {
        return strtolower(hash($this->H, $x));
    }

    function createRandomBigIntegerInRange() {
        return new BigInteger($this->getSecureRandom(), 16);
    }
    
    public function getSecureRandom($bits = 64)
    {
        /**
         * https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP
         * Our primary choice for a cryptographic strong randomness function is
         * openssl_random_pseudo_bytes.
         */
        $str = '';
        if (function_exists('openssl_random_pseudo_bytes') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || substr(PHP_OS, 0, 3) !== 'WIN')) {
            $str = openssl_random_pseudo_bytes($bits, $strong);
            if ($strong) {
                return $this->binary2hex($str);
            }
        }
        
        /*
         * If mcrypt extension is available then we use it to gather entropy from
         * the operating system's PRNG. This is better than reading /dev/urandom
         * directly since it avoids reading larger blocks of data than needed.
         * Older versions of mcrypt_create_iv may be broken or take too much time
         * to finish so we only use this function with PHP 5.3 and above.
         */
        if (function_exists('mcrypt_create_iv') && (version_compare(PHP_VERSION, '5.3.0') >= 0 || substr(PHP_OS, 0, 3) !== 'WIN')) {
            $str = mcrypt_create_iv($bits, MCRYPT_DEV_URANDOM);
            if ($str !== false) {
                return $this->binary2hex($str);
            }
        }
        
        /*
         * No build-in crypto randomness function found. We collect any entropy
         * available in the PHP core PRNGs along with some filesystem info and memory
         * stats. To make this data cryptographically strong we add data either from
         * /dev/urandom or if its unavailable, we gather entropy by measuring the
         * time needed to compute a number of SHA-1 hashes.
         */
        
        $bitsPerRound = 2; // bits of entropy collected in each clock drift round
        $msecPerRound = 400; // expected running time of each round in microseconds
        $hashLength = 20; // SHA-1 Hash length
        $total = $bits; // total bytes of entropy to collect
        
        $handle = @fopen('/dev/urandom', 'rb');
        if ($handle && function_exists('stream_set_read_buffer')) {
            @stream_set_read_buffer($handle, 0);
        }
        
        do {
            $bytes = ($total > $hashLength) ? $hashLength : $total;
            $total -= $bytes;
            
            // collect any entropy available from the PHP system and filesystem
            $entropy = rand() . uniqid(mt_rand(), true) . $str;
            $entropy .= implode('', @fstat(@fopen(__FILE__, 'r')));
            $entropy .= memory_get_usage();
            if ($handle) {
                $entropy .= @fread($handle, $bytes);
            } else {
                // Measure the time that the operations will take on average
                for ($i = 0; $i < 3; $i ++) {
                    $c1 = microtime(true);
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < 50; $j ++) {
                        $var = sha1($var);
                    }
                    $c2 = microtime(true);
                    $entropy .= $c1 . $c2;
                }
                
                // Based on the above measurement determine the total rounds
                // in order to bound the total running time.
                $rounds = (int) ($msecPerRound * 50 / (int) (($c2 - $c1) * 1000000));
                
                // Take the additional measurements. On average we can expect
                // at least $bits_per_round bits of entropy from each measurement.
                $iter = $bytes * (int) (ceil(8 / $bitsPerRound));
                for ($i = 0; $i < $iter; $i ++) {
                    $c1 = microtime();
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < $rounds; $j ++) {
                        $var = sha1($var);
                    }
                    $c2 = microtime();
                    $entropy .= $c1 . $c2;
                }
            }
            // We assume sha1 is a deterministic extractor for the $entropy variable.
            $str .= sha1($entropy, true);
        } while ($bits > strlen($str));
        
        if ($handle) {
            @fclose($handle);
        }
        
        return $this->binary2hex($str);
    }

    public function binary2hex($string)
    {
        $chars = array(
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            'a',
            'b',
            'c',
            'd',
            'e',
            'f'
        );
        
        $length = strlen($string);
        
        $result = '';
        for ($i = 0; $i < $length; $i ++) {
            $b = ord($string[$i]);
            $result = $result . $chars[($b & 0xF0) >> 4];
            $result = $result . $chars[$b & 0x0F];
        }
        
        return $result;
    }
    
    /**
     * @return BigInteger The client verifier 'v'.
     */
    public function getVerifier()
    {
        return $this->v;
    }

    /**
     * @return BigInteger The client salt 's'.
     */
    public function getSalt()
    {
        return $this->salt;
    }
}