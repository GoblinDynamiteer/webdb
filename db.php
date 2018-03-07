<?php
    class mov_db
    {
        function __construct()
        {
            $this->_load_db();
        }

        private $_db;

        private function _load_db()
        {
            echo "Loading";
            $json_string = file_get_contents("db.json");
            $this->_db = json_decode($json_string, true);
        }

        public function keys()
        {
            return array_keys($this->_db);
        }

        function data($mov, $key)
        {
            if(array_key_exists($mov, $this->_db))
            {
                $m = $this->_db[$mov];
                return array_key_exists($key, $m) ? $m[$key] : "-";
            }
            return "-";
        }

        function omdb_data($mov, $key)
        {
            if(array_key_exists($mov, $this->_db))
            {
                $m = $this->_db[$mov]['omdb'];
                return array_key_exists($key, $m) ? $m[$key] : "-";
            }
            return "-";
        }
    }







?>
