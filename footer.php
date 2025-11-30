<!-- footer.php -->
<?php
if (!isset($lang)) {
    session_start();
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = 'en';
    }
    $lang = include "lang/{$_SESSION['lang']}.php";
}
?>
<footer style="background:#2c3e50; color:white; padding:15px 20px; 
               position:fixed; bottom:0; left:0; width:100%; 
               text-align:center; border-top:3px solid #16a085; 
               z-index:1000;">
    <p style="margin:0; font-size:14px;">
        &copy; <?php echo date("Y"); ?> Finance Tracker System. <?php echo $lang['footer_rights']; ?><br>
        <span style="font-size:12px; color:#bdc3c7;">
            <?php echo $lang['footer_designed']; ?> <strong>WILFREDY</strong>
            <?php echo $lang['footer_Phone_number']; ?> <strong>+255747329123</strong>
        </span>
    </p>
</footer>