/**
 * Create an jquery element from vanilla JS 
 * usage: create("div").addClass("...")
 */
function create(elemName)
{
	return $(document.createElement(elemName));
}