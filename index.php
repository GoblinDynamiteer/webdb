<!DOCTYPE html>
<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/generator.php";
$hmtl_gen = new html_generator();
 ?>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/css?family=Mina" rel="stylesheet">
    </head>
    <body>
        <?php echo $hmtl_gen->data_table(); ?>
    </body>
</html>
