<?php
$apiKey = "7f010e53a6f68679a8186d8a987a8992"; 
$weatherData = null;
$forecastData = null;

if (isset($_GET['city'])) {
    $city = urlencode($_GET['city']);

    $geoUrl = "http://api.openweathermap.org/geo/1.0/direct?q={$city}&limit=1&appid={$apiKey}";
    $geoResponse = @file_get_contents($geoUrl);
    $geoData = json_decode($geoResponse, true);

    if (!empty($geoData)) {
        $lat = $geoData[0]['lat'];
        $lon = $geoData[0]['lon'];

        $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";
        $weatherResponse = @file_get_contents($weatherUrl);
        if ($weatherResponse !== false) {
            $weatherData = json_decode($weatherResponse, true);
        }

        $forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";
        $forecastResponse = @file_get_contents($forecastUrl);
        if ($forecastResponse !== false) {
            $forecastData = json_decode($forecastResponse, true);
        }
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Weather Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f8ff; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .forecast { display: flex; gap: 10px; flex-wrap: wrap; }
        .forecast-day { flex: 1 0 150px; background: #e0f7fa; padding: 10px; border-radius: 8px; text-align: center; }
    </style>
</head>
<body>

<h1>🌦️ Weather Dashboard</h1>

<form method="get">
    <input type="text" name="city" placeholder="Enter city name" required>
    <button type="submit">Search</button>
</form>

<?php if ($weatherData && $weatherData['cod'] == 200): ?>
    <div class="card">
        <h2>Current Weather in <?= htmlspecialchars($_GET['city']) ?></h2>
        <p><strong>Temperature:</strong> <?= $weatherData['main']['temp'] ?> °C</p>
        <p><strong>Condition:</strong> <?= $weatherData['weather'][0]['description'] ?></p>
        <p><strong>Humidity:</strong> <?= $weatherData['main']['humidity'] ?>%</p>
        <p><strong>Wind Speed:</strong> <?= $weatherData['wind']['speed'] ?> m/s</p>
    </div>

    <?php if ($forecastData && $forecastData['cod'] == "200"): ?>
        <div class="card">
            <h2>5-Day Forecast</h2>
            <div class="forecast">
                <?php
                $shownDates = [];
                foreach ($forecastData['list'] as $forecast) {
                    $date = date("Y-m-d", strtotime($forecast['dt_txt']));
                    $time = date("H:i:s", strtotime($forecast['dt_txt']));
                    if ($time === "12:00:00" && !in_array($date, $shownDates)) {
                        $shownDates[] = $date;
                        ?>
                        <div class="forecast-day">
                            <strong><?= date("D, M j", strtotime($forecast['dt_txt'])) ?></strong><br>
                            <img src="http://openweathermap.org/img/wn/<?= $forecast['weather'][0]['icon'] ?>.png"><br>
                            <?= $forecast['main']['temp'] ?> °C<br>
                            <?= $forecast['weather'][0]['main'] ?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    <?php endif; ?>

<?php elseif (isset($_GET['city'])): ?>
    <p style="color: red;">City not found or failed to fetch data.</p>
<?php endif; ?>

</body>
</html>
