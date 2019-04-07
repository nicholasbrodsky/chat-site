<?php

    header('Content-Type: application/json');

    require_once('initialize.php');

    if($_SERVER['REQUEST_METHOD'] != 'POST'){
        header("Location: ../index.html");
    }
    
    try {
        $option = isset($_POST['option']) ? $_POST['option'] : '';
        if($option == 'sign_out'){
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $user = User::find_by_username($username);
            $sql = "update users set status = 0 where id = ?";
            $stmt = $db->query($sql);
            $stmt->bind_param('i', $user->id);
            $stmt->execute();
        }
        if($option == 'sign_in'){
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $user = User::authenticate($username, $password);
            if(!$user){
                echo json_encode(array(
                    'success' => false,
                    'status' => 0
                ));
                exit;
            }
            if($user->status == 1){
                echo json_encode(array(
                    'success' => false,
                    'status' => 1
                ));
                exit;
            }
            $sql = "update users set status = 1 where id = ?";
            $stmt = $db->query($sql);
            $stmt->bind_param('i', $user->id);
            $stmt->execute();
            $sql = "select friend from " . $username;
            $stmt = $db->query($sql);
            //$stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($friend);
            $friends = array();
            while($stmt->fetch()){
                array_push($friends, $friend);
            }

            print json_encode([
                'success' => true,
                'username' => $username,
                'friends' => $friends
            ]);
            exit;
        }
        if($option == 'sign_up') {
            $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
            $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $user = new User($first_name, $last_name, $email, $username, $password);

            if($user->create_user()){
                $sql = "create table " . $username . " ( id int(11) unsigned not null primary key, friend varchar(30) not null, foreign key (id) references users(id) )";
                $stmt = $db->query($sql);
                $stmt->execute();
                print json_encode([
                    'success' => true,
                    'username' => $username
                ]);
                exit;
            }
            print json_encode([
                'success' => false
            ]);
            exit;
        }
        if($option == 'add_friend') {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $new_friend = isset($_POST['new_friend']) ? $_POST['new_friend'] : '';
            if($user = User::find_by_username($username)){
                if($user->add_friend($new_friend)){
                    $sql = "select friend from " . $username;
                    $stmt = $db->query($sql);
                    //$stmt->bind_param('s', $username);
                    $stmt->execute();
                    $stmt->bind_result($friend);
                    $friends = array();
                    while($stmt->fetch()){
                        array_push($friends, $friend);
                    }
                    print json_encode([
                        'success' => true,
                        'friends' => $friends
                    ]);
                    exit;
                }
                else {
                    print json_encode([
                        'success' => false,
                        'error' => 'Could not add friend to usernames table'
                    ]);
                    exit;
                }
            }
            else {
                print json_encode([
                    'success' => false,
                    'error' => 'Could not find current user'
                ]);
                exit;
            }
        }
        if($option == 'delete_friend'){
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $old_friend = isset($_POST['old_friend']) ? $_POST['old_friend'] : '';
            if($user = User::find_by_username($username)){
                if($user->delete_friend($old_friend)){
                    $sql = "select friend from " . $username;
                    $stmt = $db->query($sql);
                    //$stmt->bind_param('s', $username);
                    $stmt->execute();
                    $stmt->bind_result($friend);
                    $friends = array();
                    while($stmt->fetch()){
                        array_push($friends, $friend);
                    }
                    print json_encode([
                        'success' => true,
                        'friends' => $friends
                    ]);
                    exit;
                } else {
                    print json_encode([
                        'success' => false,
                        'error' => 'Could not delete friend from usernames table'
                    ]);
                    exit;
                }
            } else {
                print json_encode([
                    'success' => false,
                    'error' => 'Could not find current user'
                ]);
                exit;
            }
        }
        if($option == 'check_friends'){
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            if($user = User::find_by_username($username)){
                $sql = "select id from " . $user->username;
                $stmt = $db->query($sql);
                $stmt->execute();
                $stmt->bind_result($friend_id);
                $friends_ids = array();
                $friend_status = array();
                while($stmt->fetch()){
                    if(!isset($friend_id)){
                        print json_encode([
                            'success' => false,
                            'error' => 'could not find friend id'
                        ]);
                        exit;
                    }
                    array_push($friends_ids, $friend_id);
                }
                $stmt->free_result();
                for($i = 0; $i < count($friends_ids); $i++){
                    $inner_sql = "select username, status from users where id = ?";
                    $inner_stmt = $db->query($inner_sql);
                    $inner_stmt->bind_param('i', $friends_ids[$i]);
                    $inner_stmt->execute();
                    $inner_stmt->bind_result($friend, $status);
                    $inner_stmt->fetch();
                    array_push($friend_status, array('friend' => $friend, 'status' => $status));
                    $inner_stmt->free_result();
                }
                print json_encode([
                    'success' => true,
                    'friend_status' => $friend_status
                ]);
                exit;
                
            } else {
                print json_encode([
                    'success' => false,
                    'error' => 'could not find user'
                ]);
                exit;
            }
        }
    }
    catch(Exception $e){
        print json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    


?>