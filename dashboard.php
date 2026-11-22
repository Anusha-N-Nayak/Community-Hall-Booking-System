<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login/login.html");
    exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Community Hall Dashboard</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
        }

        /* Header */

        header {
            background: #142d4e;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h2 {
            font-size: 23px;
        }

        /* Navigation */

        nav {
            background: white;
            padding: 18px;
            display: flex;
            justify-content: center;
            gap: 35px;
            flex-wrap: wrap;
            box-shadow: 0 2px 5px #ddd;
        }

        nav a {
            text-decoration: none;
            color: #142d4e;
            font-weight: bold;
        }

        nav a:hover {
            color: #e67e22;
        }

        /* Welcome Section */

        .welcome {
            margin: 30px 40px;
            padding: 35px;
            background: #1e6091;
            color: white;
            border-radius: 10px;
        }

        .welcome h1 {
            margin-bottom: 12px;
        }

        /* Search Section */

        .search-section {
            text-align: center;
            margin: 35px;
        }

        .search-section h2 {
            margin-bottom: 20px;
            color: #142d4e;
        }

        .search-section input {
            width: 60%;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            cursor: pointer;
        }

        .search-btn {
            padding: 14px 25px;
            background: #e67e22;
            color: white;
            border: none;
            border-radius: 6px;
        }

        /* Hall Section */

        .hall-section {
            padding: 20px 40px;
        }

        .hall-section h2 {
            text-align: center;
            color: #142d4e;
            margin-bottom: 30px;
        }

        .hall-container {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        .hall-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 12px #ddd;
            transition: 0.3s;
        }

        .hall-card:hover {
            transform: translateY(-5px);
        }

        .hall-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .hall-info {
            padding: 20px;
        }

        .hall-info h3 {
            color: #142d4e;
            margin-bottom: 12px;
        }

        .hall-info p {
            margin-bottom: 10px;
            color: #555;
            line-height: 1.5;
        }

        .details-btn {
            background: #1e6091;
            color: white;
            padding: 11px 20px;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .details-btn:hover {
            background: #142d4e;
        }

        /* View All */

        .view-all {
            text-align: center;
            margin: 35px;
        }

        .view-all button {
            background: #e67e22;
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 6px;
            font-size: 16px;
        }

        /* Footer */

        footer {
            background: #142d4e;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 30px;
        }

        @media(max-width: 600px) {

            header {
                padding: 18px;
            }

            .welcome {
                margin: 20px;
            }

            .search-section input {
                width: 100%;
                margin-bottom: 10px;
            }

            .hall-section {
                padding: 15px;
            }

        }
        .search-suggestions {
    display: none;
    max-width: 535px;
    width: 90%;
    margin: 5px auto 0;
    background: #ffffff;
    border: 1px solid #dce2ec;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
    text-align: left;
    overflow: hidden;
    z-index: 1000;
}

.search-suggestions div {
    padding: 13px 18px;
    cursor: pointer;
    color: #26344d;
    font-size: 15px;
    text-align: left;
    border-bottom: 1px solid #eeeeee;
    transition: background 0.2s;
}

.search-suggestions div:last-child {
    border-bottom: none;
}

.search-suggestions div:hover {
    background: #eef2ff;
    color: #243b70;
}

</style>


</head>

<body>

    <!-- Header -->

    <header>

        <h2>Community Hall Booking</h2>

        <a href="logout.php" style="color:white;">
            Logout
        </a>

    </header>


    <!-- Navigation -->

    <nav>

        <a href="index.html">Home</a>

        <a href="my_bookings.php">My Bookings</a>

        <a href="feedback.html">Feedback</a>

        <a href="notifications.php">Notifications</a>

        <a href="profile.php">My Profile</a>

    </nav>


    <!-- Welcome -->

    <section class="welcome">

        <h1>Welcome to Your Dashboard!</h1>

        <p>
            Find the perfect community hall for your
            weddings, celebrations and special occasions.
        </p>

    </section>


    <!-- Search -->

    <section class="search-section">

        <h2>Find Your Perfect Hall</h2>

        <input
    type="text"
    id="searchInput"
    autocomplete="off"
    placeholder="Search community hall by name..."
    onkeydown="if(event.key === 'Enter') {
        event.preventDefault();
        searchHalls();
    }">

