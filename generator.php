<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/db.php";

class html_mov_generator
{
    private $_titles;
    private $_mov_db;
    private $show_imdb;
    private $sort_order;
    private $current_page;
    private $page_limit;
    private $sort_by;
    private $search_string;

    function __construct(
        $sort_by, $sort_order, $page_limit, $current_page, $show_imdb,
        $search_string)
    {
        $this->_titles = array(
            "Title", "Year", "Letter", "Folder", "Imdb", "Added");
        $this->_mov_db = new mov_db();
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order == "asc" ? "desc" : "asc";
        $this->show_imdb = $show_imdb;
        $this->current_page = $current_page == "" ? 0 : intval($current_page);
        $this->page_limit = $page_limit == "" ? 0 : intval($page_limit);
        $this->search_string = $search_string;
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
            $t = "\r\n<th class=\"tableheader\"><a href=\"index.php?sort=" . strtolower($title) .
                "&order={$this->sort_order}\">{$title}</a></th>";
            $header_string .= $t;
        }
        return $header_string;
    }

    private function _generate_link_options($page, $imdb)
    {
        $var_string = "index.php?page=" . (string)$page;
        $var_string .= ($this->page_limit ? "&limit=" . $this->page_limit : "");
        $var_string .= ($this->sort_by ? "&sort=" . $this->sort_by : "");
        $var_string .= ($this->sort_order ? "&order=" . $this->sort_order : "");
        $var_string .= $imdb == "" ? "" : "&extend_info={$imdb}";
        return $var_string;
    }

    public function page_nav_footer()
    {
        $footer_string = "";
        $prev = $this->current_page == 0 ? 0 : ($this->current_page) - 1;
        $footer_string = "<a href=\"" . $this->_generate_link_options($prev, "").
            "\">PREV</a>";
        $footer_string .= " PAGE " . (string)($this->current_page) . " ";
        $footer_string .= "<a href=\"" .
            $this->_generate_link_options(1 + $this->current_page, "") ."\">NEXT</a>";
        return $footer_string;
    }

    private function _generate_table_row($col_data, $status)
    {
        $ret = "\r\n<tr class=\"status_{$status}\">";
        foreach ($col_data as $data)
        {
            $ret .= "\r\n<td>{$data}</td>";
        }
        return $ret . "\r\n</tr>";
    }

    private function _generate_table_row_mov_info($mov)
    {
        $colspan = (string)count($this->_titles);

        $folder = $this->_mov_db->data($mov, "folder");
        $letter = $this->_mov_db->data($mov, "letter");
        $imdb = $this->_mov_db->data($mov, "imdb");
        $title = $this->_mov_db->omdb_data($mov, "Title");
        $title_upper = strtoupper($title);
        $genre = $this->_mov_db->omdb_data($mov, "Genre");
        $year = $this->_mov_db->omdb_data($mov, "Year");
        $actors = $this->_mov_db->omdb_data($mov, "Actors");
        $rating = $this->_mov_db->omdb_data($mov, "imdbRating");
        $country = $this->_mov_db->omdb_data($mov, "Country");

        $ret = "\r\n<tr><td class=\"movieinfo\"colspan =\"{$colspan}\">
            <h2>{$title_upper} ({$year})</h2>
            {$genre}<br>
            COUNTRY: $country<br>
            ACTORS: {$actors}<br>
            {$imdb} - RATING: {$rating}<br>
            LOCATION: /{$letter}/$folder/";
        return $ret . "\r\n</tr>";
    }

    private function _has_search_hit($mov)
    {
        $regex = "/" . str_replace("+", ".+", $this->search_string) . "/i";
        if(preg_match($regex, $this->_mov_db->data($mov, "imdb")))
        {
            return true;
        }
        if(preg_match($regex, $this->_mov_db->omdb_data($mov, "Title")))
        {
            return true;
        }
        if(preg_match($regex, $this->_mov_db->data($mov, "folder")))
        {
            return true;
        }
        return false;
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
                $imdb = $this->_mov_db->data($mov, "imdb");
                $imdb_link =
                    "<a href=\"http://www.imdb.com/title/{$imdb}\">{$imdb}</a>";
                $folder = $this->_mov_db->data($mov, "folder");
                $folder_link = "<a href=\"" .
                    $this->_generate_link_options($this->current_page, $imdb) .
                    "\">{$folder}</a>";
                $title = $this->_mov_db->omdb_data($mov, "Title");
                $status = $this->_mov_db->data($mov, "status");
                if(!$this->search_string || $this->_has_search_hit($mov)){
                    $ret .= $this->_generate_table_row(array(
                        $this->_mov_db->omdb_data($mov, "Title"),
                        $this->_mov_db->omdb_data($mov, "Year"),
                        $this->_mov_db->data($mov, "letter"),
                        $folder_link,
                        $imdb_link,
                        $date->format('Y-m-d')
                    ), $status);
                }

                if($imdb && $imdb == $this->show_imdb)
                {
                    $ret .= $this->_generate_table_row_mov_info($mov);
                }
            }

            $count++;

            if(!$this->search_string && $this->page_limit &&
                $count > $start_at + $this->page_limit)
            {
                break;
            }
        }
        return $ret;
    }
}

class html_tv_generator
{
    private $_titles;
    private $_tv_db;
    private $show_imdb;
    private $sort_order;
    private $current_page;
    private $page_limit;
    private $sort_by;
    private $search_string;

    function __construct(
        $sort_by, $sort_order, $page_limit, $current_page, $show_imdb,
        $search_string)
    {
        $this->_titles = array(
            "Show", "Episode", "Title", "File", "Imdb", "Added");
        $this->_tv_db = new tv_db();
        $this->sort_by = $sort_by;
        $this->sort_order = $sort_order == "asc" ? "desc" : "asc";
        $this->show_imdb = $show_imdb;
        $this->current_page = $current_page == "" ? 0 : intval($current_page);
        $this->page_limit = $page_limit == "" ? 0 : intval($page_limit);
        $this->search_string = $search_string;
    }

    public function title()
    {
        return "TVDb";
    }

    public function table_header()
    {
        $header_string = "";
        foreach ($this->_titles as $title)
        {
            $t = "\r\n<th class=\"tableheader\"><a href=\"index.php?sort=" . strtolower($title) .
                "&order={$this->sort_order}\">{$title}</a></th>";
            $header_string .= $t;
        }
        return $header_string;
    }

    public function table_data()
    {
        $eplist = $this->_tv_db->episode_list();
        $str = "";

        //"Show", "Episode", "Title", "File", "Imdb", "Added");
        foreach($eplist as $ep)
        {
            $str .= $this->_generate_table_row(array(
                $ep['show'],
                $ep['se'],
                $ep['omdb'] ? $ep['omdb']['Title'] : "N/A",
                $ep['file'],
                $ep['omdb'] ? $ep['omdb']['imdbID'] : "N/A",
                $ep['date_scanned']
            ));
        }

        return $str;
    }

    private function _generate_table_row($col_data)
    {
        $ret = "\r\n<tr class=\"status_ok\">";
        foreach ($col_data as $data)
        {
            $ret .= "\r\n<td>{$data}</td>";
        }
        return $ret . "\r\n</tr>";
    }

    public function page_nav_footer()
    {
        return "TABLEFOOTER TV<BR>";
    }

}
?>
