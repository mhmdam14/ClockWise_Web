// Get the modal
let modal = document.getElementById("myModal");

// Function to open the modal
function openModal(id, event_name, event_description, event_date, event_start_time, event_end_time) {
    if (!modal) return; // Prevent errors if modal is not found

    modal.style.display = "block";

    if (id !== -1) {
        let eventIdField = document.getElementById("event_id");
        let eventNameField = document.getElementById("event_name");
        let eventDescField = document.getElementById("event_description");
        let eventDateField = document.getElementById("event_date");
        let eventStartTimeField = document.getElementById("event_start_time");
        let eventFinishTimeField = document.getElementById("event_finish_time");
        let submitBtn = document.getElementById("submit_btn");

        if (eventIdField) eventIdField.value = id;
        if (eventNameField) eventNameField.value = event_name;
        if (eventDescField) eventDescField.value = event_description;
        if (eventDateField) eventDateField.value = event_date;
        if (eventStartTimeField) eventStartTimeField.value = event_start_time;
        if (eventFinishTimeField) eventFinishTimeField.value = event_end_time;
        if (submitBtn) submitBtn.innerText = "Edit";
    }
    
}

// Function to close the modal
function closeModal() {
    if (modal) {
        modal.style.display = "none";
    }
}

// Close modal when clicking outside of it
window.addEventListener("click", function(event) {
    if (event.target === modal) {
        closeModal();
    }
});

// Prevent modal from closing when clicking inside the content
let modalContent = document.querySelector(".modal-content");
if (modalContent) {
    modalContent.addEventListener("click", function(event) {
        event.stopPropagation();
    });
}

function navigateWeek(offset) {
    const urlParams = new URLSearchParams(window.location.search);
    let currentWeek = parseInt(urlParams.get("week")) || 0;
    currentWeek += offset;

    // Update URL and reload page
    window.location.href = `calendar.php?week=${currentWeek}`;
}

function openPopup(id, name, startTime, endTime, description, date) {
    document.getElementById("popup-event-id").value = id;
    document.getElementById("popup-event-name").innerText = name;
    document.getElementById("popup-start-time").innerText = startTime;
    document.getElementById("popup-end-time").innerText = endTime;
    document.getElementById("popup-description").innerText = description;

    document.getElementById("edit-btn").setAttribute(
        "onclick",
        `editEvent(${id}, '${name}', '${startTime}', '${endTime}', '${description}', '${date}')`
    );
    document.getElementById("delete-btn").setAttribute(
        "onclick",
        `deleteEvent(${id})`
    );

    document.getElementById("popup").style.display = "block";
}

function closePopup() {
    document.getElementById("popup").style.display = "none";
}

function deleteEvent(id) {
    if (!confirm("Are you sure you want to delete this event?")) return;

    fetch("deleteEvent.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + encodeURIComponent(id)
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch((error) => {
            alert("Request failed: " + error);
        });
}

function editEvent(id, eventName, eventStartTime, eventFinishTime, eventDescription, eventDate) {
    
    // Populate the form fields
    openModal(id, eventName, eventDescription, eventDate, eventStartTime, eventFinishTime);

    const form = document.getElementById("eventForm");

    if (form) {
        // Remove any existing event listener before adding a new one
        form.removeEventListener("submit", handleEditFormSubmit);
        form.addEventListener("submit", handleEditFormSubmit);
    }

    function handleEditFormSubmit(event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(form);

        fetch("editEvent.php", {
            method: "POST",
            body: formData
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert(data.message);
                    closeModal();
                    setTimeout(() => {
                        location.reload(); // Reload after closing modal
                    }, 300);
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch((error) => {
                alert("Request Failed: " + error);
            });
    }
}
function goToToday() {
    // Navigate to the current week by setting week parameter to 0
    window.location.href = 'calendar.php?week=0';
}

