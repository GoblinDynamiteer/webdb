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
        <title><?php echo $hmtl_gen->title(); ?></title>
    </head>
    <body>
        <a href="index.php?sort=added&limit=25">Newest 25</a><br>
        <table>
            <tr class="tableheader">
                <?php echo $hmtl_gen->table_header(); ?>
            </tr>
            <?php
                $sort = isset($_GET['sort']) ? $_GET['sort'] : "";
                $limit = isset($_GET['limit']) ? $_GET['limit'] : "";
                echo $hmtl_gen->table_data($sort, $limit); ?>
        </table>
    </body>
</html>
