function createHTMLTable(dataArray) {
  if (!Array.isArray(dataArray) || dataArray.length === 0) {
    return "<p>No records found.</p>";
  }

  // Get column names from the first row
  const columns = Object.keys(dataArray[0]);

  // Start building the table
  let html = `
    <table style="border-collapse: collapse; width: 100%; text-align: left;">
      <thead>
        <tr>
          ${columns
            .map(
              (col) =>
                `<th style="border:1px solid #ccc; padding:8px;">${col}</th>`
            )
            .join("")}
        </tr>
      </thead>
      <tbody>
  `;

  // Populate rows
  for (const row of dataArray) {
    html += "<tr>";
    for (const col of columns) {
      html += `<td style="border:1px solid #ccc; padding:8px;">${
        row[col] ?? ""
      }</td>`;
    }
    html += "</tr>";
  }

  html += `
      </tbody>
    </table>
  `;
  return html;
}

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

        if (data.data) {
          // Display SELECT query results in the chat box only
          // let resultMessage = document.createElement("div");
          // resultMessage.className = "chat-message bot";
          // resultMessage.innerText = JSON.stringify(data.data, null, 2);
          // chatBox.appendChild(resultMessage);
          // chatBox.scrollTop = chatBox.scrollHeight;
          // Display SELECT query results as an HTML table
          const resultTable = createHTMLTable(data.data);
          let tableMessage = document.createElement("div");
          tableMessage.className = "chat-message bot";
          tableMessage.innerHTML = resultTable; // Insert the HTML table
          chatBox.appendChild(tableMessage);
          chatBox.scrollTop = chatBox.scrollHeight;
        } else {
          // For INSERT, UPDATE, or DELETE, refresh volunteer directory
          fetchVolunteers();
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });

  document.getElementById("aiCommand").value = "";
}
document
  .getElementById("aiCommand")
  .addEventListener("keypress", function (event) {
    if (event.key === "Enter") {
      event.preventDefault(); // Prevent the default action (e.g., newline)
      sendCommand(); // Trigger sendCommand when Enter is pressed
    }
  });
