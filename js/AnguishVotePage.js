/**
 * Anguish Vote Page
 */
var AnguishVotePage = new function AnguishVotePage() {
	
	/** current points **/
	this.currentPoints = 0;
	
	/** Are we logged in? **/
	this.isLoggedIn = false;
	
	/** Session Id to be passed **/
	this.sessionId = -1;

	var instance = this;

	AnguishVotePage.getInstance = function () {
		return instance;
	}
	
	/**
	 * Init handling
	 */
	this.init = function (isLoggedIn, sessionId, points) {

		this.currentPoints = points;
		this.isLoggedIn = isLoggedIn;
		this.sessionId = sessionId;

		var $this = this;
		
		/** Load Dom events **/
		this.loadEvents();
		
		this._getVoteHistory();

	}


	this._getVoteHistory = function () {

		var url = API_ENDPOINT + "?action=getVoteHistory&sessionId=" + this.sessionId;

		var $this = this;

		var callback = function (r) {

			var data = JSON.parse(r);
			var trans = _.sortBy(data, "generateDate").reverse(); // sort by boughtdate
			
			console.log(trans);
		};

		$.get(url, callback);

	}
	
	
	
	/**
	 * load events 
	 */
	this.loadEvents = function () {
		var $this = this;
	}

	return AnguishVotePage;
}
