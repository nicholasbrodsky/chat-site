<?php

    require_once('database.php');

    class User
    {
        public $id, $first_name, $last_name, $email, $username, $password, $status;

        public function __construct($first_name='', $last_name='', $email='', $username='', $password='', $status=0)
        {
            $this->first_name = $first_name;
            $this->last_name = $last_name;
            $this->email = $email;
            $this->username = $username;
            $this->password = $password;
            $this->status = $status;
        }
        public static function find_all()
        {
            global $db;
            $sql = "select * from users";
            $stmt = $db->query($sql);
            $stmt->execute();
            //$result = $stmt->get_result();
            //$stmt->bind_result($id, $email, $username, $password, $first_name, $last_name);
            $stmt->bind_result($id, $first_name, $last_name, $email, $username, $password, $status);
            $obj_set = array();
            while($stmt->fetch()){
                //$user = array('id' => $id, 'email' => $email, 'username' => $username, 'password' => $password, 'first_name' => $first_name, 'last_name' => $last_name);
                $user = array('id' => $id, 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'username' => $username, 'password' => $password, 'status' => $status);
                array_push($obj_set, self::create_obj($user));
            }
            /*
            while($user = $result->fetch_assoc()){
                array_push($obj_set, self::create_obj($user));
            }
            */
            $stmt->free_result();
            return $obj_set;
        }
        public static function find_by_id($id)
        {
            global $db;
            $sql = "select * from users where id = ?";
            $stmt = $db->query($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            //$stmt->bind_result($id, $email, $username, $password, $first_name, $last_name);
            $stmt->bind_result($id, $first_name, $last_name, $email, $username, $password, $status);
            $stmt->fetch();
            //$user = array('id' => $id, 'email' => $email, 'username' => $username, 'password' => $password, 'first_name' => $first_name, 'last_name' => $last_name);
            $user = array('id' => $id, 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'username' => $username, 'password' => $password, 'status' => $status);
            //$result = $stmt->get_result();
            //$user = $result->fetch_assoc();
            $stmt->free_result();
            return self::create_obj($user);
        }
        public static function find_by_username($username)
        {
            global $db;
            $sql = "select * from users where username = ?";
            $stmt = $db->query($sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows == 1){
                //$stmt->bind_result($id, $email, $username, $password, $first_name, $last_name);
                $stmt->bind_result($id, $first_name, $last_name, $email, $username, $password, $status);
                $stmt->fetch();
                $stmt->free_result();
                //$user = array('id' => $id, 'email' => $email, 'username' => $username, 'password' => $password, 'first_name' => $first_name, 'last_name' => $last_name);
                $user = array('id' => $id, 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'username' => $username, 'password' => $password, 'status' => $status);
                return self::create_obj($user);
            }
            return false;

        }
        public static function authenticate($username, $password)
        {
            global $db;
            $sql = "select * from users where username = ? and password = ?";
            $stmt = $db->query($sql);
            $stmt->bind_param('ss', $username, $password);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows != 1)
                return false;
            //$stmt->bind_result($id, $email, $username, $password, $first_name, $last_name);
            $stmt->bind_result($id, $first_name, $last_name, $email, $username, $password, $status);
            $stmt->fetch();
            //$user = array('id' => $id, 'email' => $email, 'username' => $username, 'password' => $password, 'first_name' => $first_name, 'last_name' => $last_name);
            $user = array('id' => $id, 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'username' => $username, 'password' => $password, 'status' => $status);
            //$result = $stmt->get_result();
            //$user = $result->fetch_assoc();
            $stmt->free_result();
            return self::create_obj($user);
        }
        public static function user_exists($email, $username)
        {
            global $db;
            $sql = "select * from users where email = ? or username = ?";
            $stmt = $db->query($sql);
            $stmt->bind_param('ss', $email, $username);
            $stmt->execute();
            $stmt->store_result();
            //$stmt->bind_result($id, $email, $username, $password, $first_name, $last_name);
            if($stmt->num_rows > 0)
                return true;
            else
                return false;
            /*
            $result = $stmt->get_result();
            if($result->num_rows > 0)
                return true;
            else
                return false;
            */
        }
        public function create_user()
        {
            global $db;
            $existing_user = self::user_exists($this->email, $this->username);
            if(!$existing_user){
                $sql = "insert into users (first_name, last_name, email, username, password, status) values (?, ?, ?, ?, ?, 0)";
                $stmt = $db->query($sql);
                $stmt->bind_param('sssss', $this->first_name, $this->last_name, $this->email, $this->username, $this->password);
                $stmt->execute();

                $sql = "select id from users where username = ? and password = ?";
                $stmt = $db->query($sql);
                $stmt->bind_param('ss', $this->username, $this->password);
                $stmt->execute();
                $stmt->bind_result($id);
                $stmt->fetch();
                return $id;
            }
            else {
                return false;
            }
        }
        public function add_friend($find_user)
        {
            global $db;
            if($friend = self::find_by_username($find_user)){
                $sql = "insert into " . $this->username . " values (?, (select username from users where id = ?))";
                $stmt = $db->query($sql);
                $stmt->bind_param('ii', $friend->id, $friend->id);
                $stmt->execute();
                return true;
            }
            return false;
        }
        public function delete_friend($old_user)
        {
            global $db;
            if($friend = self::find_by_username($old_user)){
                $sql = "delete from " . $this->username . " where id = ?";
                $stmt = $db->query($sql);
                $stmt->bind_param('i', $friend->id);
                $stmt->execute();
                return true;
            }
            return false;
        }
        private static function create_obj($user)
        {
            $obj = new self;
            $obj->id = $user['id'];
            $obj->first_name = $user['first_name'];
            $obj->last_name = $user['last_name'];
            $obj->email = $user['email'];
            $obj->username = $user['username'];
            $obj->password = $user['password'];
            $obj->status = $user['status'];
            return $obj;
        }
    }
?>