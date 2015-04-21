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

var BMT_PRODUCTS = [{
					'picture'  : 'http://www.nearreality.com/addons/static/49.png',
					'name' : '10 Premium Points',
					'url' : 'https://secure.bmtmicro.com/cart?CID=9139&CLR=0&PRODUCTID=91390006'
				   },{
					'picture'  : 'http://www.nearreality.com/addons/static/49.png',
					'name' : '25 Premium Points',
					'url' : 'https://secure.bmtmicro.com/cart?CID=9139&CLR=0&PRODUCTID=91390005'
				   },{
					'picture'  : 'http://www.nearreality.com/addons/static/49.png',
					'name' : '50 Premium Points',
					'url' : 'https://secure.bmtmicro.com/cart?CID=9139&CLR=0&PRODUCTID=91390007'
				   }];



function groupIndex(behavior) {
    return function(obj, iteratee, context) {
      var result = {};
      iteratee = iterateeIndex(iteratee, context);
      _.each(obj, function(value, index) {
        var key = iteratee(value, index, obj);
        behavior(result, value, key);
      });
      return result;
    };
  };
  
function iterateeIndex(value, context, argCount) {
    if (value == null) return _.identity;
    if (_.isFunction(value)) return createCallback(value, context, argCount);
    if (_.isObject(value)) return _.matches(value);
    return _.property(value);
 };
 
 
_.indexByArray = groupIndex(function(result, value, key) {
    	if(!result[key]){
    		result[key] = [];
    	}
    	result[key].push(value);
});

function sortByKey(map) {
    var keys = _.sortBy(_.keys(map), function(a) { return a; });
    var newmap = {};
    _.each(keys, function(k) {
        newmap[k] = map[k];
    });
    return newmap;
}



function createModal(id, classes, header, body, footer){
	var modal = create("div").addClass("modal").attr("id", id).appendTo($("body"));
	
	if(classes)
		modal.addClass(classes);
		
		
	var modalDialog = create("div").addClass("modal-dialog").appendTo(modal);
		
	var _header = create("div").addClass("modal-header").appendTo(modalDialog);
	var _body = create("div").addClass("modal-body").appendTo(modalDialog);
	var _footer = create("div").addClass("modal-footer").appendTo(modalDialog);

	if(header)
		_header.append(header);
	
	if(body)
		_body.append(body);
	
	if(footer)
		_footer.append(footer);
		
		modal.addClass("show");

}


/*!
	devtools-detect
	Detect if DevTools is open
	https://github.com/sindresorhus/devtools-detect
	by Sindre Sorhus
	MIT License
*/
(function () {
	'use strict';
	var devtools = {open: false};
	var threshold = 160;
	var emitEvent = function (state) {
		window.dispatchEvent(new CustomEvent('devtoolschange', {
			detail: {
				open: state
			}
		}));
	};

	setInterval(function () {
		if ((window.Firebug && window.Firebug.chrome && window.Firebug.chrome.isInitialized) || window.outerWidth - window.innerWidth > threshold ||
			window.outerHeight - window.innerHeight > threshold) {
			if (!devtools.open) {
				emitEvent(true);
			}
			devtools.open = true;
		} else {
			if (devtools.open) {
				emitEvent(false);
			}
			devtools.open = false;
		}
	}, 500);

	if (typeof module !== 'undefined' && module.exports) {
		module.exports = devtools;
	} else {
		window.devtools = devtools;
	}
})();




    // get notified when it's opened/closed
 window.addEventListener('devtoolschange', function (e) {
 				if(e.detail.open){
 					var msg = '';
 				
 					msg += "   ___                    _     _      ______  _____   \n";
					msg += "  / _ \                  (_)   | |     | ___ \/  ___|  \n";
				  msg += " / /_\ \_ __   __ _ _   _ _ ___| |__   | |_/ /\ `--.   \n";
					msg += " |  _  | '_ \ / _` | | | | / __| '_ \  |  __/  `--. \  \n";
					msg += " | | | | | | | (_| | |_| | \__ \ | | | | |    /\__/ /  \n";
					msg += " \_| |_/_| |_|\__, |\__,_|_|___/_| |_| \_|    \____/   \n";
					msg += "              __/ |                                    \n";
					msg += "             |___/                                     \n";
					msg += "=======================================================\n";
					msg += "     Why are you looking at this console :-)           \n";
					msg += "=======================================================\n";
 					console.log(msg);
 				}
 });