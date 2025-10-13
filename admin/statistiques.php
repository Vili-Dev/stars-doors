<?php
session_start(); // نفتح الدفتر / On ouvre le cahier

// نقرأو من "الدفتر"
// On lit depuis le "cahier"

echo $_SESSION['user_id'];    // يعرض : 5
echo $_SESSION['user_name'];  // يعرض : Ahmed
// PHP se souvient ! Pas besoin de redemander
session config
session database
session auth

loggedin
is admin