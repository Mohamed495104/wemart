

function fetchWeatherSuggestions() {
    fetch(`https://api.openweathermap.org/data/2.5/weather?q=Toronto&appid=${API_KEY}`)
        .then(response => response.json())
        .then(data => {
            const suggestionsDiv = document.getElementById('weather-suggestions');
            if (data.weather && data.weather[0].main === 'Rain') {
                suggestionsDiv.innerHTML = '<p>Shop umbrellas and raincoats today!</p>';
            } else if (data.weather && data.weather[0].main === 'Clear') {
                suggestionsDiv.innerHTML = '<p>Check out outdoor furniture and grills!</p>';
            }
        })
        .catch(error => console.error('Error fetching weather:', error));
}

document.addEventListener('DOMContentLoaded', fetchWeatherSuggestions);


function checkEmailAvailability(email) {
    fetch('http://localhost/wemart/api/check_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('email-message');
        if (data.status === 'success') {
            messageDiv.textContent = data.message;
            messageDiv.style.color = data.exists ? 'red' : 'green';
        } else {
            messageDiv.textContent = data.message;
            messageDiv.style.color = 'red';
        }
    })
    .catch(error => console.error('Error checking email:', error));
}