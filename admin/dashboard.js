// Utility functions
function hideAllSections() {
  document.querySelectorAll("main > section").forEach((section) => {
    section.classList.add("hidden");
  });
}

function showSection(sectionId) {
  hideAllSections();
  const section = document.getElementById(sectionId);
  if (section) section.classList.remove("hidden");

  document.querySelectorAll("aside button").forEach((button) => {
    button.classList.remove("bg-white", "text-purple-700");
  });
  const activeButton = document.querySelector(
    `#${sectionId.replace("section-", "")}`
  );
  if (activeButton) activeButton.classList.add("bg-white", "text-purple-700");
}

async function fetchProductsData() {
  try {
    const response = await fetch("/admin/dashboard.php");

    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const products = await response.json();

    if (products.error) {
      throw new Error(products.error);
    }

    // Store all fetched products in localStorage for inspection
    localStorage.setItem("allProducts", JSON.stringify(products));
    console.log("Fetched Products:", products);

    renderProducts(products);
  } catch (error) {
    console.error("Error fetching products:", error.message);
  }
}

function renderProducts(products) {
  const productContainer = document.getElementById("products-container");
  productContainer.innerHTML = "";

  products.forEach((product) => {
    const productCard = document.createElement("div");
    productCard.classList.add(
      "flex",
      "flex-col",
      "justify-between",
      "bg-purple-300",
      "rounded-lg",
      "shadow-md",
      "overflow-hidden",
      "p-4",
      "h-full",
      "w-full"
    );
    productCard.innerHTML = `
      <div class="bg-white rounded-lg overflow-hidden">
        <img
          src="/img/${product.prod_img_file}"
          alt="Product"
          class="w-full p-2 rounded-2xl transition-transform duration-300 hover:scale-110"
        />
      </div>

      <div class="flex flex-col gap-2 p-2">
        <h3 class="text-xl font-bold text-gray-900">${product.prod_name}</h3>
        <p class="text-gray-600 text-lg">₱ ${parseFloat(
          product.prod_price
        ).toFixed(2)}</p>

        <div class="flex flex-wrap justify-between gap-2">
          <a
            href="javascript:void(0)"
            data-product-id="${product.prod_id}"
            class="flex-1 text-center bg-red-600 text-white py-2 rounded transition-colors duration-300 hover:bg-red-700 delete-product"
          >
            Delete
          </a>
          <a
            href="javascript:void(0)"
            data-product-id="${product.prod_id}"
            data-product-name="${product.prod_name}"
            data-product-description="${product.prod_description}"
            data-product-price="${product.prod_price}"
            data-product-img="${product.prod_img_file}"
            class="flex-2 text-center bg-yellow-500 text-white px-6 py-2 rounded hover:bg-yellow-600 transition-colors duration-300 update-product"
          >
            Update
          </a>
        </div>
      </div>
    `;

    productContainer.appendChild(productCard);
  });

  setupProductButtons();
}

function setupProductButtons() {
  const deleteButtons = document.querySelectorAll(".delete-product");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const productId = event.target.getAttribute("data-product-id");
      if (confirm("Are you sure you want to delete this product?")) {
        deleteProduct(productId);
      }
    });
  });

  const updateButtons = document.querySelectorAll(".update-product");
  updateButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const product = {
        id: event.target.getAttribute("data-product-id"),
        name: event.target.getAttribute("data-product-name"),
        description: event.target.getAttribute("data-product-description"),
        price: event.target.getAttribute("data-product-price"),
        img: event.target.getAttribute("data-product-img"),
      };
      localStorage.setItem("selectedProduct", JSON.stringify(product));
      window.location.href = "update_product.html";
    });
  });
}

async function deleteProduct(productId) {
  try {
    const response = await fetch("/src/PHP/delete_product.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `product_id=${encodeURIComponent(productId)}`,
    });

    if (!response.ok) {
      throw new Error("Failed to delete the product.");
    }

    console.log(await response.text());
    location.reload();
  } catch (error) {
    console.error("Error deleting product:", error);
  }
}

// Reviews Section
async function loadReviews() {
  const reviewsContainer = document.getElementById("reviews-container");
  reviewsContainer.innerHTML = "";

  try {
    const reviews = await fetchReviews();
    if (reviews.length > 0) {
      renderReviews(reviews, reviewsContainer);
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

async function fetchReviews() {
  const response = await fetch("/src/PHP/get_reviews.php");
  if (!response.ok) {
    throw new Error(`HTTP error! Status: ${response.status}`);
  }

  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch (error) {
    console.error("Invalid JSON response:", text);
    throw new Error("Response is not valid JSON");
  }
}

function renderReviews(reviews, container) {
  reviews.forEach((review) => {
    const reviewElement = createReviewElement(review);
    container.appendChild(reviewElement);
  });
  setupReviewButtons();
}

function createReviewElement(review) {
  const reviewElement = document.createElement("div");
  reviewElement.className = "bg-purple-100 px-6 py-4 rounded-xl space-y-4";
  reviewElement.innerHTML = `
    <div class="flex text-lg w-full gap-4 items-center">
      <img
        src="https://via.placeholder.com/500"
        alt="Customer"
        class="rounded-full w-16 h-16"
      />
      <div class="flex flex-col w-full justify-between text-left gap-2">
        <p class="text-purple-900 text-xl font-semibold">
          ${sanitizeHTML(review.user_firstName)} ${sanitizeHTML(
    review.user_lastName
  )}
        </p>
        <p class="text-purple-900 text-sm italic">
          ${sanitizeHTML(review.prod_name)}
        </p>
        <p class="text-purple-900 text-sm font-bold space-x-2">
          ${generateStarIcons(review.rev_rate)}
        </p>
        <button onclick="deleteReview(${
          review.rev_id
        })" class="bg-red-200 py-2">Delete</button>
      </div>
    </div>
  `;
  return reviewElement;
}
// Delete review
function deleteReview(rev_id) {
  if (confirm("Are you sure you want to delete this review?")) {
    fetch("/src/PHP/delete_review.php", {
      method: "DELETE",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `rev_id=${encodeURIComponent(rev_id)}`,
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((error) => {
            throw new Error(error.error || "Failed to delete review.");
          });
        }
        return response.json();
      })
      .then((data) => {
        alert(data.message || "Review deleted successfully.");
        fetchReviews(); // Refresh the list after deletion
      })
      .catch((error) => {
        alert(error.message || "An error occurred.");
      });
  }
}

function sanitizeHTML(string) {
  const tempDiv = document.createElement("div");
  tempDiv.textContent = string;
  return tempDiv.innerHTML;
}

function generateStarIcons(rating) {
  const fullStar = '<i class="fa-solid fa-star"></i>';
  const emptyStar = '<i class="fa-regular fa-star"></i>';
  return fullStar.repeat(rating) + emptyStar.repeat(5 - rating);
}

function setupReviewButtons() {
  const deleteButtons = document.querySelectorAll(".delete-review-button");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", async (event) => {
      const reviewId = event.target.getAttribute("data-review-id");
      if (confirm("Are you sure you want to delete this review?")) {
        await deleteReview(reviewId);
      }
    });
  });
}

// Wait for the DOM to load
fetchProductsData();
loadReviews();
