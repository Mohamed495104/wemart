//Client side(UI) live Email validation functionality
let emailTimeout;
const emailInput = document.getElementById("email");
const emailMessage = document.getElementById("email-message");
const submitBtn = document.getElementById("submit-btn");
let isEmailValid = false;

// Add event listener to email input
emailInput.addEventListener("input", function () {
  const email = this.value.trim();

  // Clear previous timeout
  clearTimeout(emailTimeout);

  // Hide message if email is empty
  if (email === "") {
    hideEmailMessage();
    isEmailValid = false;
    updateSubmitButton();
    return;
  }

  // Basic email format validation
  if (!isValidEmailFormat(email)) {
    showEmailMessage("Invalid email format", "email-error");
    isEmailValid = false;
    updateSubmitButton();
    return;
  }

  // Show loading message
  showEmailMessage("Checking email availability...", "email-loading");

  // Debounce API call (wait 500ms after user stops typing)
  emailTimeout = setTimeout(() => {
    checkEmailAvailability(email);
  }, 500);
});

/**
 * Check if email format is valid
 * @param {string} email - Email to validate
 * @returns {boolean} - True if valid format
 */
function isValidEmailFormat(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Check email availability via REST API
 * @param {string} email - Email to check
 */
async function checkEmailAvailability(email) {
  try {
    const response = await fetch("api/check_email.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        email: email,
      }),
    });

    // Check if response is ok
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.status === "success") {
      if (data.exists) {
        showEmailMessage("Email already exists", "email-exists");
        isEmailValid = false;
      } else {
        showEmailMessage("Email is available", "email-available");
        isEmailValid = true;
      }
    } else {
      showEmailMessage(data.message || "Error checking email", "email-error");
      isEmailValid = false;
    }
  } catch (error) {
    console.error("Error checking email:", error);
    showEmailMessage("Error checking email availability", "email-error");
    isEmailValid = false;
  }

  updateSubmitButton();
}

/**
 * Show email validation message
 * @param {string} message - Message to display
 * @param {string} className - CSS class for styling
 */
function showEmailMessage(message, className) {
  emailMessage.textContent = message;
  emailMessage.className = `email-message ${className}`;
  emailMessage.style.display = "block";
}

/**
 * Hide email validation message
 */
function hideEmailMessage() {
  emailMessage.style.display = "none";
  emailMessage.textContent = "";
  emailMessage.className = "email-message";
}

/**
 * Update submit button state based on email validation
 */
function updateSubmitButton() {
  const email = emailInput.value.trim();

  if (email !== "" && !isEmailValid) {
    submitBtn.disabled = true;
    submitBtn.style.opacity = "0.6";
    submitBtn.style.cursor = "not-allowed";
  } else {
    submitBtn.disabled = false;
    submitBtn.style.opacity = "1";
    submitBtn.style.cursor = "pointer";
  }
}

// Form submission validation
document.querySelector("form").addEventListener("submit", function (e) {
  const email = emailInput.value.trim();

  if (email !== "" && !isEmailValid) {
    e.preventDefault();
    alert("Please use a different email address.");
    return false;
  }
});
