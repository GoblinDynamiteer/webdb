<!DOCTYPE html>
<?php
    require_once __DIR__."/config.php";
    require_once SITE_ROOT."/generator.php";
    $hmtl_gen = new html_generator(
        isset($_GET['sort']) ? $_GET['sort'] : "",
        isset($_GET['order']) ? $_GET['order'] : "asc",
        isset($_GET['limit']) ? $_GET['limit'] : "25",
        isset($_GET['page']) ? $_GET['page'] : "0",
        isset($_GET['info']) ? $_GET['info'] : "");
 ?>
<html>
    <head>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/css?family=Mina" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
        <title><?php echo $hmtl_gen->title(); ?></title>
    </head>
    <body>
        <header>
           <h1>MovieDb</h1>
        </header>
        <nav>
            <ul class="topnav">
                <li><a href="index.php?sort=title&limit=25">All</a><br></li>
                <li><a href="index.php?sort=added&limit=25">Newest</a><br></li>
                <li><a href="index.php?sort=added&limit=25">Year: 2018</a><br></li>
            </ul>
        </nav>
        <table>
            <tr class="tableheader">
                <?php echo $hmtl_gen->table_header(); ?>
            </tr>
            <?php
                echo $hmtl_gen->table_data(); ?>
        </table>
        <footer>
            <?php echo $hmtl_gen->page_nav_footer(); ?>
        </footer>
    </body>
</html>
