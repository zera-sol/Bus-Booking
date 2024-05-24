<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="./css/booking.css">
</head>
<body>
<div class="navbar">
    <div class="logo">Travel Express</div>
    <div class="nav-links">
        <a href="#">Draft</a>
        <a href="#">My Booking</a>
        <a href="#">Help</a>
        <a href="#">Contact Us</a>
    </div>
    <div class="user-profile">
       ZE
    </div>
</div>

<div class="content">
    <h1>Book a trip</h1>
    <div class="trip-form">
        <form action="" method="">
            <input type="text" name="user-name" placeholder="User name" required>
            <div id="search-trip">
                <div>
                    <label for="source">From:</label>
                    <select name="source" id="" required>
                        <option value="0">Select Source</option>
                        <option value="1">Addis Ababa</option>
                        <option value="2">Hossana</option>
                        <option value="3">Bahirdar</option>
                        <option value="4">Gonder</option>
                        <option value="5">Arbaminch</option>
                        <option value="6">Diredwa</option>
                    </select>
                </div>
                <div>
                    <label for="destination">To:</label>
                    <select name="destination" id="" required>
                        <option value="0">Select Destination</option>
                        <option value="1">Addis Ababa</option>
                        <option value="2">Hossana</option>
                        <option value="3">Bahirdar</option>
                        <option value="4">Gonder</option>
                        <option value="5">Arbaminch</option>
                        <option value="6">Diredwa</option>
                    </select>
                </div>
            </div>
            <input type="date" placeholder="Departure date" required>
            <button>Choose your favorite seats</button>
            <!-- call the function checkRoute when the next button is clicked -->
            <button type="button" onclick="checkRoute()">Book now</button>
        </form>
    </div>
    <div class="destinations">
        <h2>Popular destinations</h2>
        <div class="destination-grid">
            <div class="destination">
                <img src="./images/aa.jpg" alt="Addis Ababa">
                <p>Addis Ababa</p>
            </div>
            <div class="destination">
                <img src="./images/bahirdar.jpg" alt="Bahirdar">
                <p>Bahirdar</p>
            </div>
            <div class="destination">
                <img src="./images/arbaminch.jpg" alt="Arbaminch">
                <p>Arbaminch</p>
            </div>
            <div class="destination">
                <img src="./images/gonder.jpg" alt="Gonder">
                <p>Gonder</p>
            </div>
            <div class="destination">
                <img src="./images/diredwa.jpg" alt="diredwa">
                <p>Diredwa</p>
            </div>
            <div class="destination">
                <img src="./images/hossana.jpg" alt="Hossana">
                <p>Hossana</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Write a javasctipt code that checkes whether one of the value among the two select section must be 1 else alert there is no route in from the source to destination and disable the book now button and the select sections should not be 0
    const source = document.querySelector('select[name="source"]');
    const destination = document.querySelector('select[name="destination"]');
    const bookNow = document.querySelector('button');
    function checkRoute() {
        if (source.value === '0' || destination.value === '0') {
            alert('Please select source and destination');
            bookNow.disabled = true;
        } else if(source.value !== 1 || destination.value !== 1){
            alert('There is no route from the source to destination');
            bookNow.disabled = true;
        }
        else if (source.value === destination.value) {
            alert('Source and destination cannot be the same');
            bookNow.disabled = true;
        } else {
            bookNow.disabled = false;
        }
    }
</script>
</body>
</html>