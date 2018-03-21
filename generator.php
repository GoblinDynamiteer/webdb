<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/db.php";

class html_generator
{
    private $_titles;
    private $_mov_db;
    private $show_imdb;
    private $sort_order;
    private $current_page;
    private $page_limit;
    private $sort_by;

    function __construct(
        $sort_by, $sort_order, $page_limit, $current_page, $show_imdb)
    {
        $this->_titles = array(
            "Title", "Year", "Letter", "Folder", "Imdb", "Added");
        $this->_mov_db = new mov_db();
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order == "asc" ? "desc" : "asc";
        $this->show_imdb = $show_imdb;
        $this->current_page = $current_page == "" ? 0 : intval($current_page);
        $this->page_limit = $page_limit == "" ? 0 : intval($page_limit);
    }

    public function title()
    {
        return "MovDb";
    }

    public function table_header()
    {
        $header_string = "";
        foreach ($this->_titles as $title)
        {
            $t = "\r\n<th><a href=\"index.php?sort=" . strtolower($title) .
                "&order={$this->sort_order}\">{$title}</a></th>";
            $header_string .= $t;
        }


        return $header_string;
    }

    public function page_nav_footer()
    {
        $footer_string = "";
        $var_string = ($this->page_limit ? "&limit=" . $this->page_limit : "");
        $var_string = ($this->sort_by ? "&sort=" . $this->sort_by : "");

        $prev = $this->current_page == 0 ? 0 : ($this->current_page) - 1;
        $footer_string = "<a href=\"index.php?&page=" .
            (string)$prev . $var_string. "\">PREV</a>";
        $footer_string .= " PAGE " . (string)($this->current_page) . " ";
        $footer_string .= "<a href=\"index.php?page=" .
            (string)(1 + $this->current_page) .
            $var_string ."\">NEXT</a>";
        return $footer_string;
    }

    private function _generate_table_row($col_data)
    {
        $ret = "\r\n<tr>";
        foreach ($col_data as $data)
        {
            $imdb_pattern = "/^tt[0-9]{1,}$/";
            if(preg_match($imdb_pattern, $data))
            {
                $link = "http://www.imdb.com/title/" . $data;
                $ret .= "\r\n<td><a href=\"" . $link . "\">" . $data . "</a></td>";
            }
            else
            {
                $ret .= "\r\n<td>{$data}</td>";
            }
        }
        return $ret . "\r\n</tr>";
    }

    public function table_data()
    {
        $count = 0;
        $start_at = $this->current_page * $this->page_limit;
        $this->_mov_db->sort_by($this->sort_by, $this->sort_order);

        $ret = "";
        foreach ($this->_mov_db->keys() as $mov)
        {
            if($count >= $start_at)
            {
                $date = DateTime::createFromFormat('j M Y',
                    $this->_mov_db->data($mov, "date_scanned"));
                $ret .= $this->_generate_table_row(array(
                    $this->_mov_db->omdb_data($mov, "Title"),
                    $this->_mov_db->omdb_data($mov, "Year"),
                    $this->_mov_db->data($mov, "letter"),
                    $this->_mov_db->data($mov, "folder"), // Added
                    $this->_mov_db->data($mov, "imdb"),
                    $date->format('Y-m-d')
                ));
            }

            $count++;

            if($this->page_limit && $count > $start_at + $this->page_limit)
            {
                break;
            }
        }
        return $ret;
    }
}

?>
