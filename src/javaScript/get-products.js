const productsJSON = localStorage.getItem("user");

const get_products_user = productsJSON ? JSON.parse(productsJSON) : null;

console.log(get_products_user);

const get_products_userID = get_products_user?.user_id;
const get_products_UserName = get_products_user?.user_name;
const get_products_userFullAddress = get_products_user?.user_address;

async function fetchProductsData() {
  try {
    const response = await fetch("/src/PHP/get_products.php");

    const products = await response.json();

    if (products.error) {
      throw new Error(products.error);
    }

    // Store all fetched products in localStorage for inspection
    localStorage.setItem("allProducts", JSON.stringify(products));
    console.log("Fetched Products:", products);

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
        "w-full" // Ensure the card spans the full width
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

            <h3 class="text-xl font-bold text-gray-900">
              ${product.prod_name}
            </h3>
            <p class="text-gray-600 text-lg">
              ₱ ${parseFloat(product.prod_price).toFixed(2)}
            </p>

            <div class="flex flex-wrap justify-between gap-2">
              <a
                href="#"
                  class="flex-1 text-center bg-purple-700 text-white py-2 rounded transition-colors duration-300 hover:bg-purple-900"
              >
                View Details
              </a>
              <a
                href="javascript:void(0)"
                data-product-id="${product.prod_id}"
                data-product-name="${product.prod_name}"
                data-product-price="${product.prod_price}"
                data-product-img="${product.prod_img_file}"
                class="flex-2 text-center bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition-colors duration-300 add-to-cart"
              >
                <i class="fas fa-shopping-cart"></i>
              </a>
            </div>
          </div>
      `;

      productContainer.appendChild(productCard);
    });
    // Object to track the quantity of products added to the cart
    const cartQuantities = {};

    document.addEventListener("click", async (event) => {
      if (event.target.closest(".add-to-cart")) {
        const button = event.target.closest(".add-to-cart");
        const productId = button.getAttribute("data-product-id");

        if (!get_products_userID || isNaN(parseInt(get_products_userID, 10))) {
          alert("Please sign in to proceed.");
          // Optionally redirect to a sign-in page
          window.location.href = "/main/login.html"; // Replace with your sign-in page URL
          return; // Stop further execution
        }
        const productData = {
          customer_id: parseInt(get_products_userID, 10), // Ensure it's an integer
          prod_id: parseInt(productId, 10), // Ensure it's an integer
          shipping_address: get_products_userFullAddress, // Sanitize strings
          product_name: button.getAttribute("data-product-name").trim(),
          product_price: parseFloat(button.getAttribute("data-product-price")), // Ensure it's a float
          product_qty: cartQuantities[productId] || 1,
        };

        console.log("Adding product to cart:", productData);
        try {
          const response = await fetch("/src/PHP/post_order.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(productData),
          });

          const responseText = await response.text(); // Get the response as text
          console.log("Raw Response Text:", responseText); // Log the raw response

          try {
            const result = JSON.parse(responseText); // Try parsing the response as JSON
            if (result.status === "success") {
              alert("Product added to cart successfully!");
            } else {
              console.log(`Error: ${result.message || "Unknown error"}`);
            }
          } catch (e) {
            console.error("Error parsing response as JSON:", e);
          }
        } catch (error) {
          console.error("Error adding product to cart:", error);
          alert("An error occurred while adding the product to the cart.");
        }
      }
    });
  } catch (error) {
    console.error("Error fetching products:", error.message);
  }
}

fetchProductsData();
