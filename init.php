<?php
    if (!isset($db)) {
        $db = require_once('config/db.php');
    }

    return mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
?>
