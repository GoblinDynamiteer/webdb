<?php
    function load_db()
    {
        $json_string = file_get_contents("db.json");
        return json_decode($json_string, true);
    }

    function get_db_keys()
    {
        $data = load_db();
        return array_keys($data);
    }

?>
