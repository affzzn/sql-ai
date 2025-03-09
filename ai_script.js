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

        // If the query fetched results, display them dynamically
        if (data.data) {
          updateVolunteerList(data.data); // Dynamically update the volunteer list
        } else {
          // If it's an INSERT/UPDATE/DELETE query, re-fetch volunteers
          fetchVolunteers();
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });

  document.getElementById("aiCommand").value = "";
}
