function checkEmailAvailability(email) {
    if (email === '') {
        document.getElementById('email-message').innerHTML = '';
        return;
    }
    fetch('check_email.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('email-message').innerHTML = data.message;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('email-message').innerHTML = 'Error checking email availability.';
    });
}