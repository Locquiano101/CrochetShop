// ================== GLOBAL VARIABLES ==================
let selectedRating = 0; // Tracks star rating
let cart_item = []; // Holds cart data globally

const cartUserJson = localStorage.getItem("user");

const cartUser = cartUserJson ? JSON.parse(cartUserJson) : null;

// Safely access properties or set default values
const userCartID = cartUser?.user_id || null;
// ================== FETCH CART DATA ==================
async function fetchCartData() {
  try {
    const response = await fetch("/src/PHP/get_cart.php");

    if (!response.ok) throw new Error("Failed to fetch cart data");

    cart_item = await response.json();
    if (cart_item.error) throw new Error(cart_item.error);

    console.log(cart_item);

    localStorage.setItem("allCart", JSON.stringify(cart_item));
    displayCartItems(cart_item);
    setupEventListeners();
    updateCartCount(cart_item.length);
  } catch (error) {
    console.error("Error fetching cart:", error.message);
  }
}

// ================== DISPLAY CART ITEMS ==================
function getEmptyCartHTML() {
  return `
    <div class="flex flex-col py-12 px-12 gap-12 bg-purple-100 rounded-lg shadow-md text-center">
      <p class="text-xl font-semibold text-gray-700">There are no items in the cart :)</p>
    </div>
  `;
}

function displayCartItems(cartItems) {
  const container = document.getElementById("cart-card-container");
  container.innerHTML =
    cartItems.length === 0
      ? getEmptyCartHTML()
      : cartItems.map(createCartItemHTML).join("");
}

function createCartItemHTML(cart) {
  const { order_status, prod_name, quantity, total_price, prod_img_file } =
    cart;
  const buttonProps = getButtonProperties(order_status);

  return `
    <div class="flex flex-col md:flex-row py-12 md:py-4 px-12 gap-12 bg-purple-100 rounded-lg shadow-md">
      <img src="/img/${prod_img_file}" alt="Product" class="w-64 h-auto m-auto rounded-lg" />
      <div class="flex-1 flex flex-col justify-center gap-2">
        <p class="lg:text-2xl text-xl lg:font-bold font-semibold">${prod_name}</p>
        <p class="text-lg text-gray-500">Status: <span>${order_status}</span></p>
      </div>
      <div class="flex-1 flex flex-col gap-6 md:justify-center md:text-right">
        <p class="text-lg text-right font-medium">Qty: ${quantity}</p>
        <p class="text-xl font-bold text-right">₱${total_price}</p>
        <button type="button" class="action-button px-12 py-2 text-sm font-semibold ${
          buttonProps.class
        } border rounded-lg" aria-label="${
    buttonProps.ariaLabel
  }" data-index="${cart_item.indexOf(cart)}">
          ${buttonProps.text}
        </button>
      </div>
    </div>
  `;
}

function getButtonProperties(status) {
  const actions = {
    Pending: {
      text: "Cancel",
      class: "text-red-600 border-red-600 hover:bg-red-100",
      ariaLabel: "Cancel order",
    },
    "On Process": {
      text: "Cancel",
      class: "text-red-600 border-red-600 hover:bg-red-100",
      ariaLabel: "Cancel order",
    },
    "In Cart": {
      text: "Check Out",
      class: "text-green-600 border-green-600 hover:bg-green-100",
      ariaLabel: "Check out",
    },
    Delivered: {
      text: "Rate",
      class: "text-yellow-600 border-yellow-600 hover:bg-yellow-100",
      ariaLabel: "Rate product",
    },
    Cancelled: {
      text: "Cancelled",
      class: "text-gray-600 border-gray-600 bg-gray-300 cursor-not-allowed",
      ariaLabel: "Default action",
      disabled: true, // Adding disabled flag for Cancelled status
    },
    default: {
      text: "Action",
      class: "text-gray-600 border-gray-600 hover:bg-gray-100",
      ariaLabel: "Default action",
    },
  };

  // Return the action properties for the given status
  return actions[status] || actions.default;
}

// ================== BUTTON HANDLER ==================
document.addEventListener("click", (event) => {
  const button = event.target.closest(".action-button");
  if (!button) return;

  const index = parseInt(button.dataset.index, 10);
  const cart = cart_item[index];

  try {
    handleCartAction(cart);
  } catch (error) {
    console.error("Failed to handle action:", error);
  }
});

function handleCartAction(cart) {
  const { order_status } = cart;
  switch (order_status) {
    case "In Cart":
      openCheckoutModal(cart);
      break;
    case "Pending":
    case "On Process":
      if (confirm(`Do you want to cancel "${cart.prod_name}"?`))
        openCancellationModal(cart);
      break;
    case "Delivered":
      openRatingModal(cart);
      break;
    default:
      console.warn("Unrecognized action for status:", order_status);
  }
}

