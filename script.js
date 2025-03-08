// Fetch volunteers and display them as cards
function fetchVolunteers() {
    fetch('get_volunteers.php')  // Make sure this points to your correct PHP file
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayVolunteers(data.data);
            } else {
                displayErrorMessage("Failed to load volunteers");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            displayErrorMessage("Error fetching volunteers");
        });
}

// Display volunteers as cards
function displayVolunteers(volunteers) {
    const container = document.getElementById('volunteersContainer');
    container.innerHTML = '';  // Clear previous content

    volunteers.forEach(volunteer => {
        const card = document.createElement('div');
        card.classList.add('card');
        
        // Add volunteer details to the card
        card.innerHTML = `
            <h2>${volunteer.first_name} ${volunteer.last_name}</h2>
            <p>Email: ${volunteer.email}</p>
            <p>Phone: ${volunteer.phone}</p>
            <p>Date of Birth: ${volunteer.date_of_birth || 'N/A'}</p>
            <p>Status: ${volunteer.status}</p>
        `;
        
        // Append the card to the container
        container.appendChild(card);
    });
}

// Search volunteers by name, email, or phone
function searchVolunteers() {
    const searchTerm = document.getElementById('searchInput').value;
    const searchParams = new URLSearchParams();

    if (searchTerm) {
        searchParams.append('search', searchTerm);
    }

    fetch(`search_volunteers.php?${searchParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayVolunteers(data.data);
            } else {
                displayErrorMessage("No volunteers found");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            displayErrorMessage("Error searching volunteers");
        });
}

// Display error messages
function displayErrorMessage(message) {
    const messageElement = document.getElementById('errorMessage');
    messageElement.innerText = message;
}

// Initial fetch of volunteers when the page loads
window.onload = function() {
    fetchVolunteers();
};