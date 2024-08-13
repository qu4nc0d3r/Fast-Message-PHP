<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/systems/config.php');

if (!$_SESSION['room'])
{
    header('Location: /');
    die();
}
else
{
    if (!file_exists($storage . DIRECTORY_SEPARATOR . $_SESSION['room']))
    {
        unset($_SESSION['room']);
        header('Location: /');
        die();
    }

    $config = json_decode(file_get_contents($storage . DIRECTORY_SEPARATOR . $_SESSION['room'] . DIRECTORY_SEPARATOR . 'config.json'), TRUE);

    if (!in_array($_SESSION['user'], $config['users']))
    {
        unset($_SESSION['room']);
        header('Location: /');
        die();
    }

    if ($_POST && isset($_POST['get_message']))
    {
        header('Content-Type: application/json');

        $messages = json_decode(file_get_contents($storage . DIRECTORY_SEPARATOR . $_SESSION['room'] . DIRECTORY_SEPARATOR . 'messages.json'), TRUE);
        if (count($messages['messages']) < 1)
        {
            echo json_encode(['status' => 'error', 'message' => 'Chưa có tin nhắn nào']);
            die();
        }
        echo json_encode([
            'status' => 'success',
            'results' => $messages
        ]);

        die();
    }

}


require_once ($_SERVER['DOCUMENT_ROOT'] . '/systems/header.php');
?>


<div class="card shadow-sm">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div style="float: left !important">
                    <h4>Phòng #<?php echo $_SESSION['room'] ?></h4>
                    Chủ phòng: <span
                        class="text-danger"><?php echo $config['admin']; ?></span><?php echo ($_SESSION['admin']) ? '<small class="fst-italic"> (Bạn là chủ phòng) </small>' : ''; ?>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div style="float: right !important">
                    <?php echo ($_SESSION['admin']) ? '<button class="btn btn-danger btn-sm" type="button" id="delete-room">Xoá phòng</button>' : '' ?>
                    <a href="/" class="btn btn-dark btn-sm">Trang chủ</a>
                </div>
            </div>
        </div>
        <hr>
        <div class="mb-3">
            <div class="chatbox ps-3 pe-3 pt-3 border border-bottom border-opacity-25 rounded" id="chatbox"
                style="background-color: var(--bs-gray-100);">
                <div class="message text-center">Chưa có tin nhắn nào</div>
            </div>
        </div>
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Nhập nội dung tin nhắn..."
                onkeyup="if(event.keyCode == 13) document.getElementById('send-message').click()" id="message" value="">
            <button class="btn btn-success" type="button" id="send-message">Gửi</button>
        </div>
    </div>
</div>


<script>

    $("#send-message").click(() => {
        var message = $("#message").val().trim();
        if (!message) {
            toastr.error('Vui lòng nhập nội dung tin nhắn');
            return;
        }
        $("#chatbox").animate({
            scrollTop: $("#chatbox").prop("scrollHeight")
        }, 1000);
        $.get('/request.php?action=send_message&message=' + message, (data) => {
            $('#message').val('');
            $("#chatbox").animate({
                scrollTop: $("#chatbox").prop("scrollHeight")
            }, 100);
            if (data.message != '' && typeof data.message != 'undefined') {
                toastr[data.status](data.message);
            }
        });
    });

    function getMessages() {
        $.post('', {
            get_message: true
        }, (data) => {
            if (data.results && typeof data.results != undefined && data.results.messages.length > 0) {
                var lists = [];
                var messages = data.results.messages;
                messages.forEach((message) => {
                    lists.push(`<p><img src="https://ui-avatars.com/api/?background=random&name=${message.user}&length=1" alt="" style="border-radius: 100%; border: 2px solid white;" width="32" height="32" title="member"> &nbsp;<span class="text-primary fw-bold">${message.user}</span> <small class="text-muted">[${formatDate(message.created_at)}]</small>: ${message.message} `);
                });
                $("#chatbox").html(lists.join('<div class="border-bottom border"></div></p>'));
            }
            if (data.message != '' && typeof data.message != 'undefined') {
                $("#chatbox").html(`<div class="message text-center">${data.message}</div>`);
            }
            //get status request location header



        }).done((data) => {
            setTimeout(() => {
                getMessages();
            }, 1000);
            x
        });
    }

    $(document).ready(() => {
        getMessages();
        setTimeout(() => {
            $("#chatbox").animate({
                scrollTop: $("#chatbox").prop("scrollHeight")
            }, 1000);
        }, 1000);
        startAutoScroll();
    });

    var interval;
    var scrollTimeout;

    function startAutoScroll() {
        interval = setInterval(() => {
            $("#chatbox").animate({
                scrollTop: $("#chatbox").prop("scrollHeight")
            }, 1000);
        }, 3000);
    }

    function stopAutoScroll() {
        clearInterval(interval);
    }

    function resetScrollTimeout() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            startAutoScroll();
        }, 10000);
    }

    window.addEventListener('wheel', function () {
        stopAutoScroll();
        resetScrollTimeout();
    });


    $("#delete-room").click(() => {
        $.get('/request.php?action=delete_room', (data) => {
            if (data.message != '' && typeof data.message != 'undefined') {
                toastr[data.status](data.message);
            }
            if (data.status == 'success') {
                setTimeout(() => {
                    location.href = '/';
                }, 2000);
            }
        });
    });


    console.log()

    function formatDate(timestamp) {
        var date = new Date(timestamp * 1000);
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        var hours = date.getHours();
        var minutes = "0" + date.getMinutes();
        var seconds = "0" + date.getSeconds();
        var formattedDate = day + '-' + month + '-' + year + ' : ' + hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
        return formattedDate;
    }
</script>

<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/systems/footer.php');
?>