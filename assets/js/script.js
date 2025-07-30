

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

document.querySelectorAll('.carousel-container').forEach(container => {
    const carousel = container.querySelector('.carousel');
    const prevBtn = container.querySelector('.carousel-prev');
    const nextBtn = container.querySelector('.carousel-next');
    prevBtn.addEventListener('click', () => {
        carousel.scrollBy({ left: -300, behavior: 'smooth' });
    });
    nextBtn.addEventListener('click', () => {
        carousel.scrollBy({ left: 300, behavior: 'smooth' });
    });
});

import Swiper from 'https://unpkg.com/swiper/swiper-bundle.min.js';
new Swiper('.carousel', {
    slidesPerView: 'auto',
    spaceBetween: 24,
    navigation: {
        nextEl: '.carousel-next',
        prevEl: '.carousel-prev',
    },
});