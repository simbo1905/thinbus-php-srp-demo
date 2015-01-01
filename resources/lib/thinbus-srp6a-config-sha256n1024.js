/**
This is the recommended class as it uses the strong hash which 
comes with JDK8 by default. It also uses a 1024 bit prime but 
you can generate and configure a larger one. You can also 
do a custom JCA install to your JDK/JRE to get an use a 
stronger hashing algorithm. 

Here we subclass and add the H, N and g for 1024 with SHA256. 
On the server use the matching java class: 

	com.nimbusds.srp6.js.SRP6JavascriptServerSessionSHA256 
	
Running that class as a main outputs the constants. Note that 'k' 
is the output of the servers hashing approach hex string. 
*/

function SRP6JavascriptClientSessionSHA256(){ 

}

SRP6JavascriptClientSessionSHA256.prototype = new SRP6JavascriptClientSession();

SRP6JavascriptClientSessionSHA256.prototype.N = function() {
	return new BigInteger("19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107", 10);
}

SRP6JavascriptClientSessionSHA256.prototype.g = function() {
	return new BigInteger("2", 10);
}

SRP6JavascriptClientSessionSHA256.prototype.H = function (x) {
		return CryptoJS.SHA256(x).toString().toLowerCase();
}


SRP6JavascriptClientSessionSHA256.prototype.k = new BigInteger("1a3d1769e1d6337af78796f1802f9b14fbc20278fb6e15e4361beb38a8e7cd3a", 16);