// ================== CANCELLATION ==================
async function finalizeCancellation(orderID) {
  try {
    const cancelNoteElement = document.getElementById("cancel-note");

    const cancelNote = cancelNoteElement.value.trim();
    if (!cancelNote) {
      alert("Please provide a reason for cancellation.");
      return;
    }

    const payload = { order_item_id: orderID, cancel_note: cancelNote };

    const response = await fetch("/src/PHP/post_cancel_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const responseText = await response.text();
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error("Response is not valid JSON:", parseError, responseText);
      alert(
        "An unexpected error occurred while processing the server response."
      );
      return;
    }

    if (result.status === "error") {
      alert(`Error: ${result.message}`);
    } else {
      alert("Order cancelled successfully!");
      fetchCartData(); // Refresh cart data
    }
  } catch (error) {
    console.error("Error finalizing cancellation:", error);
    alert(
      "An unexpected error occurred during cancellation. Please try again."
    );
  } finally {
    document.getElementById("cancel-container").classList.add("hidden");
  }
}

function createCancellationHTML(cart) {
  return `
    <img src="/img/${cart.prod_img_file}" alt="Product" class="w-1/3 h-auto rounded-lg" />
    <div class="h-auto flex flex-col w-full text-purple-900 text-left gap-4 justify-center">
      <div class="flex justify-between">
        <h3 class="text-lg font-bold text-purple-900">${cart.prod_name}</h3>
        <p class="text-purple-900 text-lg font-semibold">₱${cart.total_price}</p>
      </div>
      <p class="text-left text-purple-900 text-lg"><strong>Description:</strong> ${cart.prod_description}</p>
      <div class="flex gap-4">
        <label for="cancel-note" class="font-semibold py-2 text-lg text-purple-900">Reason for Cancellation:</label>
        <textarea
          id="cancel-note"
          class="w-full p-2 h-32 border rounded-lg resize-none border-2 border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent"
          placeholder="Please provide a reason for cancellation">
        </textarea>
      </div>
      <button class="flex-2 py-4 text-center text-xl bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition-colors duration-300"
        onclick="finalizeCancellation(${cart.order_item_id})">
        <i class="fa-solid fa-ban mr-4"></i>Cancel Order
      </button>
    </div>
  `;
}

function openCancellationModal(cart) {
  const modal = document.getElementById("cancel-container");
  const cancelInfoContainer = document.getElementById("cancel-info-container");

  cancelInfoContainer.innerHTML = createCancellationHTML(cart);
  console.log(cart);
  modal.classList.remove("hidden");

  document.querySelector(".close").onclick = () =>
    closeCancellationModal(modal);
}

function closeCancellationModal(modal) {
  modal.classList.add("hidden");
  const cancelNoteElement = document.getElementById("cancel-note");
  if (cancelNoteElement) cancelNoteElement.value = "";
}

// ================== CHECKOUT ==================
async function finalizeCheckout(orderID) {
  try {
    const noteElement = document.getElementById("Note");
    if (!noteElement) {
      alert("Note element not found. Please try again.");
      return;
    }

    const note = noteElement.value.trim();
    const payload = { order_item_id: orderID, status: note };
    console.log(payload);
    const response = await fetch("/src/PHP/post_order_items.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      console.error("Server error:", response.status, response.statusText);
      alert(
        `Failed to complete checkout. Server responded with: ${response.status} ${response.statusText}`
      );
      return;
    }

    // Safely parse the response as JSON
    const responseText = await response.text();
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error("Response is not valid JSON:", parseError, responseText);
      alert(
        "An unexpected error occurred while processing the server response."
      );
      return;
    }

    // Handle the server response
    if (result.status === "error") {
      alert(`Error: ${result.message}`);
    } else {
      alert("Checkout successful!");
      fetchCartData(); // Refresh cart data
    }
  } catch (error) {
    console.error("Error finalizing checkout:", error);
    alert("An unexpected error occurred during checkout. Please try again.");
  } finally {
    // Re-enable the button after processing
    document.getElementById("check-out-container").classList.add("hidden");
  }
}

function createCheckoutHTML(cart) {
  return `
    <img src="/img/${cart.prod_img_file}" alt="Product" class="w-1/3 h-auto rounded-lg" />
    <div class="h-auto flex flex-col w-full text-purple-900 text-left gap-4 justify-center">
      <div class="flex justify-between">
        <h3 class="text-lg font-bold text-purple-900">${cart.prod_name}</h3>
        <p class="text-purple-900 text-lg font-semibold">₱${cart.total_price}</p>
      </div>
      <p class="text-left text-purple-900 text-lg"><strong>Description:</strong> ${cart.prod_description}</p>
      <div class="flex gap-4">
        <label for="Note" class="font-semibold py-2 text-lg text-purple-900">Note:</label>
        <textarea
          id="Note"
          class="w-full p-2 h-32 border rounded-lg"
          placeholder="Add any special notes for this order">
        </textarea>
      </div>
      <button class="flex-2 py-4 text-center text-xl bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors duration-300"
        onclick="finalizeCheckout(${cart.order_item_id})">
        <i class="fa-solid fa-truck mr-4"></i>Check Out
      </button>
    </div>
  `;
}

