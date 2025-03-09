// Function to create the HTML table from data
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

// Function to generate the downloadable report
function generateReport(data) {
    const tableHtml = createHTMLTable(data);

    // Create a new report HTML page
    const reportHtml = `
        <html>
        <head>
            <title>Generated Report</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                    font-size: 16px;
                    text-align: left;
                }
                th, td {
                    padding: 12px;
                    border: 1px solid #ddd;
                }
                th {
                    background-color: #f2f2f2;
                }
            </style>
        </head>
        <body>
            <h1>Report Generated from Query</h1>
            <div>
                ${tableHtml}
            </div>
        </body>
        </html>
    `;

    // Create a Blob object with the HTML content
    const blob = new Blob([reportHtml], { type: 'text/html' });

    // Create a download link for the report
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'report.html'; // Set the filename for the report
    link.click(); // Trigger the download
}

// Function to handle sending the command to PHP backend
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
                    const resultTable = createHTMLTable(data.data);
                    let tableMessage = document.createElement("div");
                    tableMessage.className = "chat-message bot";
                    tableMessage.innerHTML = resultTable; // Insert the HTML table
                    chatBox.appendChild(tableMessage);
                    chatBox.scrollTop = chatBox.scrollHeight;

                    // Add the "Download Report" button
                    const downloadButton = document.createElement("button");
                    downloadButton.innerText = "Download Report";
                    downloadButton.onclick = function() {
                        generateReport(data.data); // Generate and download the report
                    };
                    tableMessage.appendChild(downloadButton); // Add to the message container
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