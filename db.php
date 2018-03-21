<?php
    class mov_db
    {
        function __construct()
        {
            $this->_load_db();
        }

        private $_db;
        private $sorting_order;

        /* Load json db */
        private function _load_db()
        {
            $json_string = file_get_contents("dbmov.json");
            $this->_db = json_decode($json_string, true);
        }

        /* Get all keys (movies) */
        public function keys()
        {
            return array_keys($this->_db);
        }

        /* Get all keys (movies) */
        public function sort_by($sort, $order)
        {
            $this->sorting_order = $order;

            if($sort == "year")
            {
                usort($this->_db, array($this, "_sort_by_year"));
            }

            if($sort == "added")
            {
                usort($this->_db, array($this, "_sort_by_added"));
            }

            if($sort == "letter")
            {
                usort($this->_db, array($this, "_sort_by_letter"));
            }
        }

        /* Sort by movie year */
        private function _sort_by_year($a, $b)
        {
            $ystring_a = $ystring_b = "0000";

            if($a['omdb'] && array_key_exists("Year", $a['omdb']))
            {
                $ystring_a = $a['omdb']['Year'];
            }

            if($b['omdb'] && array_key_exists("Year", $b['omdb']))
            {
                $ystring_b = $b['omdb']['Year'];
            }

            if($this->sorting_order == "desc")
            {
                return strnatcmp($ystring_b, $ystring_a);
            }

            return strnatcmp($ystring_a, $ystring_b);
        }

        /* Sort by added date, helper function */
        private function _sort_by_added($a, $b)
        {
            $adate = DateTime::createFromFormat('j M Y', $a['date_scanned']);
            $bdate = DateTime::createFromFormat('j M Y', $b['date_scanned']);

            if($this->sorting_order == "desc")
            {
                return ($adate > $bdate);
            }

            return ($adate < $bdate);
        }

        /* Sort by letter, helper function */
        private function _sort_by_letter($a, $b)
        {
            $let_a = $let_b = "-";

            if(array_key_exists("letter", $a))
            {
                $let_a = $a['letter'];
            }

            if(array_key_exists("letter", $b))
            {
                $let_b = $b['letter'];
            }

            if($this->sorting_order == "desc")
            {
                return ($let_a > $let_b);
            }

            return ($let_a < $let_b);
        }

        /* Get specific data */
        function data($mov, $key)
        {
            if(array_key_exists($mov, $this->_db))
            {
                $m = $this->_db[$mov];
                if($m)
                {
                    return array_key_exists($key, $m) ? $m[$key] : "-";
                }
            }
            return "-";
        }

        /* Get specific omdb data */
        function omdb_data($mov, $key)
        {
            if(array_key_exists($mov, $this->_db))
            {
                $m = $this->_db[$mov]['omdb'];
                if($m)
                {
                    return array_key_exists($key, $m) ? $m[$key] : "-";
                }
            }
            return "-";
        }
    }
?>
