var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var needle = require('needle');
/** MYSQL */
var mysql      = require('mysql');
var connection = initializeConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'rJCa!#7@mgq82hNS',
  database : 'forums'
});


connection.config.queryFormat = function (query, values) {
  if (!values) return query;
  return query.replace(/\:(\w+)/g, function (txt, key) {
    if (values.hasOwnProperty(key)) {
      return this.escape(values[key]);
    }
    return txt;
  }.bind(this));
};



/** Current clients **/
var activeClients = {};

/**
 * Process callbacks from Voting Sites
 */
 app.get('/process.php', function(req, res){
 		console.log(JSON.stringify(req.query));
    var sessionId = req.query.usr;
   	createVotePin(res, sessionId);
 });
 
 
 /**
  * We need to append www. to all these requests to go through apache
  */
 app.get('*', function(req, res){
  res.redirect('http://www.anguishps.com'+req.originalUrl);
});

/**
 *  Get a valid vote pin from our php API
 */
 function createVotePin(res, sessionId){
    var params =  {
		      	     		'action' : 'createVPin'
	  							};
	/**
	 * Honestly, we should use node js to pull this but since we have php code...
	 */
   needle.post('http://www.anguishps.com/website/api.php', params,  function(err, resp, body){       
         
         res.send('Thanks for voting!'); // just a blank response
         
         /** Is this an active person on the page?? **/
         if(activeClients[sessionId])
            activeClients[sessionId].emit('alert', body);       
            
   });

 }
 
 function getMyData(memberId, callback){
 
    connection.query('SELECT dp.pin, site, hasRedeemed, generateDate FROM forums.vote_history vh, testDB.donation_pins dp WHERE vh.pin = dp.pin and vh.memberId = :memberId ', { "memberId" : memberId }, function(err, results) {

      if (err) throw err;    
       callback(results);
    });
    
 }
 
 function savePinToUser(pin, site, memberId){
    connection.query("INSERT INTO forums.vote_history(`memberId`, `site`, `pin`) VALUES (:memberId, :site, :pin)", { "memberId" : memberId,  "site" : site, "pin": pin} );
 }
 
 /**
  * Insert Pin
  */
  function insertPin(pin){
     connection.query("INSERT INTO testDB.donation_pins(`pin`, `hasRedeemed`, `generateDate`, `type`)  VALUES (:pin,0,sysdate(3), 3)", { "pin": pin });
  }
 
/**
 * Get Member Id so we insert for them
 */
 function getMemeberId(sessionId, callback){
   
    connection.query('SELECT m.member_id FROM `sessions` s, `members` m where s.id = :sessionId and s.member_id = m.member_id', { "sessionId" : sessionId}, function(err, results) {
     
      if (err) throw err;    
       callback(results);
    });
    
    handleDisconnect(connection);

 }


/** Socket IO */
io.on('connection', function(socket){

  /**
   *  We just connected and going to vote site 
   * add us to array of sockets so we can message back
   * 
   * **/
  socket.on('addMe', function (data) {
 
    var sessionId = data.sessionId;
    
    console.log("Connected ["+sessionId+"]");
    
    socket.sessionId =  sessionId;
    activeClients[sessionId] = socket;
  
  });
  
  
   socket.on('getMyData', function (data) {
 
     var sessionId = data.sessionId;
     
     console.log("Getting session for " + sessionId);
      
      getMemeberId(sessionId, function(results){
        
        /** User was logged in, lets get out data  */
        if(results.length != 0){
      
          var memberId = results[0]["member_id"];
          
          getMyData(memberId, function(data){
              /** Return voting data to Client  */
              activeClients[sessionId].emit('myDataReturn', JSON.stringify(data)); 
          });
      
        } 

     });
     
  });
   
  // disconnect from us 
  socket.on('disconnect', function(){
    var sessionId = socket.sessionId;
    console.log("Disconnecting ["+sessionId+"]");
    delete activeClients[sessionId];
  });
  
});


http.listen(3000, function(){
  console.log('Vote Server listening on port 3000');
});




/**
 * Handle Connection for MYSQL node
 */
function initializeConnection(config) {
    function addDisconnectHandler(connection) {
        connection.on("error", function (error) {
            if (error instanceof Error) {
                if (error.code === "PROTOCOL_CONNECTION_LOST") {
                    console.error(error.stack);
                    console.log("Lost connection. Reconnecting...");

                    initializeConnection(connection.config);
                } else if (error.fatal) {
                    throw error;
                }
            }
        });
    }

    var connection = mysql.createConnection(config);

    // Add handlers.
    addDisconnectHandler(connection);

    connection.connect();
    return connection;
}

/**
 * Handle disconnect for MYSQL node
 */
function handleDisconnect(connection) {
  connection.on('error', function(err) {
    if (!err.fatal) {
      return;
    }

    if (err.code !== 'PROTOCOL_CONNECTION_LOST') {
      throw err;
    }

    console.log('Re-connecting lost connection: ' + err.stack);

    connection = mysql.createConnection(connection.config);
    handleDisconnect(connection);
    connection.connect();
  });
}