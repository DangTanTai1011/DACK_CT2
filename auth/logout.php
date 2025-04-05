<?php
session_start();
session_unset();
session_destroy();
header("Location: /Nhom8_DACK/index.php");
exit();
