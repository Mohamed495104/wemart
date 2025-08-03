document.addEventListener("DOMContentLoaded", fetchWeatherSuggestions);

document.querySelectorAll(".carousel-container").forEach((container) => {
  const carousel = container.querySelector(".carousel");
  const prevBtn = container.querySelector(".carousel-prev");
  const nextBtn = container.querySelector(".carousel-next");
  prevBtn.addEventListener("click", () => {
    carousel.scrollBy({ left: -300, behavior: "smooth" });
  });
  nextBtn.addEventListener("click", () => {
    carousel.scrollBy({ left: 300, behavior: "smooth" });
  });
});
