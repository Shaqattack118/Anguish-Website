/**
 * Create an jquery element from vanilla JS 
 * usage: create("div").addClass("...")
 */
function create(elemName)
{
	return $(document.createElement(elemName));
}


Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};


function showNotification(heading, message){
	
	$(".modalNotice").find("#heading").html(heading);
	$(".modalNotice").find("#message").html(message);
	
	$(".modalNotice").find(".close-btn").click(function(e) {  
		$("#modalNoticeAlert").removeClass("show");
		$("#modalNoticeAlert").addClass("hideSection");
	 });

	$("#modalNoticeAlert").removeClass("hideSection").addClass("show");
}


var API_ENDPOINT = "/website/api.php"; // i hate php