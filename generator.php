<?php
require_once __DIR__."/config.php";
require_once SITE_ROOT."/db.php";

class setting
{
    private $options = array("sort", "page", "year", "order", "info", "limit", "search", "show");
    private $options_default = array(
        "sort" => "title",
        "limit" => 25,
        "page" => 1,
        "year" => null,
        "order" => "az",
        "info" => null,
        "search" => null,
        "show" => null);

    function __construct()
    {
    }

    public function options()
    {
        return $this->options;
    }

    public function get($option)
    {
        if(isset($_GET[$option]))
        {
            return $_GET[$option];
        }
        return $this->options_default[$option];
    }
}

class html_mov_generator
{
    private $_titles;
    private $_settings;
    private $_db;
    private $_db_filtered_keys;

    function __construct()
    {
        $this->_db = new mov_db();
        $this->_settings = new setting();
        $this->_filter_db();
        $this->_titles = array(
            "#", "Title", "Year", "Letter", "Folder", "Srt", "Imdb", "Added");
    }

    private function _filter_db_year($mov)
    {
        $year = $this->_settings->get("year");
        if($year) {
            $mov_year = $this->_db->omdb_data($mov, "Year");
            return($mov_year == $year);
        }
        return true;
    }

    private function _filter_db_search($mov)
    {
        $search = $this->_settings->get("search");
        if($search) {
            $regex = "/" . str_replace("+", ".+", $search) . "/i";
            if(preg_match($regex, $this->_db->data($mov, "imdb")))
                return true;
            if(preg_match($regex, $this->_db->omdb_data($mov, "Title")))
                return true;
            if(preg_match($regex, $this->_db->data($mov, "folder")))
                return true;
            return false;
        }
        return true;
    }

    private function _filter_db()
    {
        $this->_db->sort_by(
            $this->_settings->get("sort"),
            $this->_settings->get("order"));
        $keys = $this->_db->keys();
        $keys = array_filter($keys, array($this, "_filter_db_search"));
        $this->_db_filtered_keys = array_filter($keys, array($this, "_filter_db_year"));
    }

    public function title()
    {
        return "MovDb";
    }

