<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/systems/config.php');
header('Content-Type: application/json');

/* CHẶN SPAM CLICK */
$currentTime     = time();
$lastRequestTime = isset($_SESSION['last_request_time']) ? $_SESSION['last_request_time'] : 0;
$timeDifference  = $currentTime - $lastRequestTime;
$timeLimit       = 3;
if ($timeDifference < $timeLimit)
{
    echo json_encode(['status' => 'error', 'message' => 'Thao tác quá nhanh, vui lòng thử lại sau']);
    die();
}
$_SESSION['last_request_time'] = $currentTime;
/* END */

$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : '';


if ($action == 'delete_room')
{
    if (!$_SESSION['room'])
    {
        echo json_encode(['status' => 'error', 'message' => 'Bạn chưa tham gia phòng nào']);
        die();
    }
    $room = $storage . DIRECTORY_SEPARATOR . $_SESSION['room'];
    if (file_exists($room))
    {
        $config = json_decode(file_get_contents($room . DIRECTORY_SEPARATOR . 'config.json'), TRUE);
        if ($config['admin'] != $_SESSION['admin'])
        {
            echo json_encode(['status' => 'error', 'message' => 'Bạn không phải chủ phòng']);
            die();
        }

        $files = glob($room . '/*');
        foreach ($files as $file)
        {
            if (is_file($file))
            {
                unlink($file);
            }
        }

        if (rmdir($room))
        {
            unset($_SESSION['room']);
            unset($_SESSION['admin']);
            echo json_encode(['status' => 'success', 'message' => 'Xoá phòng thành công']);
        }
        else
        {
            echo json_encode(['status' => 'error', 'message' => 'Xoá phòng thất bại']);
        }
    }
    else
    {
        echo json_encode(['status' => 'error', 'message' => 'Phòng không tồn tại']);
    }
    die();
}

if ($action == 'send_message' && isset($_GET['message']))
{
    if (!$_SESSION['room'])
    {
        echo json_encode(['status' => 'error', 'message' => 'Bạn chưa tham gia phòng nào']);
        die();
    }
    $message = sanitizeInput(antiXSS($_GET['message']));
    if ($message == '')
    {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập nội dung tin nhắn']);
        die();
    }
    if (strlen($message) > 1000)
    {
        echo json_encode(['status' => 'error', 'message' => 'Nội dung tin nhắn không được quá 1000 ký tự']);
        die();
    }
    $room                   = $storage . DIRECTORY_SEPARATOR . $_SESSION['room'];
    $messages               = json_decode(file_get_contents($room . DIRECTORY_SEPARATOR . 'messages.json'), TRUE);
    $messages['messages'][] = [
        'user' => $_SESSION['user'],
        'message' => $message,
        'created_at' => time()
    ];
    file_put_contents($room . DIRECTORY_SEPARATOR . 'messages.json', json_encode($messages));
    echo json_encode(['status' => 'success', 'message' => 'Gửi tin nhắn thành công']);
    die();
}

if ($action == 'create')
{
    if ($_SESSION['room'])
    {
        echo json_encode(['status' => 'error', 'message' => 'Bạn đã có phòng, để tao phòng mới vui lòng xoá phòng đã tạo và thử lại']);
        die();
    }
    $code = '';
    while (TRUE)
    {
        $code = '';
        for ($i = 0; $i < 12; $i++)
        {
            $code .= random_char();
        }
        $room = $storage . DIRECTORY_SEPARATOR . $code;
        if (!file_exists($room))
        {
            break;
        }
    }

    if (mkdir($room, 0777, TRUE))
    {
        $_SESSION['room']  = $code;
        $_SESSION['admin'] = "admin_" . $code;
        $namebot           = 'BOT_' . random_letter() . rand(10000, 99999);
        $configFile        = $room . DIRECTORY_SEPARATOR . 'config.json';
        $configData        = [
            'admin' => $_SESSION['admin'],
            'users' => [
                $_SESSION['user'],
                $namebot
            ],
            'created_at' => time()
        ];
        $messageFile       = $room . DIRECTORY_SEPARATOR . 'messages.json';
        $messageData       = [
            'messages' => [
                [
                    'user' => $namebot,
                    'message' => 'Chào mừng bạn đến với phòng chat của chúng tôi, tôi là <b>' . $namebot . '</b>, có thể giúp gì cho bạn?',
                    'created_at' => time()
                ]
            ]
        ];
        file_put_contents($messageFile, json_encode($messageData));
        file_put_contents($configFile, json_encode($configData));

        echo json_encode(['status' => 'success', 'message' => 'Tạo phòng thành công', 'code' => $code]);
    }
    else
    {
        $error = error_get_last();
        echo json_encode(['status' => 'error', 'message' => 'Tạo phòng thất bại', 'error' => $error]);
    }

    die();
}

if ($action == 'join')
{
    if ($_SESSION['room'])
    {
        echo json_encode(['status' => 'error', 'message' => 'Bạn đã có phòng, để tham gia phòng mới vui lòng thoát phòng hiện tại và thử lại']);
        die();
    }
    $code = isset($_GET['code']) ? sanitizeInput($_GET['code']) : '';
    if ($code == '' || !preg_match('/^[A-Z0-9]{12}$/', $code))
    {
        echo json_encode(['status' => 'error', 'message' => 'Mã phòng không hợp lệ']);
        die();
    }
    $room = $storage . '/' . $code;
    if (!file_exists($room))
    {
        echo json_encode(['status' => 'error', 'message' => 'Mã phòng không tồn tại']);
        die();
    }
    $config = json_decode(file_get_contents($room . '/config.json'), TRUE);
    if (in_array($_SESSION['user'], $config['users']))
    {
        echo json_encode(['status' => 'error', 'message' => 'Bạn đã tham gia phòng này rồi']);
        die();
    }
    $config['users'][] = $_SESSION['user'];
    file_put_contents($room . '/config.json', json_encode($config));
    $_SESSION['room'] = $code;
    echo json_encode(['status' => 'success', 'message' => 'Tham gia phòng thành công']);
    die();
}
