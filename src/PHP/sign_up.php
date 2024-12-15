<?php
// Display error messages, if any
$errorMsg = $successMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $municipality = $_POST['municipality'] ?? '';
    $baranggay = $_POST['baranggay'] ?? '';
    $houseTitle = $_POST['houseTitle'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate the form inputs
    if ($password !== $confirmPassword) {
        $errorMsg = 'Passwords do not match!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Invalid email address!';
    } else {
        // Hash password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Placeholder: Code to save the user to a database
        // Replace this section with database insertion logic.
        // Example: MySQL connection and insertion (not shown here)
        $successMsg = 'Sign up successful! Welcome to Milo\'s Crochet Shop.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>

    <section class="fixed inset-0 flex justify-center rounded items-center bg-purple-300 ">
        <section class="modal bg-white rounded-lg w-[45%] px-8 py-4 h-[90%] overflow-auto">
            <h2 class="text-lg font-bold text-purple-900 text-center mb-4">
                WELCOME TO MILO'S CROCHET SHOP
            </h2>
            <?php if ($errorMsg): ?>
                <p class="text-red-600 font-bold text-center"><?= htmlspecialchars($errorMsg) ?></p>
            <?php endif; ?>
            <?php if ($successMsg): ?>
                <p class="text-green-600 font-bold text-center"><?= htmlspecialchars($successMsg) ?></p>
            <?php endif; ?>

            <form method="POST" class="flex flex-col gap-2">
                <label for="firstName" class="text-purple-900 block text-sm font-medium">First Name</label>
                <input type="text" id="firstName" name="firstName" class="px-4 py-2 border border-gray-300 rounded-lg w-full" placeholder="Enter your first name" required>

                <label for="lastName" class="text-purple-900 block text-sm font-medium">Last Name</label>
                <input type="text" id="lastName" name="lastName" class="px-4 py-2 border border-gray-300 rounded-lg w-full" placeholder="Enter your last name" required>

                <div class="flex justify-between gap-2">
                    <div class="flex-1 flex flex-col gap-2">
                        <label for="municipality" class="text-purple-900 block text-sm font-medium">Municipality:</label>
                        <select id="municipality" name="municipality" class="px-4 py-2 border border-gray-300 rounded-lg w-full" required>
                            <option selected disabled>Select Municipality</option>
                            <!-- Populate dynamically -->
                        </select>
                    </div>
                    <div class="flex-1 flex flex-col gap-2">
                        <label for="baranggay" class="text-purple-900 block text-sm font-medium">Baranggay</label>
                        <select id="baranggay" name="baranggay" class="px-4 py-2 border border-gray-300 rounded-lg w-full" required>
                            <option selected disabled>Select Baranggay</option>
                        </select>
                    </div>
                </div>

                <label for="houseTitle" class="text-purple-900 block text-sm font-medium">House No. / Residence</label>
                <input type="text" id="houseTitle" name="houseTitle" class="px-4 py-2 border border-gray-300 rounded-lg w-full" placeholder="House Number / Residence" required>


                <label for="signUpEmail" class="text-purple-900 block text-sm font-medium">Email</label>
                <input type="email" id="signUpEmail" name="email" class="px-4 py-2 border border-gray-300 rounded-lg w-full" placeholder="Email@example.com" required>

                <label for="signUpPassword" class="text-purple-900 block text-sm font-medium">Password</label>
                <input type="password" id="signUpPassword" name="password" class="px-4 py-2 border border-gray-300 rounded-lg w-full" placeholder="Password" required>

                <label for="confirmSignUpPassword" class="text-purple-900 block text-sm font-medium">Confirm Password</label>
                <input type="password" id="confirmSignUpPassword" name="confirmPassword" class="px-4 py-2 border border-gray-300 rounded-lg w-full" placeholder="Confirm Password" required>

                <button type="submit" class="flex-1 px-8 py-2 border bg-purple-600 text-white rounded nav-link hover:bg-purple-900 hover:text-white active:bg-purple-500">
                    Sign Up
                </button>
            </form>
            <div class="mt-4 text-center">
                <p class="text-md">Already have an account? <span class="text-purple-900 font-bold underline">Log in</span>.</p>
            </div>
        </section>
    </section>
    <script>
        const municipalities = [
            "Basud",
            "Capalonga",
            "Daet",
            "Jose Panganiban",
            "Labo",
            "Mercedes",
            "Paracale",
            "San Lorenzo Ruiz",
            "San Vicente",
            "Santa Elena",
            "Talisay",
            "Vinzons",
        ];

        const baranggaysData = {
            Basud: [
                "Angas",
                "Bactas	",
                "Binatagan",
                "Caayunan",
                "Guinatungan",
                "Hinampacan",
                "Langa",
                "Laniton",
                "Lidong",
                "Mampili",
                "Mandazo",
                "Mangcamagong",
                "Manmuntay",
                "Mantugawe",
                "Matnog",
                "Mocong",
                "Oliva",
                "Pagsangahan",
                "Pinagwarasan",
                "Plaridel",
                "Poblacion 1",
                "Poblacion 2",
                "San Felipe",
                "San Jose",
                "San Pascual",
                "Taba-taba",
                "Tacad",
                "Taisan	",
            ],
            Capalonga: [
                "Alayao",
                "Binawangan",
                "Calabaca",
                "Camagsaan",
                "Catabaguangan",
                "Catioan",
                "Del Pilar",
                "Itok",
                "Lucbanan",
                "Mabini",
                "Mactang",
                "Mataque",
                "Old Camp",
                "Poblacion",
                "Magsaysay",
                "San Antonio",
                "San Isidro",
                "San Roque",
                "Tanauan",
                "Ubang",
                "Villa Aurora",
                "Villa Belen",
            ],
            Daet: [
                "Alawihao",
                "Awitan",
                "Bagasbas",
                "I",
                " II (Pasig)",
                "III (Bagumbayan)",
                "IV (Mantagbac)",
                " V (Pandan)",
                " VI (Centro Occidental)",
                " VII (Centro Oriental)",
                " VIII (Salcedo)",
                "Bibirao",
                "Borabod ",
                "Calasgasan",
                "Camambugan",
                "Cobangbang",
                "Dogongan",
                "Gahonon",
                "Gubat",
                "Lag-on",
                "Magang",
                "Mambalite",
                "Pamorangon",
                "Mancruz",
                "San Isidro ",
            ],
            "Jose Panganiban": [
                "Bagong Bayan",
                "Calero",
                "Dahican",
                "Dayhagan",
                "Larap",
                "Luklukan Norte",
                "Luklukan Sur",
                "Motherlode",
                "Nakalaya",
                "Osmeña",
                "Pag-Asa",
                "Parang",
                "Plaridel",
                "North Poblacion",
                "South Poblacion",
                "Salvacion",
                "San Isidro",
                "San Jose",
                "San Martin",
                "San Pedro",
                "San Rafael",
                "Santa Cruz",
                "Santa Elena",
                "Santa Milagrosa",
                "Santa Rosa Norte",
                "Santa Rosa Sur",
                "Tamisan",
            ],
            Labo: [
                "Anahaw",
                "Anameam",
                "Awitan",
                "Baay",
                "Bagacay",
                "Bagong Silang I",
                "Bagong Silang II",
                "Bagong Silang III",
                "Bakiad",
                "Bautista",
                "Bayabas",
                "Bayan-bayan",
                "Benit",
                "Bulhao",
                "Cabatuhan",
                "Cabusay",
                "Calabasa",
                "Canapawan",
                "Daguit",
                "Dalas",
                "Dumagmang",
                "Exciban",
                "Fundado",
                "Guinacutan",
                "Guisican",
                "Gumamela",
                "Iberica",
                "Kalamunding",
                "Lugui",
                "Mabilo I",
                "Mabilo II",
                "Macogon",
                "Mahawan-hawan",
                "Malangcao-Basud",
                "Malasugui",
                "Malatap",
                "Malaya",
                "Malibago",
                "Maot",
                "Masalong",
                "Matanlang",
                "Napaod",
                "Pag-Asa",
                "Pangpang",
                "Pinya",
                "San Antonio",
                "San Francisco",
                "Santa Cruz",
                "Submakin",
                "Talobatib",
                "Tigbinan",
                "Tulay Na Lupa",
            ],
            Mercedes: [
                "Apuao",
                "I (Poblacion)",
                "II (Poblacion)",
                "III (Poblacion)",
                "IV (Poblacion)",
                "V (Poblacion)",
                "VI (Poblacion)",
                "VII (Poblacion)",
                "Caringo",
                "Catandunganon",
                "Cayucyucan",
                "Colasi",
                "Del Rosario (Tagongtong)",
                "Gaboc",
                "Hamoraon",
                "Hinipaan",
                "Lalawigan",
                "Lanot",
                "Mambungalon",
                "Manguisoc",
                "Masalongsalong",
                "Matoogtoog",
                "Pambuhan",
                "Quinapaguian",
                "San Roque",
                "Tarum",
            ],
            Paracale: [
                "Awitan",
                "Bagumbayan",
                "Bakal",
                "Batobalani",
                "Calaburnay",
                "Capacuan",
                "Casalugan",
                "Dagang",
                "Dalnac",
                "Dancalan",
                "Gumaus",
                "Labnig",
                "Macolabo Island",
                "Malacbang",
                "Malaguit",
                "Mampungo",
                "Mangkasay",
                "Maybato",
                "Palanas",
                "Pinagbirayan Malaki",
                "Pinagbirayan Munti",
                " Poblacion Norte",
                "Poblacion Sur",
                "Tabas",
                "Talusan",
                "Tawig",
                "Tugos",
            ],
            "San Lorenzo Ruiz": [
                " Daculang Bolo",
                "Dagotdotan",
                "Langga",
                "Laniton",
                "Maisog",
                "Mampurog",
                "Manlimonsito",
                "Matacong (Poblacion)",
                "Salvacion",
                "San Antonio",
                "San Isidro",
                "San Ramon",
            ],
            "San Vicente": [
                "Asdum",
                "Cabanbanan",
                "Calabagas",
                "Fabrica",
                "Iraya Sur",
                "Man-Ogob",
                "Poblacion District I (Silangan/Baranggay1)",
                "Poblacion District II (Kanluran/Baranggay2)",
                "San Jose - formerly Iraya Norte",
            ],
            "Santa Elena": [
                "asiad",
                "Bulala",
                "Don Tomas",
                "Guitol",
                "Kabuluan",
                "Kagtalaba",
                "Maulawin",
                "Patag Ibaba",
                "Patag Ilaya",
                "Plaridel",
                "Polungguitguit",
                "Rizal",
                "Salvacion",
                "San Lorenzo",
                "San Pedro",
                "San Vicente",
                "Santa Elena (Poblacion)",
                "Tabugon",
                "Villa San Isidro",
            ],
            Talisay: [
                "Binanuaan",
                "Caawigan",
                "Cahabaan",
                "Calintaan",
                "Del Carmen",
                "Gabon",
                "Itomang",
                "Poblacion",
                "San Francisco",
                "San Isidro",
                "San Jose",
                "San Nicolas",
                "Santa Cruz",
                "Santa Elena",
                "Santo Niño",
            ],
            Vinzons: [
                "   Aguit-It ",
                "Banocboc",
                "Cagbalogo",
                "Calangcawan Norte",
                "Calangcawan Sur",
                "Guinacutan",
                "Mangcayo",
                "Mangcawayan",
                "Manlucugan",
                "Matango",
                "Napilihan",
                "Pinagtigasan ",
                "I (Poblacion)",
                "II (Poblacion)",
                "III (Poblacion)",
                "Sabang",
                "Santo Domingo",
                "Singi ",
                "Sula ",
            ],
        };

        // Populate the Municipality Dropdown
        const municipalitySelect = document.getElementById("municipality");

        municipalities.forEach((municipality) => {
            const option = document.createElement("option");
            option.value = municipality;
            option.textContent = municipality;
            municipalitySelect.appendChild(option);
        });
        // Update Baranggay Dropdown
        function updateBaranggays() {
            const municipality = document.getElementById("municipality").value;
            const baranggaySelect = document.getElementById("baranggay");

            // Clear existing Baranggay options
            baranggaySelect.innerHTML = "<option selected disabled>Select Baranggay</option>";

            if (municipality && baranggaysData[municipality]) {
                baranggaysData[municipality].forEach((baranggay) => {
                    const option = document.createElement("option");
                    option.value = baranggay;
                    option.textContent = baranggay;
                    baranggaySelect.appendChild(option);
                });
            }
        }

        // Attach event listener to Municipality dropdown
        municipalitySelect.addEventListener("change", updateBaranggays);

        const toggleSignUpPasswordButton = document.getElementById(
            "toggleSignUpPassword"
        );
        const toggleSignUpConfirmPasswordButton = document.getElementById(
            "toggleSignUpConfirmPassword"
        );

        toggleSignUpPasswordButton.addEventListener("click", () => {
            if (signUpPasswordInput.type === "password") {
                signUpPasswordInput.type = "text";
                toggleSignUpPasswordButton.innerHTML =
                    '<i class="fa-solid fa-eye-slash"></i>';
            } else {
                signUpPasswordInput.type = "password";
                toggleSignUpPasswordButton.innerHTML = '<i class="fa-solid fa-eye"></i>';
            }
        });
        toggleSignUpConfirmPasswordButton.addEventListener("click", () => {
            if (signUpConfirmPasswordInput.type === "password") {
                signUpConfirmPasswordInput.type = "text";
                toggleSignUpConfirmPasswordButton.innerHTML =
                    '<i class="fa-solid fa-eye-slash"></i>';
            } else {
                signUpConfirmPasswordInput.type = "password";
                toggleSignUpConfirmPasswordButton.innerHTML =
                    '<i class="fa-solid fa-eye"></i>';
            }
        });
    </script>
</body>

</html>