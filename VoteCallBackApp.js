var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

var activeClients = {};

 app.get('/', function(req, res){
    var sessionId = req.query.id2;
    res.send('<h1>Hello ' + sessionId + '</h1>');
    
 });

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