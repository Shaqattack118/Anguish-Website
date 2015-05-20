/**
 * Anguish Vote Page
 */
var AnguishVotePage = new function AnguishVotePage() {
	
	/** current points **/
	this.currentPoints = 0;
	
	/** Are we logged in? **/
	this.isLoggedIn = false;
	
	/** Session Id to be passed **/
	this.sessionId = -2;

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
		this.socket =  io.connect('http://www.anguishps.com:3000');
		var $this = this;
		
		/** Load Dom events **/
		this.loadEvents();
		
		this._getVoteHistory();

	}

	this._renderVoteAuths = function(data){
							
		var row =  create("tr");							
							//<tr>
								//<th class ="authcode" align="center"><strong>Auth Code</strong></td>
								//<th class ="blank" align="center"><strong></strong></td>
								//<th class ="status" align="center"><strong>Status</strong></td>
								//<th class ="date" align="center"><strong>Date</strong></td>
						    //</tr>
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
		
		$this.socket.emit('addMe', { 'sessionId': sessionId} );
		$this.socket.on('alert', function(data){
			var pin = data.pin;
			
			alert("You have received a vote pin!");
  		});
		  
		  $('.rl').click(function(e) { $this._handleVoteClick('rl'); });
	}

	this._handleVoteClick = function(site){
		var $this = this;
		
		switch(site){
		 case 'rl':
			var link = 'http://www.runelocus.com/toplist/index.php?action=vote&id=41625&id2='+$this.sessionId
			window.open(link, '_blank');
			break;
		}
	}
	
	return AnguishVotePage;
}
