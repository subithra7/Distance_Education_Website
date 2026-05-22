```php id="u88upq"
<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Save all form data
    $_SESSION['step2_data'] = $_POST;

    // Go to preview page
    header("Location: preview.php");
    exit;
}
?>
```
