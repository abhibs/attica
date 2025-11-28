<?php
// index.php

// Serve an HTML page that requests mic access, then redirects to login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/png" href="images/favicon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attica Analytics</title>
  <script>
    // As soon as the page loads, request microphone access
    window.addEventListener('DOMContentLoaded', () => {
      // Check if the browser supports getUserMedia
      if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ audio: true })
          .then(stream => {
            // Stop all tracks immediately—this was just to trigger the permission prompt
            stream.getTracks().forEach(track => track.stop());
            // Redirect to login.php
            window.location.href = 'login.php';
          })
          .catch(err => {
            // If access is denied or an error occurs, still redirect
            console.warn('Microphone access was denied or an error occurred:', err);
            window.location.href = 'login.php';
          });
      } else {
        // Fallback: if getUserMedia isn't supported, just redirect
        window.location.href = 'login.php';
      }
    });
  </script>
</head>
<body>
  <p>Requesting microphone access… If nothing happens, <a href="login.php">click here to continue</a>.</p>
</body>
</html>