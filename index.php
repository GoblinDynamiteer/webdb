<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/css?family=Mina" rel="stylesheet">
    </head>
    <body>
        <?php
        require_once __DIR__."/config.php";
        require_once SITE_ROOT."/table.php";
        echo generate_table();
        ?>
    </body>
</html>
