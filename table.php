<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/db.php";

function get_table_titles()
{
    return array("Letter", "Title", "Folder", "Year");
}

function generate_table($sort='none')
{
    $table_string = generate_table_header();
    $table_string .= generate_table_data();
    return $table_string . "</table>";
}

function generate_table_header()
{
    $table_header_titles = get_table_titles();
    $header_string = "<table>\r\n<tr class=\"tableheader\">";
    foreach ($table_header_titles as $title)
    {
        $t = "\r\n<th><a href=\"index.php?sort=" . strtolower($title) .
            "\">{$title}</a></th>";
        $header_string .= $t;
    }
    return $header_string . "\r\n</tr>";
}

function generate_table_row($col_data)
{
    $ret = "\r\n<tr>";
    foreach ($col_data as $data)
    {
        $ret .= "\r\n<td>{$data}</td>";
    }
    return $ret . "\r\n</tr>";
}

function generate_table_data()
{
    $db = new mov_db();
    $ret = "";
    foreach ($db->keys() as $mov)
    {
        $ret .= generate_table_row(array(
            $db->data($mov, "letter"),
            $db->omdb_data($mov, "Title"),
            $db->data($mov, "folder"),
            $db->omdb_data($mov, "Year"),)
        );
    }
    return $ret;
}
?>
