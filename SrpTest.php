<?php
require_once 'Srp.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'BigInteger.php';

/**
 * This subclass lets use override the random 'b' value and constant 'k' value with those seen in a debugger running the js+java thinbus tests.
 */
class NotRandomSrp extends Srp {
    
    protected $notRandomNumber;
    
    function setNotRandom($nr){
        $this->notRandomNumber = new BigInteger($nr, 16);
    }
    
    function createRandomBigIntegerInRange() {
        return $this->notRandomNumber;
    }
}

/**
 * Srp test case.
 */
class SrpTest extends PHPUnit_Framework_TestCase {
	
	/**
	 *
	 * @var Srp
	 */
	private $Srp;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		$N_base10str = "19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107";
		$g_base10str = "2";
		
		$this->Srp = new NotRandomSrp($N_base10str, $g_base10str);
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated SrpTest::tearDown()
		$this->Srp = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}
	
	/**
	 * Tests the step1 using values taken from thinbus js+java tests. Note we overload the 'k' and 'b' values to values the java library gives when debugging.
	 */
	public function testStep1() {
	   $this->Srp->setNotRandom("8709a550d913081df92bb23ae650a488ad7df1aad73d22b0d1e6175b8b5355d8c7beecdb13317abd9c9972e7bcc98acfbae6ea86c2109d311de3f62a0bf5674bf2eea38efa4f5ee9bbe7856246ac282fb1322316b641f09cbf2b424d82945283e899b7d5a92623065d9249ba2f5000a392c89f26189d6");
	   $this->Srp->setK("8d7c38a15a345fc1285b7b5a9e704e0587329ed8");
	   $B = $this->Srp->step1("tom@arcot.com", "c23b1b4e36512f02c1216119a215672453593ef9332e46d397ebb4d26a8bba14", "431d44df77b1bfa081cb2b806c772bdbc2e7dc9438c08b64a1287c512fa32b35b4e52e799037612fe1e8d5600c2fbf38db4b5f7ad216799a858dfed09aedfdac04208db72d352b1fd24f36286c75cb6a7fe4c5e97b2528cfeb4685fb97f41ceb76aa76a275a1f993e00d18a7a7d0bbbbfd8fb13da861b3");
	   $this->assertEquals("7085129c6e644f58e6adda217aaffb103c809d5d30c76d2767e5dc5b6fb63f43caee49d44e7e10ad0c11051a59f00af5aa903792a395d75ed804d0c16f4d081cd7c2a9f356db04bdfa06a0681d8132d3ba3a18b4481dcc3eddc97beb20e8a3291539902bcf43f587978d525dd6468cfd9a61747d14786", $B);
	}

	/**
	 * Tests the step2 using values taken from thinbus js+java tests. Note we overload the 'b' values to values the java library gives when debugging.
	 */
	public function testStep2() {
	    $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
	    $B = $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $M2 = $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
        $this->assertEquals("d14d1a028b06ab00a14e1dd5518684d4d2811e452350b5f2d154efbf9e250755",$M2);
	}

}

