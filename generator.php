<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/db.php";

class html_generator
{
    private $_titles;
    private $_mov_db;
    private $asc_desc_sort;

    function __construct()
    {
        $this->_titles = array("Title", "Year", "Letter", "Folder");
        $this->_mov_db = new mov_db();
        $this->asc_desc_sort = true;
    }

    public function title()
    {
        return "MovDb";
    }

    public function table_header()
    {
        $header_string = "";
        $order = $this->asc_desc_sort ? "asc" : "desc";
        $this->asc_desc_sort = !$this->asc_desc_sort;

        foreach ($this->_titles as $title)
        {
            $t = "\r\n<th><a href=\"index.php?sort=" . strtolower($title) .
                "\"&order=\"" . $order .
                "\">{$title}</a></th>";
            $header_string .= $t;
        }


        return $header_string;
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

    public function table_data($sort)
    {
        echo "sort:" . $sort;
        $this->_mov_db->sort_by($sort);
        $ret = "";
        foreach ($this->_mov_db->keys() as $mov)
        {
            $ret .= $this->_generate_table_row(array(
                $this->_mov_db->omdb_data($mov, "Title"),
                $this->_mov_db->omdb_data($mov, "Year"),
                $this->_mov_db->data($mov, "letter"),
                $this->_mov_db->data($mov, "folder")
            ));
        }
        return $ret;
    }
}

?>