function openCheckoutModal(cart) {
  const modal = document.getElementById("check-out-container");
  const productContainer = document.getElementById("products-container");

  productContainer.innerHTML = createCheckoutHTML(cart);
  console.log(cart);
  modal.classList.remove("hidden");

  document.querySelector(".close").onclick = () => closeCheckoutModal(modal);
}

function closeCheckoutModal(modal) {
  modal.classList.add("hidden");
  document.getElementById("Note").value = "";
}

// ================== RATING MODAL ==================
function openRatingModal(cart) {
  const modal = document.getElementById("modalContainer2");
  document.getElementById("modal-title").textContent = `Rate Product`;
  document.getElementById(
    "modal-product-name"
  ).textContent = `Product Name: ${cart.prod_name}`;
  document.getElementById(
    "modal-product-img"
  ).src = `/img/${cart.prod_img_file}`;
  document.getElementById("modal-product-img").alt = cart.prod_name;

  modal.classList.remove("hidden");
  console.log();
  document.getElementById("close-modal").onclick = () => closeModal(modal);
  document.getElementById("closeModalButton").onclick = () => closeModal(modal);

  setupStarRating(cart.prod_id);
}

function closeModal(modal) {
  modal.classList.add("hidden");
  document.getElementById("feedback").value = "";
  updateStarDisplay(-1);
}

// ================== STAR RATING ==================
function setupStarRating(productId) {
  const stars = document.querySelectorAll("#modal-stars .fa-star");

  stars.forEach((star, index) => {
    star.addEventListener("click", () => {
      selectedRating = index + 1;
      updateStarDisplay(index);
    });
  });

  document.getElementById("submitRatingButton").onclick = () =>
    submitRating(productId);
}

function updateStarDisplay(selectedIndex) {
  const stars = document.querySelectorAll("#modal-stars .fa-star");

  stars.forEach((star, index) => {
    star.classList.toggle("fa-solid", index <= selectedIndex);
    star.classList.toggle("fa-regular", index > selectedIndex);
    star.classList.toggle("text-yellow-500", index <= selectedIndex);
  });
}
async function submitRating(productId) {
  const comment = document.getElementById("feedback").value;

  if (selectedRating === 0) {
    alert("Please select a rating before submitting.");
    return;
  }

  // Find the cart item based on the product ID
  const cart = cart_item.find((item) => item.prod_id === productId);

  if (!cart) {
    alert("Error: Unable to find the cart item for this product.");
    return;
  }

  await submitReview(productId, cart.order_item_id, selectedRating, comment);
  closeModal(document.getElementById("modalContainer2"));
}

async function submitReview(productId, orderItemId, rating, comment) {
  const payload = {
    action: "submit_rating_and_update_order",
    customer_id: userCartID, // Ensure userCartID is defined properly
    order_item_id: orderItemId,
    product_id: productId,
    rating,
    comment,
  };

  console.log("Submitting review payload:", payload);

  try {
    const response = await fetch(
      "/src/PHP/post_reviews.php", // Check if this URL is correct
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      }
    );

    const result = await response.json();

    // Handle invalid JSON response from server
    if (response.ok) {
      alert(result.message || "Review submitted successfully!");
    } else {
      throw new Error(result.message || "Unexpected error occurred.");
    }
  } catch (error) {
    console.error("Error submitting review:", error);
    alert(
      "An error occurred while submitting your review. Please try again later."
    );
  } finally {
    document.getElementById("modalContainer2").classList.add("hidden");
  }
}

// ================== FILTER CART ITEMS ==================
function filterCartItems(status) {
  const filtered = cart_item.filter((item) => item.order_status === status);
  displayCartItems(filtered);
}

// ================== UPDATE CART COUNT ==================
function updateCartCount(count) {
  const cartLink = document.querySelector(
    "#cart-button-desktop, #cart-button-mobile"
  );
  if (cartLink) {
    cartLink.innerHTML = `<i class="fas fa-shopping-cart mr-2"></i> Cart (${count})`;
  }
}

// ================== EVENT LISTENERS ==================
function setupEventListeners() {
  const filters = [
    { id: "filter-all", action: () => displayCartItems(cart_item) },
    {
      id: "filter-delivered",
      action: () => filterCartItems("Delivered"),
    },
    {
      id: "filter-pending",
      action: () => filterCartItems("Pending"),
    },
    {
      id: "filter-on-process",
      action: () => filterCartItems("On Process"),
    },
    {
      id: "filter-in-cart",
      action: () => filterCartItems("In Cart"),
    },
  ];

  filters.forEach(({ id, action }) => {
    document.getElementById(`${id}-desktop`)?.addEventListener("click", action);
    document.getElementById(`${id}-mobile`)?.addEventListener("click", action);
  });
}

// ================== INITIALIZATION ==================
fetchCartData();
