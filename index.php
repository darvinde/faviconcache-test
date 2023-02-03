<?php
/**
 * Favicon Cache testing
 *
 * @author     Darvin Schlüter <mail@darvin.de>
 */

/*Includes the Router.php class by SteamPixel*/
/*https://github.com/steampixel/simplePHPRouter*/
require $_SERVER["DOCUMENT_ROOT"].'/include/classes/Router.php';
$router = new Steampixel\Route();

/*Writes a line to the logfile*/
function logcall(){
  $log = date('Y-m-d H:i:s') . " - " . $_SERVER['REQUEST_URI'] . " - " . $_SERVER['REMOTE_ADDR'];
  file_put_contents('./logs.txt', $log."\r\n", FILE_APPEND);
}

/*Outputting this will prevent the browser to request the favicon.ico*/
function prevent_favicon_request(){
  ?><link rel="icon" href="data:,"><?php
}

/*Sends a cache control max-age header*/
function enablecache($seconds){
  header("Cache-Control: max-age=$seconds");
}

/*Triggered when the path /logs is opened*/
$router->add("/logs", function() {
  prevent_favicon_request();

  ?>
  <div id="logoutput"></div>
  <script>
  /* This script just fetched the logs.txt every 1 second*/
    let logoutputdom = document.getElementById("logoutput");
    logoutputdom.innerHTML = "Logs loading...";
    let cachepreventstring = Date.now();
    function fetchLogs(){
      fetch('logs.txt?'+cachepreventstring)
        .then(response => response.text())
        .then(logs => {
          logoutputdom.innerHTML = "";
          const rows = logs.split('\n').reverse();
          for (row of rows) {
            logoutputdom.innerHTML += row + "<br>";
          }
        });
    }
    setInterval(fetchLogs, 1000);
  </script>

  <?php
});

/*Triggered when the path / is opened*/
$router->add("/", function() {
  prevent_favicon_request();
  echo "<a href='/logs'>View logs</a><br>";
  echo "Open logs in a different tab and then open random links like /abc123.";
  echo "Cache control max-age is set to 3 minutes.";
});

/*Triggered when any path with ends with /favicon.ico is opened
This outputs a favicon.ico file*/
$router->add("/(.*)/favicon.ico", function($path) {
  logcall();
  enablecache(60*3);

  $file = $_SERVER["DOCUMENT_ROOT"]."/include/img/favicon.ico";
  $type = 'image/x-icon';
  header('Content-Type:'.$type);
  header('Content-Length: ' . filesize($file));
  readfile($file);

});

/*Triggered when any path other path is opened*/
$router->add("/(.*)", function($path) {
  logcall();

  echo "<html><head>";
  echo "<link rel='shortcut icon' href='/$path/favicon.ico' type='image/x-icon'/>";
  echo "</head><body>";
  echo $_SERVER['REQUEST_URI'];
  echo "</body>";
});


$router->run();

?>
