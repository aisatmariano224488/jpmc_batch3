<?php
// session_start();
if (isset($_POST['tab'])) {
    $_SESSION['current_tab'] = $_POST['tab'];
}
?> 