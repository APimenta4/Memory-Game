const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
    credentials: true,
  },
});

const PORT = process.env.APP_PORT || 8086;

httpServer.listen(PORT, () => {
  console.log(`listening on localhost:${PORT}`);
});

io.on("connection", (socket) => {
  console.log(`client ${socket.id} has connected`);

  socket.on("echo", (message) => {
    socket.emit("echo", message);
  });

  
  // ------------------------------------------------------
  // Notification Alert
  // ------------------------------------------------------
  socket.on("new_device", (userId) => {
  console.log(`New Device of User ${userId} connected with socket ID ${socket.id}`);
  socket.join(`user_${userId}`);
  });
  
  socket.on("notification_alert", (userId) => {
  console.log(`Notification Alert of User ${userId} connected with socket ID ${socket.id}`);
  const userRoom = `user_${userId}`;
  setTimeout(
      ()=>{
          io.to(userRoom).emit("notification");
          console.log(`Notification sent`);
      },
      1000
  )
  });
});

