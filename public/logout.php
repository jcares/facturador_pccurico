<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;

Auth::logout();