    public function table_header()
    {
        $header_string = "";
        $sort_order = "";
        foreach ($this->_titles as $title) {
            if(strtolower($title) == $this->_settings->get("sort")) {
                $sort_order = $this->_settings->get("order") == "az" ? "za" : "az";
            } else {
                $sort_order =  $this->_settings->get("order");
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
        foreach ($this->_settings->options() as $option) {
            if(isset($_GET[$option])) {
                $data = array_merge($data, array($option => $_GET[$option]));
            }
        }
        if($override_options_array) {
            $data = array_merge($data, $override_options_array);
        }
        return "<a href=\"index.php?" . http_build_query($data) . "\">{$text}</a>";
    }

    private function _get_total_page_count()
    {
        return ceil(count($this->_db_filtered_keys) / $this->_settings->get("limit"));
    }

    public function page_nav_footer()
    {
        $foot = "";
        $page = 1;
        $total_pages = $this->_get_total_page_count();
        $mov_count = count($this->_db_filtered_keys);

        if(isset($_GET["page"])) {
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
        foreach ($col_data as $data) {
            $ret .= "\r\n<td>{$data}</td>";
        }
        return $ret . "\r\n</tr>";
    }

    private function _generate_table_row_mov_info($mov)
    {
        $colspan = (string)count($this->_titles);
        $folder = $this->_db->data($mov, "folder");
        $letter = $this->_db->data($mov, "letter");
        $imdb = $this->_db->data($mov, "imdb");
        $title = $this->_db->omdb_data($mov, "Title");
        $title_upper = strtoupper($title);
        $genre = $this->_db->omdb_data($mov, "Genre");
        $year = $this->_db->omdb_data($mov, "Year");
        $actors = $this->_db->omdb_data($mov, "Actors");
        $rating = $this->_db->omdb_data($mov, "imdbRating");
        $country = $this->_db->omdb_data($mov, "Country");

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
        $start_at = ($this->_settings->get("page") - 1) * $this->_settings->get("limit");
        $ret = "";

        foreach ($this->_db_filtered_keys as $mov) {
            if($processed_count >= $start_at) {
                $show_data = true;

                if(strpos($this->_db->data($mov, "date_scanned"), ":") !== false){
                    $date = DateTime::createFromFormat('j M Y H:i',
                        $this->_db->data($mov, "date_scanned"));
                } else {
                    $date = DateTime::createFromFormat('j M Y',
                        $this->_db->data($mov, "date_scanned"));
                }

                $imdb = $this->_db->data($mov, "imdb");
                $imdb_link =
                    "<a href=\"http://www.imdb.com/title/{$imdb}\">{$imdb}</a>";

                $folder = $this->_db->data($mov, "folder");
                $folder_link = $this->_gen_link($folder, array('info' => $imdb));

                $year = $this->_db->omdb_data($mov, "Year");
                $year_link = $this->_gen_link($year, array('year' => $year));

                // Determine subtitles:
                $subs_string = "";
                $subs_dict = $this->_db->data($mov, "subs");
                $subs_string .= $subs_dict["sv"] ? "sv" : "";
                $subs_string .= ($subs_dict["sv"] && $subs_dict["en"] ? " | " : "")
                    . ($subs_dict["en"] ? "en" : "");

                $title = $this->_db->omdb_data($mov, "Title");
                $status = $this->_db->data($mov, "status");

                if($show_data) {
                    $displayed_count++;
                    $ret .= $this->_generate_table_row(array(
                        $displayed_count + ($this->_settings->get("page") - 1) * $this->_settings->get("limit"),
                        $this->_db->omdb_data($mov, "Title"),
                        $year_link,
                        $this->_db->data($mov, "letter"),
                        $folder_link,
                        $subs_string,
                        $imdb_link,
                        $date->format('Y-m-d')
                    ), $status);
                }

                if($imdb && $imdb == $this->_settings->get("info")) {
                    $ret .= $this->_generate_table_row_mov_info($mov);
                }
            }

            $processed_count++;
            if($displayed_count >= $this->_settings->get("limit"))
                break;
        }
        return $ret;
    }
}

class html_tv_generator
{
    private $_titles;
    private $_db;
    private $_db_filtered_keys;
    private $_ep_list;
    private $_settings;

    function __construct()
    {
        $this->_db = new tv_db();
        $this->_settings = new setting();
        $this->_filter_db();
        $this->_titles = array(
            "Show", "Episode", "Title", "File", "Srt", "Imdb", "Added");
    }

    private function _filter_db()
    {
        $this->_db->sort_by($this->_settings->get("sort"), $this->_settings->get("order"));
        $this->_ep_list = $this->_db->episode_list();
        $this->_ep_list = array_filter($this->_ep_list, array($this, "_filter_db_search"));
        $this->_ep_list = array_filter($this->_ep_list, array($this, "_filter_db_show"));
    }

    private function _filter_db_search($ep)
    {
        $search = $this->_settings->get("search");
        if($search) {
            $regex = "/" . str_replace("+", ".+", $search) . "/i";
            if(preg_match($regex, $ep["show"]))
                return true;
            if(preg_match($regex, $ep["file"]))
                return true;
            return false;
        }
        return true;
    }

    private function _filter_db_show($ep)
    {
        $show = $this->_settings->get("show");
        if($show) {
            $regex = "/" . str_replace(" ", ".", $show) . "/i";
            if(preg_match($regex, $ep["show"])) {
                return true;
            }
            return false;
        }
        return true;
    }

    public function title()
    {
        return "TVDb";
    }

    public function table_header()
    {
        $header_string = "";
        $sort_order = "";
        foreach ($this->_titles as $title) {
            if(strtolower($title) == $this->_settings->get("sort")) {
                $sort_order = $this->_settings->get("order") == "az" ? "za" : "az";
            } else {
                $sort_order =  $this->_settings->get("order");
            }

            $link = $this->_gen_link($title, array('order' => $sort_order, 'sort' => strtolower($title)));
            $t = "\r\n<th class=\"tableheader\">{$link}</th>";
            $header_string .= $t;
        }
        return $header_string;
    }

    private function _gen_link($text, $override_options_array = null)
    {
        $data = array();
        foreach ($this->_settings->options() as $option) {
            if(isset($_GET[$option])) {
                $data = array_merge($data, array($option => $_GET[$option]));
            }
        }
        if($override_options_array) {
            $data = array_merge($data, $override_options_array);
        }
        return "<a href=\"index.php?" . http_build_query($data) . "\">{$text}</a>";
    }

    public function table_data()
    {
        $str = "";
        $displayed_count = 0;
        $processed_count = 0;
        $start_at = ($this->_settings->get("page") - 1) * $this->_settings->get("limit");

        //"Show", "Episode", "Title", "File", "Srt", "Imdb", "Added");
        foreach($this->_ep_list as $ep) {
            if($processed_count >= $start_at) {
                $imdb = $ep['omdb'] ? $ep['omdb']['imdbID'] : "";
                $imdb_link =
                    "<a href=\"http://www.imdb.com/title/{$imdb}\">{$imdb}</a>";

                $subs_string = "";
                $subs_dict = $ep["subs"];
                $subs_string .= $subs_dict["sv"] ? "sv" : "";
                $subs_string .= ($subs_dict["sv"] && $subs_dict["en"] ? " | " : "")
                    . ($subs_dict["en"] ? "en" : "");

                $show_link = $this->_gen_link($ep['show'], array('show' => $ep['show']));

                if(strpos($ep['date_scanned'], ":") !== false){
                    $date = DateTime::createFromFormat('j M Y H:i', $ep['date_scanned']);
                } else {
                    $date = DateTime::createFromFormat('j M Y', $ep['date_scanned']);
                }

                $str .= $this->_generate_table_row(array(
                    $show_link,
                    $ep['se'],
                    $ep['omdb'] ? $ep['omdb']['Title'] : "N/A",
                    $ep['file'],
                    $subs_string,
                    $imdb_link,
                    $date->format('Y-m-d')
                ), $ep['status']);
                $displayed_count++;
            }
            $processed_count++;
            if($displayed_count >= $this->_settings->get("limit"))
                break;
        }
        return $str;
    }

    private function _get_total_page_count()
    {
        return ceil(count($this->_ep_list) / $this->_settings->get("limit"));
    }

    private function _generate_table_row($col_data, $status)
    {
        $ret = "\r\n<tr class=\"status_{$status}\">";
        foreach ($col_data as $data) {
            $ret .= "\r\n<td>{$data}</td>";
        }
        return $ret . "\r\n</tr>";
    }

    public function page_nav_footer()
    {
        $foot = "";
        $ep_count = count($this->_ep_list);
        $total_pages = $this->_get_total_page_count();

        $page = $this->_settings->get("page");
        $prev = $page == 1 ? 1 : $page - 1;
        $next = $page + 1;

        $foot .= $this->_gen_link("PREV", array('page' => $prev));
        $foot .= " | PAGE " . (string)$page ." OF {$total_pages} | ";
        $foot .= $this->_gen_link("NEXT", array("page" => $next));

        $foot .= "<br>total episodes: {$ep_count}";
        return $foot;
    }

}
?>
