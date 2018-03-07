<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/db.php";

class html_generator
{
    private $_titles;
    private $_mov_db;

    function __construct()
    {
        $this->_titles = array("Letter", "Title", "Folder", "Year");
        $this->_mov_db = new mov_db();
    }

    public function data_table($sort='none')
    {
        $html = $this->_generate_table_header();
        $html .= $this->_generate_table_data();
        return $html . "</table>";
    }

    private function _generate_table_header()
    {
        $header_string = "<table>\r\n<tr class=\"tableheader\">";
        foreach ($this->_titles as $title)
        {
            $t = "\r\n<th><a href=\"index.php?sort=" . strtolower($title) .
                "\">{$title}</a></th>";
            $header_string .= $t;
        }
        return $header_string . "\r\n</tr>";
    }

    private function _generate_table_row($col_data)
    {
        $ret = "\r\n<tr>";
        foreach ($col_data as $data)
        {
            $ret .= "\r\n<td>{$data}</td>";
        }
        return $ret . "\r\n</tr>";
    }

    private function _generate_table_data()
    {
        $ret = "";
        foreach ($this->_mov_db->keys() as $mov)
        {
            $ret .= $this->_generate_table_row(array(
                $this->_mov_db->data($mov, "letter"),
                $this->_mov_db->omdb_data($mov, "Title"),
                $this->_mov_db->data($mov, "folder"),
                $this->_mov_db->omdb_data($mov, "Year"),)
            );
        }
        return $ret;
    }
}

?>
