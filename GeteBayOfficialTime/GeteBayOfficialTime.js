
/* jQuery */

	$(function() {

		$("#GeteBayOfficialTime_btn").click(function() {

  			$.get("handle_GeteBayOfficialTime.php", function(data) {
  				
  				// clear any current data/HTML from the page
  				$("#GeteBayOfficialTime_result").empty();

  				// append the result to the page
  				$("#GeteBayOfficialTime_result").append("The official eBay time is:<br>"+data);
  			});

		});

	});

/* END jQuery */