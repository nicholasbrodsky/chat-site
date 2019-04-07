<?php

    class Database
    {
        private $connection;
        
        /*
        private $db_host = "x10host.c467kqvieunn.us-west-1.rds.amazonaws.com";
        private $db_user = "nickbrodsky";
        private $db_pass = "College_07";
        private $db_name = "chat_app";
        */

        /*
        private $db_host = "198.91.81.6";
        private $db_user = "nickbx10_nickb";
        private $db_pass = "College_07";
        private $db_name = "nickbx10_chat";
        */

        private $db_host = "localhost";
        private $db_user = "nicholas";
        private $db_pass = "password";
        private $db_name = "chat_site";
        
        // private $db_host = "localhost";
        // private $db_user = "nickbrodsky";
        // private $db_pass = "College_07";
        // private $db_name = "chat_test";

        public function __construct()
        {
            $this->open_connection();
        }

        public function open_connection()
        {
            $this->connection = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
            if($this->connection->connect_errno){
                exit("Database connection failed.");
            }
        }
        public function close_connection()
        {
            $this->connection->close();
        }
        public function query($sql)
        {
            $stmt = $this->connection->prepare($sql);
            if(!$stmt){
                exit("Database query failed.");
            }
            return $stmt;
        }
    }

    $db = new Database();
?>