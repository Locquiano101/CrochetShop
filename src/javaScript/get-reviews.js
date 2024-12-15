// reviewSection.js

// Function to fetch reviews and display them asynchronously
async function loadReviews() {
  const reviewsContainer = document.getElementById("review-container");
  reviewsContainer.innerHTML = "";

  try {
    // Replace this URL with your actual API endpoint or JSON file path
    const response = await fetch("/src/PHP/get_reviews.php");

    if (!response.ok) {
      throw new Error("Failed to fetch reviews");
    }

    const reviews = await response.json();

    if (reviews.length > 0) {
      console.log(reviews);
      reviews.forEach((review) => {
        const reviewElement = document.createElement("div");
        reviewElement.className =
          "bg-purple-100 px-6 py-4 rounded-xl space-y-4";
        reviewElement.innerHTML = `
                    <div class="flex text-lg gap-4 items-center">
                        <img
                            src="https://via.placeholder.com/500"
                            alt="Customer"
                            class="rounded-full w-16 h-16"
                        />
                        <div class="flex flex-col justify-between text-left gap-2">
                          <p class="text-purple-900 text-xl font-semibold">${sanitizeHTML(
                            review.user_firstName
                          )} ${sanitizeHTML(review.user_lastName)}</p>
                          <p class="text-purple-900 text-sm italic">${sanitizeHTML(
                            review.prod_name
                          )}</p>  
                          <p class="text-purple-900 text-sm font-bold space-x-2">${generateStarIcons(
                            review.rev_rate
                          )}</p>
                        </div>
                    </div>
                    <p class="text-gray-900  italic">
                        "${sanitizeHTML(review.rev_comment)}"
                    </p>
                `;
        reviewsContainer.appendChild(reviewElement);
      });
    } else {
      reviewsContainer.innerHTML =
        "<p class='text-gray-700'>No reviews available.</p>";
    }
  } catch (error) {
    console.error("Error loading reviews:", error);
    reviewsContainer.innerHTML =
      "<p class='text-gray-700'>Failed to load reviews.</p>";
  }
}

// Helper function to sanitize HTML to prevent XSS
function sanitizeHTML(string) {
  const tempDiv = document.createElement("div");
  tempDiv.textContent = string;
  return tempDiv.innerHTML;
}

// Helper function to generate star icons using Font Awesome
function generateStarIcons(rating) {
  const fullStar = '<i class="fa-solid fa-star"></i>';
  const emptyStar = '<i class="fa-regular fa-star"></i>';
  return fullStar.repeat(rating) + emptyStar.repeat(5 - rating);
}

// Wait for the DOM to load before running the script
document.addEventListener("DOMContentLoaded", () => {
  loadReviews();
});
