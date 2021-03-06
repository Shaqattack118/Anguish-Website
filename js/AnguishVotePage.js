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
		if(!this.isLoggedIn)
			sessionId = guid();
			
		this.sessionId = sessionId;
		this.socket =  io.connect('http://www.anguishps.com:3000');
		var $this = this;
		
		/** Load Dom events **/
		this.loadEvents();

	}

    /**
	 * Render vote auth
	 */
	this._renderVoteAuths = function(data){
		
		$(".votingTable.auths").find(".noAuthRow").remove();
		
		_.each(data, function(obj){
			
			var pin = obj.pin;
			var hasRedeemed = obj.hasRedeemed;
			var mysqlDate = obj.generateDate;
			var date = (new Date ((new Date((new Date(new Date(mysqlDate))).toISOString() )).getTime() - ((new Date(mysqlDate)).getTimezoneOffset()*60000))).toISOString().slice(0, 19).replace('T', ' ');
			var row =  create("tr");	
		
			row.append(create("td").attr("colspan", "2").addClass("authcode").append(pin));		
									
			row.append(create("td").addClass("status").append((hasRedeemed == 0 ? 'Not Redeemed' : "Redeemed")));
				
			row.append(create("td").addClass("date").append(date));
				
			$(".votingTable.auths").append(row);
		});

		
	}



	/**
	 * load events 
	 */
	this.loadEvents = function () {
		var $this = this;
		
		$this.socket.emit('addMe', { 'sessionId': sessionId } );
		$this.socket.emit('getMyData', { 'sessionId': sessionId } );
		
		$this.socket.on('myDataReturn',  function(dataIn){
			$this._renderVoteAuths(JSON.parse(dataIn));
		});
		
		$this.socket.on('alert', function(dataIn){
			
			var data = JSON.parse(dataIn);
			var pin = data.pin;

			alert("Thank you for voting! Please \"Okay\" to continue");
			showNotification("Success", "Your Vote Auth is <b> " + pin + "</b><br><br>If you are logged in on a forum account, you will see the vote pin under \"Your Voting Auth Codes\" <br><br>Happy Gaming!");
			
			$this._renderVoteAuths(data);
				
  		});
		  
		  $('.rl').click(function(e) { $this._handleVoteClick('rl'); });
	}

	/**
	 * Handle Vote Click
	 */
	this._handleVoteClick = function(site){
		var $this = this;
		var link = "";
		switch(site){
			case 'rl': // rune locus
				 link = 'http://www.runelocus.com/toplist/index.php?action=vote&id=41625&id2='+$this.sessionId
			break;
		}
		
		window.open(link, '_blank');
	}
	
	return AnguishVotePage;
}
