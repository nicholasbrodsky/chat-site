var username = ''
var friends = [];
var friend = '';
var friend_count = [];
var friend_status = [];
var messages = [];
var newMessage;
var messagesHeight;
var messagesDivArray = [];
var checkMessageCount;
var intervalTime = 1000;
var timeSent;
var timeReceived;
var formattedTime;

$(document).ready(function() {

    $(window).on('beforeunload', function() {
        if (username != '') {
            $.post('private/chat.php', {
                'username': username,
                'option': 'sign_out'
            });
        }
        return;
    });

    setInterval(getMessages, intervalTime);
    setInterval(checkMessages, intervalTime);
    setInterval(checkFriends, 5000);


    $('#sign_in_form input[name=username]').focus();
    $('#sign_in').on('click', signIn);
    $('#sign_up').on('click', signUp);
    $('#send').on('click', sendMessage);
    $('#msg').on('keypress', function(event) {
        if (event.which == 13) {
            sendMessage();
        }
    });

    $('#login').on('click', function() {
        $(this).addClass('active');
        $('#register').removeClass('active');

        $('#sign_in_form').show();
        $('#sign_up_form').hide();

        $('#start_form').css('height', '350px');
        $('#sign_in_form input[name=username]').focus();
        $('#register_error').hide();
    });
    $('#register').on('click', function() {
        $(this).addClass('active');
        $('#login').removeClass('active');

        $('#sign_up_form').show();
        $('#sign_in_form').hide();

        $('#start_form').css('height', '500px');
        $('#sign_up_form input[name=first_name]').focus();
        $('#login_error').hide();
        $('#multiple_logins').hide();
    });
    $('.friends').on('click', 'li', function() {
        friend = $(this).text();
        $('.messages#' + friend).show();
        for (var i = 0; i < friends.length; i++) {
            if (friends[i] != friend) {
                $('.messages#' + friends[i]).hide();
            }
        }
        $('#body').show();
        $('#main').css('z-index', 1);
        $('#side_panel').css('z-index', 0);
        $('#main h3').text(friend);
        $('#msg').focus();
    });
    $('#add_friend_btn').on('click', function() {
        $('#add_friend_menu').toggle();
        $('#delete_friend_menu').hide();
    });
    $('#add_friend').on('click', addFriend);
    $('#delete_friend_btn').on('click', function() {
        $('#delete_friend_menu').toggle();
        $('#add_friend_menu').hide();
    });
    $('#delete_friend').on('click', deleteFriend);
});

function signIn(event) {
    event.preventDefault();

    username = $('#sign_in_form input[name=username]').val();
    var password = $('#sign_in_form input[name=password]').val();

    $.post('private/chat.php', {
        'username': username,
        'password': password,
        'option': 'sign_in'
    }, function(data) {
        if (!data.success) {
            if (data.status == 0) {
                $('#login_error').show();
                $('#multiple_logins').hide();
                username = '';
                password = '';
                return;
            }
            if (data.status == 1) {
                $('#multiple_logins').show();
                $('#login_error').hide();
                username = '';
                password = '';
                return;
            }
        } else {
            username = data.username;
            friends = data.friends;

            $('#sign_in_form input[name=username]').val('');
            $('#sign_in_form input[name=password]').val('');
            $('#start_form').hide();
            $('header, section, footer').css('opacity', 1);
            $('#username').html("<i class='glyphicon glyphicon-user'></i>&nbsp;" + username);

            setFriends();
            checkFriends();
        }
    });
}

function signUp(event) {
    event.preventDefault();

    var first_name = $('#sign_up_form input[name=first_name]').val();
    var last_name = $('#sign_up_form input[name=last_name]').val();
    var email = $('#sign_up_form input[name=email]').val();
    username = $('#sign_up_form input[name=username]').val();
    var password = $('#sign_up_form input[name=password]').val();

    $.post('private/chat.php', {
        'option': 'sign_up',
        'first_name': first_name,
        'last_name': last_name,
        'email': email,
        'username': username,
        'password': password
    }, function(data) {
        if (!data.success) {
            $('#register_error').show();
        } else {
            username = data.username;

            $('#sign_up_form input[name=first_name]').val('');
            $('#sign_up_form input[name=last_name]').val('');
            $('#sign_up_form input[name=email]').val('');
            $('#sign_up_form input[name=username]').val('');
            $('#sign_up_form input[name=password]').val('');
            $('#start_form').hide();
            $('header, section, footer').css('opacity', 1);
            $('#username').text("Username: " + username);
        }
    });
}
// send message to specific friend
function sendMessage() {
    if ($('#msg').val() == '' || friend == '')
        return;

    var time = new Date();
    var hour = time.getHours();
    var minute = time.getMinutes();
    if (hour == 0)
        hour = 12;
    if (hour > 12)
        hour -= 12;
    var when;
    if (time.getHours() < 12)
        when = "am";
    else
        when = "pm";
    if (minute < 10)
        formattedTime = hour + ":0" + minute + when;
    else
        formattedTime = hour + ":" + minute + when;

    $.post('private/message.php', {
        'sent_to': friend,
        'sent_by': username,
        'message': $('#msg').val(),
        'time': formattedTime,
        'option': 'send'
    }, function(data) {

    });

    var sentMessage = "<div class='sent-msg'>" + $('#msg').val() + "</div><div class='send-time'>" + formattedTime + "</div>";
    $('#msg').val('');
    $('.messages#' + friend).append(sentMessage);

    messagesHeight = document.getElementById(friend).scrollHeight;
    $('.messages#' + friend).scrollTop(messagesHeight);
    $('#msg').focus();
}
// get messages for current conversation
function getMessages() {
    if (friend == '')
        return;
    $.post('private/message.php', {
        'option': 'receive',
        'sent_by': friend,
        'sent_to': username
    }, function(data) {
        if (!data.success) {
            console.log("no messages");
            return;
        }
        messages = data.messages;
        time = data.time;
        if (messages) {
            for (var i = 0; i < messages.length; i++) {
                $('.messages#' + friend).append("<div class='recv-msg'>" + messages[i] + "</div><div class='recv-time'>" + time + "</div>");
            }
        }
        if (document.getElementById(friend)) {
            messagesHeight = document.getElementById(friend).scrollHeight;
            $('.messages#' + friend).scrollTop(messagesHeight);
        }
    });
}

