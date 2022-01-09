<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
    <title>whereIsTheISS</title>
    <style>
        #ISSmap {
            height: 500px;
            width: 500px;
        }
    </style>
</head>

<body>

    <h2>WHERE THE ISS AT?</h2>
    <p>Well lets have a look</p>
    <p>latitude: <span id="lat"></span>°</p>
    <p>longitude: <span id="lon"></span>°</p>
    <br>
    <p><b>Wait lemme show:</b></p>
    <div id="ISSmap"></div>

    <script>
        //map and tiles
        const mymap = L.map('ISSmap').setView([0, 0], 1);
        const tileUrl = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
        const tiles = L.tileLayer(tileUrl);
        tiles.addTo(mymap);

        //custom icon
        const issIcon = L.icon({
            iconUrl: 'iss200.png',
            iconSize: [50, 32],
            iconAnchor: [25, 16],
        });
        const marker = L.marker([0, 0], {
            icon: issIcon
        }).addTo(mymap);

        let firstTime = true;

        async function getISS() {
            const response = await fetch("https://api.wheretheiss.at/v1/satellites/25544");
            const data = await response.json();
            console.log(data.latitude);
            console.log(data.longitude);
            const {
                latitude,
                longitude
            } = data;
            marker.setLatLng([latitude, longitude]);
            if (firstTime) {
                mymap.setView([latitude, longitude], 2);
                firstTime = false;
            }
            document.getElementById("lat").textContent = latitude.toFixed(2);
            document.getElementById("lon").textContent = longitude.toFixed(2);
        }

        getISS();
        setInterval(getISS, 1000);
    </script>
</body>

</html>