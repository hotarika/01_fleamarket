<?php
require('f-function.php');
session_destroy();
debug('ログアウト情報');
debug(print_r($_SESSION, true));

header("Location:b-login.php");
