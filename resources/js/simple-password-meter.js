
// thanks to tm_lv http://stackoverflow.com/a/11268104/329496

// note that there is an extra check in this version that the password is at least 8 char long. 

function scorePassword(pass) {
    var score = 0;
    if (!pass)
        return score;

    // award every unique letter until 5 repetitions
    var letters = new Object();
    for (var i=0; i<pass.length; i++) {
        letters[pass[i]] = (letters[pass[i]] || 0) + 1;
        score += 5.0 / letters[pass[i]];
    }

    // bonus points for mixing it up
    var variations = {
        digits: /\d/.test(pass),
        lower: /[a-z]/.test(pass),
        upper: /[A-Z]/.test(pass),
        nonWords: /\W/.test(pass),
    }

    variationCount = 0;
    for (var check in variations) {
        variationCount += (variations[check] == true) ? 1 : 0;
    }
    score += (variationCount - 1) * 10; 

    return parseInt(score);
}

function checkPassStrength(pass) {
	
	if( pass.length < 8 ) {
		return "poor";
	}
	
    var score = scorePassword(pass);
    
    if (score > 80)
        return "strong";
    if (score > 60)
        return "good";
    if (score >= 30)
        return "weak";

    return "poor";
}

$(document).ready(function() {
    $("#password").on("keypress keyup keydown", function() {
        var pass = $(this).val();
        $("#strength_human").text(checkPassStrength(pass));
        $("#strength_score").text(scorePassword(pass));
    });
});
