<!DOCTYPE html>
<?php
    session_start();
    if(isset($_GET['db']))
    {
        $_SESSION["db"] = $_GET['db'];
    }
    require_once __DIR__."/config.php";
    require_once SITE_ROOT."/generator.php";

    if(!isset($_SESSION["db"]) || $_SESSION["db"] == "mov")
    {
        $hmtl_gen = new html_mov_generator();
    }
    else if($_SESSION["db"] == "tv")
    {
        $hmtl_gen = new html_tv_generator(
            isset($_GET['sort']) ? $_GET['sort'] : "show",
            isset($_GET['order']) ? $_GET['order'] : "az",
            isset($_GET['limit']) ? $_GET['limit'] : "25",
            isset($_GET['page']) ? $_GET['page'] : "0",
            isset($_GET['extend_info']) ? $_GET['extend_info'] : "",
            isset($_GET['search']) ? $_GET['search'] : "");
    }

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
           <h1><a href="index.php?db=mov">MovieDb</a> |
           <a href="index.php?db=tv">TVDb</a></h1>
        </header>
        <nav>
            <ul class="topnav">
                <li><a href="index.php?sort=title&limit=25">All</a><br></li>
                <li><a href="index.php?sort=added&limit=25&order=za">Newest</a><br></li>
                <li>
                    <form name="form" action="" method="get">
                        <input type="text" name="search" value="">
                        <button>Search</button><br>
                    </form>
                </li>
            </ul>
        </nav>
        <table>
            <tr>
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