function loadMessages() {
    $.post('private/messages.php', {
        'option': 'load',
        'sent_by': friend,
        'sent_to': username
    }, function(data) {

    });
}

// add friend
function addFriend() {
    var newFriend = $('#add_friend_input').val();
    if (username == newFriend) {
        console.log("cannot add yourself");
        $('#add_friend_input').addClass('add_error');
        return;
    }
    $.post('private/chat.php', {
        'username': username,
        'new_friend': newFriend,
        'option': 'add_friend'
    }, function(data) {
        if (!data.success) {
            console.log(data.error);
            $('#add_friend_input').addClass('add_error');
        } else {
            friends = data.friends;
            setFriends();
            $('#add_friend_menu').hide();
            $('#add_friend_input').val('');
            $('#add_friend_input').removeClass('add_error');
        }
    });
}
// delete friend
function deleteFriend() {
    var oldFriend = $('#delete_friend_input').val();
    if (username == oldFriend || oldFriend == '')
        return;
    for (var i = 0; i < friends.length; i++) {
        if (oldFriend == friends[i]) {
            $.post('private/chat.php', {
                'username': username,
                'old_friend': oldFriend,
                'option': 'delete_friend'
            }, function(data) {
                if (!data.success) {
                    console.log("error deleting friend");
                    $('#delete_friend_input').addClass('delete_error');
                } else {
                    friends = data.friends;
                    setFriends();
                    $('#delete_friend_menu').hide();
                    $('#delete_friend_input').val('');
                    $('#delete_friend_input').removeClass('delete_error');
                }
                return;
            });
        }
    }
}
// create list of friends and message box for each
function setFriends() {
    $('.friends').html('');
    for (var i = 0; i < friends.length; i++) {
        $('.friends').append("<li id='" + friends[i] + "_list'>" + friends[i] + "</li>");
        $('.friends').append("<span id='" + friends[i] + "_unread'></span><br />");
    }
    // friends with message boxes already
    var friendMessageBox = [];
    // if message boxes are already created, only add new message box with newly added friend, otherwise create all message boxes with current friends
    if (messagesDivArray.length > 0) {
        for (var i = 0; i < messagesDivArray.length; i++) {
            friendMessageBox[i] = messagesDivArray[i].attr('id');
        }
        var checkFriend;
        for (var i = 0; i < friends.length; i++) {
            checkFriend = false;
            for (var j = 0; j < friendMessageBox.length; j++) {
                // when there's a match between original friends list and newly received one
                if (friends[i] == friendMessageBox[j]) {
                    checkFriend = true;
                }
            }
            // if no match, add the new message area for the newly added friend
            if (!checkFriend) {
                var position = messagesDivArray.push($('<div class="messages" id="' + friends[i] + '"></div>'));
                $('#messageBox').append(messagesDivArray[position - 1]);
                messagesDivArray[position - 1].hide();
            }
        }
    } else {
        for (var i = 0; i < friends.length; i++) {
            messagesDivArray[i] = $('<div class="messages"></div>');
            messagesDivArray[i].attr('id', friends[i]);
            $('#messageBox').append(messagesDivArray[i]);
            messagesDivArray[i].hide();
        }
    }
}
// see who else is online
function checkFriends() {
    if (friends.length < 1)
        return;
    $.post('private/chat.php', {
        'username': username,
        'option': 'check_friends'
    }, function(data) {
        if (!data.success) {
            console.log("error getting friend status");
            return;
        }
        friend_status = data.friend_status;
        for (var i = 0; i < friends.length; i++) {
            if (friend_status[i].status == 1) {
                $('#' + friend_status[i].friend + '_list').addClass('online').removeClass('offline').attr('title', friend_status[i].friend + ' is online');
            }
            if (friend_status[i].status == 0) {
                $('#' + friend_status[i].friend + '_list').addClass('offline').removeClass('online').attr('title', friend_status[i].friend + ' is offline');
            }
        }
    });
}
// get any unread messages
function checkMessages() {
    if (username == '' || friends.length < 1)
        return;
    $.post('private/message.php', {
        'option': 'check_messages',
        'sent_to': username,
        'friends': friends
    }, function(data) {
        if (!data.success) {
            console.log("error getting number of unread messages");
            return;
        }
        friend_count = data.friend_count;
        for (var i = 0; i < friends.length; i++) {
            for (var j = 0; j < friend_count.length; j++) {
                if (friends[i] == friend_count[j].friend) {
                    if (friend_count[j].count > 0) {
                        $('#' + friends[i] + '_unread').text(friend_count[j].count).addClass('unread');
                    } else {
                        $('#' + friends[i] + '_unread').text('').removeClass('unread');
                    }
                }
            }
        }
    });
}