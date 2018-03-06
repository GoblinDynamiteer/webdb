<!DOCTYPE html>
<html>
    <head>
    </head>
    <body>
        <?php
        require_once __DIR__."/config.php";
        require_once SITE_ROOT."/table.php";
        echo generate_table();
        generate_table_data();
        ?>
    </body>
</html>
