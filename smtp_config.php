<?php

if (!defined("BREVO_API_KEY")) {
    define("BREVO_API_KEY", getenv("BREVO_API_KEY"));
}

if (!defined("SENDER_EMAIL")) {
    define("SENDER_EMAIL", "campusconnect.project2026@gmail.com");
}

if (!defined("SENDER_NAME")) {
    define("SENDER_NAME", "CampusConnect");
}

if (!defined("BASE_URL")) {
    define("BASE_URL", "https://campusconnect-ee48.onrender.com");
}
?>
