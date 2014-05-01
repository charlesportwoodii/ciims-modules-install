var Install = {
	migrate: function() {
		var width = 1;
	    progressInterval = setInterval(function() {
	        width+=.5;
	        $(".progress-bar").css("width", width + "%");
	        if (width >= 99)
	            clearInterval(progressInterval);
	    }, 100);


	    $.post("runmigrations", function(data) {
	        // Set the bar to 100%
	        width = 100;
	        $(".progress-bar").css("width", "100%");
	        clearInterval(progressInterval);
	        $("#inprogress").hide();
	        if (data.migrated)
	        {
	            $(".progress-bar").removeClass("progress-bar-warning").addClass("progress-bar-success");
	            $("#done").show();
	            $("#continue-button").removeClass("pure-button-disabled")
	        }
	        else
	        {
	            $(".progress-bar").removeClass("progress-bar-warning").addClass("progress-danger");
	            $("#error").show();
	        }
	    }).error(function() {
	        $("#inprogress").hide();
	        width = 100;
	        $(".progress-bar").css("width", "100%");
	        clearInterval(progressInterval);
	        $(".progress-bar").removeClass("progress-bar-warning").addClass("progress-bar-danger");
	        $("#error").show();
	    });
	}
};