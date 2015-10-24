<?php

/**
 * NOTE THIS FILE DOES NOT RUN ON A SERVER
 * It is used to test the code in an IDE before releasing.
 */


require_once 'PHPUnit/Framework/TestCase.php';

require_once 'thinbus/thinbus-srp.php';

require_once 'thinbus/BigInteger.php';



/**
 * This subclass lets use override the random 'b' value and constant 'k' value with those seen in a debugger running the js+java thinbus tests.
 */
class NotRandomSrp extends ThinbusSrp
{

    protected $notRandomNumber;

    function setNotRandom($nr)
    {
        $this->notRandomNumber = new BigInteger($nr, 16);
    }

    function createRandomBigIntegerInRange()
    {
        return $this->notRandomNumber;
    }
}

/**
 * Tests authentication using values from thinbus js+java tests.
 */
class ThibusTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var Srp
     */
    private $Srp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $N_base10str = "19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107";
        $g_base10str = "2";
        $k_base16str = "1a3d1769e1d6337af78796f1802f9b14fbc20278fb6e15e4361beb38a8e7cd3a";
        
        $this->Srp = new NotRandomSrp($N_base10str, $g_base10str, $k_base16str, "sha256");
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Srp = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    public function testWithJavaValues() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        $B = $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $M2 = $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
        $this->assertEquals("d14d1a028b06ab00a14e1dd5518684d4d2811e452350b5f2d154efbf9e250755",$M2);
        $this->assertEquals("92d39597b7db73054a4b98fc3b7bda4aafa8ccda8b1b310178e6e62eda022c6f", $this->Srp->getSessionKey());
        $this->assertEquals("tom@arcot.com", $this->Srp->getUserID());
    }
    
    /**
     * @expectedException Exception
     */
    public function testOnlyGivesOneB() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
    }
    
    /**
     * @expectedException Exception
     */
    public function testOnlyValidatesOneM1() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
        $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
    }
    
    public function testWithJavaValuesThinbus13() {
        // new BigInteger("6f2f0345b7b927babd4342f7f28ba30fb8e739163c5e997cade873bc2ae16b57e582a379642f91e610dbb156132ee50540630ea8576c94a6d8d7813b39b5607637a98383a90bae88146e95fd09b559d447f41c65e4117d44c740b129c424e6afda46356417a78c051695843f2dc533c2d188f3b5d4ebee", 16)
        $this->Srp->setNotRandom("6f2f0345b7b927babd4342f7f28ba30fb8e739163c5e997cade873bc2ae16b57e582a379642f91e610dbb156132ee50540630ea8576c94a6d8d7813b39b5607637a98383a90bae88146e95fd09b559d447f41c65e4117d44c740b129c424e6afda46356417a78c051695843f2dc533c2d188f3b5d4ebee");
        // s c7ce7e7f7cd06cd296570c487886e4b18847a0a96b9d1571bb351c7cb3fd10c8
        // v 1c4adb908deffde2ccc738b2a9b773b61e2f6640df8459d8009a2f25e8c47ec73956552f3de9d912810955555afebd2e426b43df7d1ddd12f265a0f177fa03108e8939c4e0be6de5af18ebb486ea11f41da4ced644e0dc1d4a27c0aeb744b8205f509528a5edbeb17336670f8b76749868f6c4452c3ce1
        $B = $this->Srp->step1("tom@arcot.com", "c7ce7e7f7cd06cd296570c487886e4b18847a0a96b9d1571bb351c7cb3fd10c8", "1c4adb908deffde2ccc738b2a9b773b61e2f6640df8459d8009a2f25e8c47ec73956552f3de9d912810955555afebd2e426b43df7d1ddd12f265a0f177fa03108e8939c4e0be6de5af18ebb486ea11f41da4ced644e0dc1d4a27c0aeb744b8205f509528a5edbeb17336670f8b76749868f6c4452c3ce1");
        // B 7b9ce200f95227d16a03b43c73780c2adb469ff0b6b123e52507002c25ca32f2e097c6c66d4dd0d47c28eec7476e6945329fedfaf0d5a2411e334e69dbd6088e1fa8f92455e1786313547b266482d16a5755951fc396a0d6795e4ce80915dd3f06449e479726b8bde6ebd7f4d504175e1d616dfe22a16b
        // A 5fd6d73d866c4543250c9c04ab6964de0db8f3bad831dfa0e7edaf2f9862a60ea76313bf47ac475789a65f4459a9da4c2739957762084b9d5a2a7c76a33e1ef75ea6662c21d976fa9272b2b3019d7c14af5845de42000209f27127b3e29332c4eb944197c1ebcb4cbc1b543f97ed1e1b966c6a19d55f9f
        // M1 3f99d6b724102cacd2feb3f5ac5c54283bc954265d1d7919a8d59fd198019cb3
        $M2 = $this->Srp->step2("5fd6d73d866c4543250c9c04ab6964de0db8f3bad831dfa0e7edaf2f9862a60ea76313bf47ac475789a65f4459a9da4c2739957762084b9d5a2a7c76a33e1ef75ea6662c21d976fa9272b2b3019d7c14af5845de42000209f27127b3e29332c4eb944197c1ebcb4cbc1b543f97ed1e1b966c6a19d55f9f", "3f99d6b724102cacd2feb3f5ac5c54283bc954265d1d7919a8d59fd198019cb3");
        $this->assertEquals("4e9852f22ffe107c463b4037d3527992ee8d9b78318257ac3d2bbbb03c143946", $M2);
    }
    
    public function testSha1Vectors()
    {
        $projectDir = getenv('ZEND_PHPUNIT_PROJECT_LOCATION');
        
        // parse your data file however you want
        $data = array();
        foreach (file($projectDir . '/test-vectors-sha1.txt') as $line) {
            $data[] = trim($line);
        }

        $username = $data[0];
        $password = $data[1];
        $g_base10 = $data[2];
        $N_base10 = $data[3];
        $k_base16 = $data[4];
        
        for ($i = 1; $i < 100; $i ++) {
            
            $s = $data[7*$i+0+5];
            $v = $data[7*$i+1+5];
            $b = $data[7*$i+2+5];
            $B = $data[7*$i+3+5];
            $A = $data[7*$i+4+5];
            $M = $data[7*$i+5+5];
            $M2 = $data[7*$i+6+5];
            
            $this->Srp = new NotRandomSrp($N_base10, $g_base10, $k_base16, "sha1");
            $this->Srp->setNotRandom($b);
            $Bs = $this->Srp->step1($username, $s, $v);
            $this->assertEquals($B, $Bs); // sanity check that the injected not random took hold
            $M2s = $this->Srp->step2($A, $M);
            $this->assertEquals($M2, $M2s);
            
        }
    }
}

