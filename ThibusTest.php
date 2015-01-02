<?php
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Srp.php';

require_once 'BigInteger.php';

/**
 * This subclass lets use override the random 'b' value and constant 'k' value with those seen in a debugger running the js+java thinbus tests.
 */
class NotRandomSrp extends Srp
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
 * Thibus test case.
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
    
   
    /**
     * Tests authentication using values from thinbus js+java tests.
     */
    public function testMutualAuthentication() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        $B = $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $M2 = $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
        $this->assertEquals("d14d1a028b06ab00a14e1dd5518684d4d2811e452350b5f2d154efbf9e250755",$M2);
    }
}

