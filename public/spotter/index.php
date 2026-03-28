<?php
// Forward /spotter directory requests to Laravel's front controller
// Required because PHP's built-in dev server serves directories before routing
require __DIR__.'/../index.php';
