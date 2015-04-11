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