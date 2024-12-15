// Toggle the visibility of the mobile menu
function toggleMobileMenu() {
  const mobileMenu = document.getElementById("mobileMenu");
  if (mobileMenu) {
    mobileMenu.classList.toggle("hidden");
  } else {
    console.error("Mobile menu element not found.");
  }
}

// Retrieve the user from localStorage
const indexUserJson = localStorage.getItem("user");

const user = indexUserJson ? JSON.parse(indexUserJson) : null;

// Safely access properties or set default values
const userID = user?.user_id || null;
const userFullName = user?.user_name || "Login";
const userFullAddress = user?.user_address || "Login";

// Log the user's ID and full name to the console
console.log("User ID:", userID);
console.log("User Full Name:", userFullName);
console.log("User Full Address:", userFullAddress);

document.addEventListener("DOMContentLoaded", () => {
  // Update elements with the user's full name
  const userNameElement = document.getElementById("userNameDisplay");
  const userNameElement2 = document.getElementById("userNameDisplay2");

  if (userNameElement) {
    userNameElement.innerHTML = userFullName;
  }

  if (userNameElement2) {
    userNameElement2.innerHTML = userFullName;
  }

  // Define the login or logout handler
  const handleLoginOrLogout = () => {
    if (userID) {
      showLogoutModal();
    } else {
      window.location.href = "login.html"; // Redirect to login page
    }
  };

  // Attach event listeners to login buttons
  const loginButtons = document.querySelectorAll(
    "#login-button-desktop, #login-button-mobile"
  );
  loginButtons.forEach((button) => {
    button.addEventListener("click", handleLoginOrLogout);
  });

  // Show the logout modal
  const showLogoutModal = () => {
    const logoutModal = document.getElementById("logoutModal");
    if (logoutModal) {
      logoutModal.classList.remove("hidden");
    } else {
      console.error("Logout modal element not found.");
    }
  };

  // Close the logout modal
  const closeModal = () => {
    const logoutModal = document.getElementById("logoutModal");
    if (logoutModal) {
      logoutModal.classList.add("hidden");
    } else {
      console.error("Logout modal element not found.");
    }
  };

  // Attach the logout and close modal functionality
  const logoutButton = document.getElementById("logoutBtn");
  const closeModalButton = document.getElementById("closeModalBtn");

  if (logoutButton) {
    logoutButton.addEventListener("click", logout);
  }

  if (closeModalButton) {
    closeModalButton.addEventListener("click", closeModal);
  }
});

// Logout functionality
const logout = () => {
  localStorage.removeItem("user"); // Remove user data
  window.location.reload(); // Reload the page
};

async function fetchCartData() {
  try {
    const response = await fetch("/src/PHP/get_cart.php");

    if (!response.ok) throw new Error("Failed to fetch cart data");

    const carts = await response.json();

    if (carts.error) throw new Error(carts.error);

    localStorage.setItem("allCart", JSON.stringify(carts));

    const cartCount = carts.length || 0; // Assume carts is an array of items
    const cartLinkDesktop = document.getElementById("cart-button-desktop");
    const cartLinkMobile = document.getElementById("cart-button-mobile");
    cartLinkDesktop.innerHTML = `<i class="fas fa-shopping-cart mr-2"></i> Cart (${cartCount})`;
    cartLinkMobile.innerHTML = `<i class="fas fa-shopping-cart mr-2"></i> Cart (${cartCount})`;
  } catch (error) {
    console.error("Error fetching cart:", error.message);
  }
}
fetchCartData();
