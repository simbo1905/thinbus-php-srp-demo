<?php

require_once 'srand.php';
require_once 'BigInteger.php';

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
/**
 * See http://simon_massey.bitbucket.org/thinbus/login.png
 * Please read the readme at https://bitbucket.org/simon_massey/thinbus-srp-js
 *
 * This is the server authentication session object. It issues a challenge B at
 * step1 then validates a password proof based on that challenge in step2. The
 * server needs to hold this object between the client being given the challenge
 * and the client sending the password proof based on that challenge. This can
 * be done by either serializing this object into the server http session or
 * by putting it into the database. To prevent against online dicionary attacks this
 * object refuses to either steps more than once. That forces an attacker to
 * get a new challenge from the server for every password guess to slow them down
 * without causing any significant overhead to real users logging in.
 */
class ThinbusSrp
{
    /**
     * To protect against dictionary attacks we refuse to generate new challenges
     * or validate additional guesses of the password. This variable tracks
     * whether we have issued a challenge or checked a password proof and only
     * allows either of those to happen once.
     */
    protected $step = 0;

    /**
     * @var \BigInteger N
     */
    protected $N;

    /**
     * @var \BigInteger g
     */
    protected $g;

    /**
     * We require the 'k' to be configured as binary->BigInteger is not platform portable.
     * @var \BigInteger k
     */
    protected $k;

    /**
     * @var \BigInteger The password verifier 'v'.
     */
    protected $v;

    /**
     * @var string The user identity 'I'.
     */
    protected $userID;

    /**
     * @var string A hex encoded secure randome number.
     */
    protected $b = null;

    /**
     * @var \BigInteger|null The server one time ephemeral key derived from 'b'
     */
    protected $B = null;

    /**
     * @var srring A string version of B // TODO is this redundant?
     */
    protected $Bhex;
    
    /**
     * A shared strong session key K=H(S)
     */
    protected $K;
    
    /**
     * name of the hashing algorith e.g. "sha256"
     * @var string
     */
    protected $H;

    protected function stripLeadingZeros($str) {
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
     * @param unknown $salt_base16str The user salt 's'. Actually unused although the http://srp.stanford.edu/design.html suggests using it in the M calculation as we use SHA256 we don't.
     * @param unknown $v_base16str The user verifier 'v'
     * @return string The server challenge 'B'
     */
    public function step1($userID, $salt_base16str, $v_base16str)
    {
        if($this->step != 0 ) throw new \Exception("Possible dictionary attack refusing to collaborate");
        $this->v = new BigInteger($v_base16str, 16);
        $this->userID = $userID;
        
        while (! $this->B || $this->B->powMod(new BigInteger(1), $this->N) === 0) {
            $this->b = $this->createRandomBigIntegerInRange();
            $gPowed = $this->g->powMod($this->b, $this->N);
             $this->B = $this->k->multiply($this->v)
                 ->add($gPowed)
                 ->powMod(new BigInteger(1), $this->N);
        }
        
        $this->Bhex = $this->stripLeadingZeros($this->B->toHex());
        
        $this->step = 1;
        
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
        if($this->step != 1 ) throw new \Exception("Possible dictionary attack refusing to collaborate.");
        $Ahex = $this->stripLeadingZeros($Ahex);
        $A = new BigInteger($Ahex, 16);
        
        if ($A->powMod(new BigInteger(1), $this->N) === 0) {
            throw new \Exception('Client sent invalid key: A mod N == 0.');
        }
        
        $u = new BigInteger($this->hash($Ahex . $this->Bhex), 16);
        $avu = $A->multiply($this->v->powMod($u, $this->N));
        
        $S = $avu->modPow($this->b, $this->N);
        $Shex = $this->stripLeadingZeros($S->toHex());
        
        $this->K = $this->hash($Shex);
        
        $M = $this->stripLeadingZeros($this->hash($Ahex . $this->Bhex . $Shex));
        
        if( $M1hex != $M) {
            throw new \Exception('Client M1 does not match Server M1.');
        }
        
        $M2 = $this->hash($Ahex . $M . $Shex);
        
        $this->step = 2;
        
        $this->v = null;
        $this->N = null;
        $this->g = null;
        $this->k = null;
        $this->b = null;
        $this->B = null;
        $this->H = null;
        
        return $this->stripLeadingZeros($M2);
    }

    /**
     * @return string The user id 'I'.
     */
    public function getUserID()
    {
        return $this->userID;
    }
    
    /**
     * @return string 'K=H(S)' a strong shared session key.
     */
    public function getSessionKey()
    {
        return $this->K;
    }

    protected function hash($x)
    {
        return strtolower(hash($this->H, $x));
    }

    protected function createRandomBigIntegerInRange() {
        return new BigInteger($this->getSecureRandom(), 16);
    }
    
    protected function getSecureRandom($bits = 64)
    {
        $str = secure_random_bytes($bits);
        return $this->binary2hex($str);
    }

    protected function binary2hex($string)
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

}