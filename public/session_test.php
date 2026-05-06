<?php
session_start();
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = time();
    echo "Session initialized. Reload to verify.";
} else {
    echo "Session persisted: " . $_SESSION['test'];
}
