<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/db.php";

class html_mov_generator
{
    private $_titles;
    private $_mov_db;
    private $_mov_db_filtered_keys;
    private $options = array("sort", "page", "year", "order", "info", "limit", "search");
    private $options_default = array(
        "sort" => "title",
        "limit" => 25,
        "page" => 1,
        "year" => null,
        "order" => "az",
        "info" => null,
        "search" => null);

    function __construct()
    {
        $this->_mov_db = new mov_db();
        $this->_filter_db();
        $this->_titles = array(
            "#", "Title", "Year", "Letter", "Folder", "Srt", "Imdb", "Added");
    }

    private function _filter_db_year($mov)
    {
        $year = $this->_get_opt("year");
        if($year)
        {
            $mov_year = $this->_mov_db->omdb_data($mov, "Year");
            return($mov_year == $year);
        }

        return true;
    }

    private function _filter_db_search($mov)
    {
        $search = $this->_get_opt("search");
        if($search)
        {
            $regex = "/" . str_replace("+", ".+", $search) . "/i";
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
        return true;
    }

    private function _filter_db()
    {
        $this->_mov_db->sort_by($this->_get_opt("sort"), $this->_get_opt("order"));
        $keys = $this->_mov_db->keys();
        $keys = array_filter($keys, array($this, "_filter_db_search"));
        $this->_mov_db_filtered_keys = array_filter($keys, array($this, "_filter_db_year"));
    }

    private function _get_opt($option)
    {
        if(isset($_GET[$option]))
        {
            return $_GET[$option];
        }

        return $this->options_default[$option];
    }

    public function title()
    {
        return "MovDb";
    }

    public function table_header()
    {
        $header_string = "";
        $sort_order = "";

        foreach ($this->_titles as $title)
        {
            if(strtolower($title) == $this->_get_opt("sort"))
            {
                $sort_order = $this->_get_opt("order") == "az" ? "za" : "az";
            }

            else
            {
                $sort_order =  $this->_get_opt("order");
            }

            $t = "\r\n<th class=\"tableheader\"><a href=\"index.php?sort=" . strtolower($title) .
                "&order={$sort_order}\">{$title}</a></th>";
            $header_string .= $t;
        }
        return $header_string;
    }

    private function _gen_link($text, $override_options_array = null)
    {
        $data = array();

        foreach ($this->options as $option)
        {
            if(isset($_GET[$option]))
            {
                $data = array_merge($data, array($option => $_GET[$option]));
            }
        }

        if($override_options_array)
        {
            $data = array_merge($data, $override_options_array);
        }

        return "<a href=\"index.php?" . http_build_query($data) . "\">{$text}</a>";
    }

    private function _get_total_page_count()
    {
        return ceil(count($this->_mov_db_filtered_keys) / $this->_get_opt("limit"));
    }

    public function page_nav_footer()
    {
        $foot = "";
        $page = 1;
        $total_pages = $this->_get_total_page_count();
        $mov_count = count($this->_mov_db_filtered_keys);

        if(isset($_GET["page"]))
        {
            $page = intval($_GET["page"]);
        }

        $prev = $page == 1 ? 1 : $page - 1;
        $next = $page + 1;

        $foot .= $this->_gen_link("PREV", array('page' => $prev));
        $foot .= " | PAGE " . (string)$page ." OF {$total_pages} | ";
        $foot .= $this->_gen_link("NEXT", array("page" => $next));

        $foot .= "<br>total movies: {$mov_count}";
        return $foot;
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

    public function table_data()
    {
        $processed_count = 0;
        $displayed_count = 0;
        $start_at = ($this->_get_opt("page") - 1) * $this->_get_opt("limit");
        $ret = "";

        foreach ($this->_mov_db_filtered_keys as $mov)
        {

            if($processed_count >= $start_at)
            {
                $show_data = true;

                $date = DateTime::createFromFormat('j M Y',
                    $this->_mov_db->data($mov, "date_scanned"));

                $imdb = $this->_mov_db->data($mov, "imdb");
                $imdb_link =
                    "<a href=\"http://www.imdb.com/title/{$imdb}\">{$imdb}</a>";

                $folder = $this->_mov_db->data($mov, "folder");
                $folder_link = $this->_gen_link($folder, array('info' => $imdb));

                $year = $this->_mov_db->omdb_data($mov, "Year");
                $year_link = $this->_gen_link($year, array('year' => $year));

                // Determine subtitles:
                $subs_string = "";
                $subs_dict = $this->_mov_db->data($mov, "subs");
                $subs_string .= $subs_dict["sv"] ? "sv" : "";
                $subs_string .= ($subs_dict["sv"] && $subs_dict["en"] ? " | " : "")
                    . ($subs_dict["en"] ? "en" : "");

                $title = $this->_mov_db->omdb_data($mov, "Title");
                $status = $this->_mov_db->data($mov, "status");

                if($show_data)
                {
                    $displayed_count++;

                    $ret .= $this->_generate_table_row(array(
                        $displayed_count + ($this->_get_opt("page") - 1) * $this->_get_opt("limit"),
                        $this->_mov_db->omdb_data($mov, "Title"),
                        $year_link,
                        $this->_mov_db->data($mov, "letter"),
                        $folder_link,
                        $subs_string,
                        $imdb_link,
                        $date->format('Y-m-d')
                    ), $status);


                }

                if($imdb && $imdb == $this->_get_opt("info"))
                {
                    $ret .= $this->_generate_table_row_mov_info($mov);
                }
            }

            $processed_count++;

            if($displayed_count >= $this->_get_opt("limit"))
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
        $this->sort_order = $sort_order;
        $this->show_imdb = $show_imdb;
        $this->current_page = $current_page == "" ? 0 : intval($current_page);
        $this->page_limit = $page_limit == "" ? 0 : intval($page_limit);
        $this->search_string = $search_string;
    }

    public function title()
    {
        return "TVDb";
    }

    private function _gen_link($page, $imdb)
    {
        $var_string = "index.php?page=" . (string)$page;
        $var_string .= ($this->page_limit ? "&limit=" . $this->page_limit : "");
        $var_string .= ($this->sort_by ? "&sort=" . $this->sort_by : "");
        $var_string .= ($this->sort_order ? "&order=" . $this->sort_order : "");
        $var_string .= $imdb == "" ? "" : "&extend_info={$imdb}";
        return $var_string;
    }

    public function table_header()
    {
        $header_string = "";
        $sort_order = "";

        foreach ($this->_titles as $title)
        {
            if(strtolower($title) == $this->sort_by)
            {
                $sort_order = $this->sort_order == "az" ? "za" : "az"; // toggle order if sorted column
            }

            else
            {
                $sort_order = $this->sort_order;
            }

            $t = "\r\n<th class=\"tableheader\"><a href=\"index.php?sort=" . strtolower($title) .
                "&order={$sort_order}\">{$title}</a></th>";
            $header_string .= $t;
        }
        return $header_string;
    }

    public function table_data()
    {
        $this->_tv_db->sort_by($this->sort_by, $this->sort_order);
        $eplist = $this->_tv_db->episode_list();
        $str = "";
        $count = 0;
        $start_at = $this->current_page * $this->page_limit;

        //"Show", "Episode", "Title", "File", "Imdb", "Added");
        foreach($eplist as $ep)
        {
            if($count >= $start_at)
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

            $count++;
            if($this->page_limit && $count > $start_at + $this->page_limit)
            {
                break;
            }

        }
        return $str;
    }

    private function _get_total_page_count()
    {
        return ceil($this->_tv_db->count_episodes() / $this->page_limit);
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
        $footer_string = "";
        $ep_count = $this->_tv_db->count_episodes();
        $show_count = $this->_tv_db->count_shows();
        $total_pages = $this->_get_total_page_count();
        $prev = $this->current_page == 0 ? 0 : ($this->current_page) - 1;

        $footer_string = "<a href=\"" . $this->_gen_link($prev, "").
            "\">PREV</a>";
        $footer_string .= " | PAGE " . (string)($this->current_page) .
            "  OF {$total_pages} | ";
        $footer_string .= "<a href=\"" .
            $this->_gen_link(1 + $this->current_page, "") ."\">NEXT</a>";
        $footer_string .= "<br>Totals: episodes: {$ep_count} | shows: {$show_count}";
        return $footer_string;
    }

}
?>
