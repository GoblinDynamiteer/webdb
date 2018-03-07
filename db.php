<?php
    class mov_db
    {
        function __construct()
        {
            $this->_load_db();
        }

        private $_db;

        /* Load json db */
        private function _load_db()
        {
            $json_string = file_get_contents("db.json");
            $this->_db = json_decode($json_string, true);
        }

        /* Get all keys (movies) */
        public function keys()
        {
            return array_keys($this->_db);
        }

        /* Get all keys (movies) */
        public function sort_by($sort)
        {
            if($sort == "year")
            {
                usort($this->_db, array($this, "_sort_by_year"));
            }
        }

        private function _sort_by_year($a, $b)
        {
            $ystring_a = $ystring_b = "0000";

            if(array_key_exists("Year", $a['omdb']))
            {
                $ystring_a = $a['omdb']['Year'];
            }

            if(array_key_exists("Year", $b['omdb']))
            {
                $ystring_b = $b['omdb']['Year'];
            }

            return strnatcmp($ystring_a, $ystring_b);
        }

        /* Get specific data */
        function data($mov, $key)
        {
            if(array_key_exists($mov, $this->_db))
            {
                $m = $this->_db[$mov];
                return array_key_exists($key, $m) ? $m[$key] : "-";
            }
            return "-";
        }

        /* Get specific omdb data */
        function omdb_data($mov, $key)
        {
            if(array_key_exists($mov, $this->_db))
            {
                // FIXME: Build check for key-exists: omdb
                $m = $this->_db[$mov]['omdb'];
                return array_key_exists($key, $m) ? $m[$key] : "-";
            }
            return "-";
        }
    }
?>
