function sendCommand() {
  let command = document.getElementById("aiCommand").value;
  if (command.trim() === "") {
    alert("Please enter a command.");
    return;
  }

  let chatBox = document.getElementById("chatBox");
  let userMessage = document.createElement("div");
  userMessage.className = "chat-message user";
  userMessage.innerText = command;
  chatBox.appendChild(userMessage);
  chatBox.scrollTop = chatBox.scrollHeight;

  fetch("ai_process.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ prompt: command }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        let aiMessage = document.createElement("div");
        aiMessage.className = "chat-message bot";
        aiMessage.innerText = data.sqlQuery;
        chatBox.appendChild(aiMessage);
        chatBox.scrollTop = chatBox.scrollHeight;

        // If the query fetched results, display them
        if (data.data) {
          let resultMessage = document.createElement("div");
          resultMessage.className = "chat-message bot";
          resultMessage.innerText = JSON.stringify(data.data, null, 2);
          chatBox.appendChild(resultMessage);
          chatBox.scrollTop = chatBox.scrollHeight;
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });

  document.getElementById("aiCommand").value = "";
}
