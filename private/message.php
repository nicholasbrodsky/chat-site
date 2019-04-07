<?php

    require_once('initialize.php');

    header("Content-Type: application/json");

    if($_SERVER['REQUEST_METHOD'] != 'POST'){
        header("Location: ../index.html");
    }

    try {
        $sent_by = isset($_POST['sent_by']) ? $_POST['sent_by'] : '';
        $sent_to = isset($_POST['sent_to']) ? $_POST['sent_to'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';
        $time = isset($_POST['time']) ? $_POST['time'] : '';
        $option = isset($_POST['option']) ? $_POST['option'] : '';
        $friends = isset($_POST['friends']) ? $_POST['friends'] : array();
        $friends_ids = array();
        $friend_count = array();

        if(!empty($sent_by)){
            $sent_by_user = User::find_by_username($sent_by);
            $sent_by_id = $sent_by_user->id;
        }
        
        if(!empty($sent_to)){
            $sent_to_user = User::find_by_username($sent_to);
            $sent_to_id = $sent_to_user->id;
        }
        

        if(count($friends) >= 1){
            for($i = 0; $i < count($friends); $i++){
                $sent_by_user = User::find_by_username($friends[$i]);
                $friends_ids[$i] = $sent_by_user->id;
            }
        }

        if($option == 'send'){
            $sql = "insert into messages (sent_by, sent_to, message, flag, time) values (?, ?, ?, 0, ?)";
            $stmt = $db->query($sql);
            $stmt->bind_param('iiss', $sent_by_id, $sent_to_id, $message, $time);
            $stmt->execute();
            exit;
        }
        if($option == 'receive'){
            $messages = array();
            $sql = "select id, message, time from messages where sent_to = ? and sent_by = ? and flag = 0 order by id";
            $stmt = $db->query($sql);
            $stmt->bind_param('ii', $sent_to_id, $sent_by_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows < 1){
                print json_encode([
                    'success' => false
                ]);
                exit;
            }
            $stmt->bind_result($id, $message, $time);
            while($stmt->fetch()){
                array_push($messages, $message);
                $sql = "update messages set flag = 1 where id = ?";
                $flag_stmt = $db->query($sql);
                $flag_stmt->bind_param('i', $id);
                $flag_stmt->execute();
            }

            print json_encode([
                'success' => true,
                'messages' => $messages,
                'time' => $time
            ]);
            exit;
        }
        if($option == 'load'){
            $messages = array();
            $sql = 'select message, time, from messages where sent_to = ?, sent_by = ? order by id';
            $stmt = $db->query($sql);
            $stmt->bind_param('ii', $sent_to_id, $sent_by_id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows < 1){
                print json_encode([
                    'success' => false
                ]);
                exit;
            }
            $stmt->bind_result($message, $time);
            while($stmt->fetch()){
                array_push($messages, $message);
            }
        }
        if($option == 'check_messages'){
            for($i = 0; $i < count($friends); $i++){
                $sql = "select count(id) from messages where sent_to = ? and sent_by = ? and flag = 0";
                $stmt = $db->query($sql);
                $stmt->bind_param('ii', $sent_to_id, $friends_ids[$i]);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows != 1){
                    print json_encode([
                        'success' => false,
                        'error' => 'error getting message count'
                    ]);
                    exit("error getting message count");
                }
                $stmt->bind_result($count);
                $stmt->fetch();
                $friend_count[$i] = array('friend' => $friends[$i], 'count' => $count);
            }
            print json_encode([
                'success' => true,
                'friend_count' => $friend_count
            ]);
        }
    }
    catch(Exception $e){
        print json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
?>