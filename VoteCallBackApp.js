var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

/** MYSQL */
var mysql      = require('mysql');
var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'rJCa!#7@mgq82hNS'
});

var activeClients = {};

 app.get('/', function(req, res){
    var sessionId = req.query.usr;
    getMemeberId(res, sessionId);
 });



 function getMemeberId(res, sessionId){
   
   connection.connect();
   
    var query = connection.query('SELECT m.member_id FROM `sessions` s, `members` m where s.id = ? and s.member_id = m.member_id', [sessionId], function(err, results) {
        if (err) throw err;
        
              
      res.send('<h1>'+JSON.stringify(results)+'</h1>');

    });
    
    connection.end();

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