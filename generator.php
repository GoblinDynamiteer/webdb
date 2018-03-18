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
        $this->_titles = array("Title", "Year", "Letter", "Folder", "Imdb", "Added");
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
                "&order=" . $order .
                "\">{$title}</a></th>";
            $header_string .= $t;
        }


        return $header_string;
    }
    
    public function page_nav_footer($page)
    {
        $footer_string = "";
        $prev = intval($page) == 0 ? 0 : intval($page) - 1;
        $footer_string = "<a href=\"index.php?limit=25&page=" . (string)$prev . "\">PREV</a>";
        $footer_string .= " PAGE " . $page . " ";
        $footer_string .= "<a href=\"index.php?limit=25&page=" . (string)(1 + intval($page)) . "\">NEXT</a>";
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

    public function table_data($sort, $limit, $page)
    {
        $count = 0;
        $start_at = intval($page) * intval($limit);
        $this->_mov_db->sort_by($sort);
        
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
            
            if($limit != "" && $count > $start_at + intval($limit))
            {
                break;
            }
        }
        return $ret;
    }
}

?>