<div id="searchSuggestions" class="search-suggestions"></div>


    </section>


    <!-- Available Halls -->

    <section class="hall-section">

        <h2>Available Community Halls</h2>

        <div class="hall-container" id="hallContainer">


           <?php include "customer_halls.php"; ?>
           </div>
           <p id="noResult" style="display:none; text-align:center; color:red; margin:20px;">
    No matching halls found!
</p>

    </section>


   

    <!-- Footer -->

    <footer>

        <p>
            © 2026 Community Hall Booking and Management System
        </p>

    </footer>


    <!-- JavaScript -->

    <script>

     
    function searchHalls() {
    let input = document.getElementById("searchInput").value.toLowerCase().trim();

    let cards = document.querySelectorAll("#hallContainer .hall-card");
    let found = 0;

    let words = input.split(/\s+/).filter(word => word !== "");

    cards.forEach(function(card) {
        let name = card.querySelector("h3").innerText.toLowerCase();

        let match = input === "" || words.every(word => name.includes(word));

        card.style.display = match ? "" : "none";

        if (match) {
            found++;
        }
    });

    let noResult = document.getElementById("noResult");

    if (noResult) {
        noResult.style.display = found === 0 ? "block" : "none";
    }

    
}
const searchInput = document.getElementById("searchInput");
const suggestions = document.getElementById("searchSuggestions");

function showHallSuggestions() {
    let input = searchInput.value.toLowerCase().trim();

    let cards = document.querySelectorAll("#hallContainer .hall-card");

    suggestions.innerHTML = "";

    let shownNames = new Set();

    cards.forEach(function(card) {
        let hallName = card.querySelector("h3").innerText.trim();
        let uniqueName = hallName.toLowerCase();

        if (uniqueName.includes(input) && !shownNames.has(uniqueName)) {
            shownNames.add(uniqueName);

            let item = document.createElement("div");
            item.textContent = hallName;

            item.onclick = function() {
                searchInput.value = hallName;
                suggestions.style.display = "none";
                searchHalls();
                viewDetails(hallName);
            };

            suggestions.appendChild(item);
        }
    });

    suggestions.style.display =
        suggestions.children.length > 0 ? "block" : "none";

    searchHalls();
}
function showHallSuggestions() {
    let input = searchInput.value.toLowerCase().trim();

    let cards = document.querySelectorAll("#hallContainer .hall-card");

    suggestions.innerHTML = "";

    let shownNames = new Set();

    cards.forEach(function(card) {
        let hallName = card.querySelector("h3").innerText.trim();
        let uniqueName = hallName.toLowerCase();

        if (uniqueName.includes(input) && !shownNames.has(uniqueName)) {
            shownNames.add(uniqueName);

            let item = document.createElement("div");
            item.textContent = hallName;

            item.onclick = function() {
                searchInput.value = hallName;
                suggestions.style.display = "none";
                searchHalls();
                viewDetails(hallName);
            };

            suggestions.appendChild(item);
        }
    });

    suggestions.style.display =
        suggestions.children.length > 0 ? "block" : "none";

    searchHalls();
}

searchInput.addEventListener("focus", showHallSuggestions);
searchInput.addEventListener("input", showHallSuggestions);
function viewDetails(hallName) {
    window.location.href =
        "halldetails.php?hall=" + encodeURIComponent(hallName);
}

        function viewAllHalls() {

            window.location.href = "allhalls.html";

        }
        </script>
    <script>
    window.addEventListener("pageshow", function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>

</body>

</html>