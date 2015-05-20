var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var requestify = require('requestify');
/** MYSQL */
var mysql      = require('mysql');
var connection = initializeConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'rJCa!#7@mgq82hNS',
  database : 'forums'
});


/** Current clients **/
var activeClients = {};

/**
 *  Default route for Callback
 */
 app.get('/', function(req, res){
    var sessionId = req.query.usr;
    getMemeberId(res, sessionId);
 });

/**
 *  Get a valid vote pin from our php API
 */
 function createVotePin(){
 
  requestify.post('http://www.anguishps.com/website/api.php', {
      action: 'createVPin'
  })
  .then(function(response) {
      console.log(response.getBody());
  });
 }
 
/**
 * Get Member Id 
 */
 function getMemeberId(res, sessionId){
   
    var query = connection.query('SELECT m.member_id FROM `sessions` s, `members` m where s.id = ? and s.member_id = m.member_id', [sessionId], function(err, results) {
        if (err) throw err;
        
              
      res.send('<h1>'+JSON.stringify(results)+'</h1>');
      createVotePin();

    });
    
    handleDisconnect(connection);

 }


/** Socket IO */
io.on('connection', function(socket){
  console.log('a user connected');
  
  /**
   *
   *  We just connected and going to vote site 
   * add us to array of sockets so we can message back
   * 
   * **/
  socket.on('addMe', function (data) {
 
    var sessionId = data.sessionId;
    
    socket.set('sessionId', sessionId, function() {
      activeClients[sessionId] = socket;
    });
  

  });
   
  // disconnect from us 
  socket.on('disconnect', function(){
    socket.get('sessionId', function(err, sessionId) {
        delete activeClients[sessionId];
      });
  });
  
});


http.listen(3000, function(){
  console.log('listening on *:3000');
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