<?php
// Brevo API Configuration (NO SMTP anymore)

// IMPORTANT: Set this in Render Environment Variables
define("BREVO_API_KEY", getenv("BREVO_API_KEY"));

// Your verified sender email (must be verified in Brevo)
define("SENDER_EMAIL", "campusconnect.project2026@gmail.com");
define("SENDER_NAME", "CampusConnect");

// Base URL of your project (used in links)
define("BASE_URL", "https://campusconnect-ee48.onrender.com");
?>
