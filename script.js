// Fetch volunteers and display them as cards
function fetchVolunteers() {
  fetch("get_volunteers.php") // Make sure this points to your correct PHP file
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json(); // Parse JSON
    })
    .then((data) => {
      console.log(data); // Log the response to check
      if (data.status === "success") {
        displayVolunteers(data.data);
      } else {
        displayErrorMessage("Failed to load volunteers");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      displayErrorMessage("Error fetching volunteers");
    });
}

// Display volunteers as cards
function displayVolunteers(volunteers) {
  const container = document.getElementById("volunteersContainer");
  container.innerHTML = ""; // Clear previous content

  volunteers.forEach((volunteer) => {
    const card = document.createElement("div");
    card.classList.add("card");

    // Add volunteer details to the card
    card.innerHTML = `
            <h2>${volunteer.first_name} ${volunteer.last_name}</h2>
            <p>Email: ${volunteer.email}</p>
            <p>Phone: ${volunteer.phone}</p>
            <p>Date of Birth: ${volunteer.date_of_birth || "N/A"}</p>
            <p>Status: ${volunteer.status}</p>
        `;

    // Append the card to the container
    container.appendChild(card);
  });
}

function searchVolunteers() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase();
  const allVolunteers = document.querySelectorAll(".card");

  allVolunteers.forEach((card) => {
    const name = card.querySelector("h2").innerText.toLowerCase();
    const email = card.querySelector("p:nth-child(2)").innerText.toLowerCase();
    const phone = card.querySelector("p:nth-child(3)").innerText.toLowerCase();

    if (
      name.includes(searchTerm) ||
      email.includes(searchTerm) ||
      phone.includes(searchTerm)
    ) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

// Display error messages
function displayErrorMessage(message) {
  const messageElement = document.getElementById("errorMessage");
  messageElement.innerText = message;
}

// Initial fetch of volunteers when the page loads
window.onload = function () {
  fetchVolunteers();
};
