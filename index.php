<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/systems/config.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/systems/header.php');
?>


<div class="card shadow-sm">
    <div class="card-body">
        <div class="row dflex justify-content-center">
            <div class="col-md-6" id="vao-phong-html">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mã phòng</label>
                    <input type="text" class="form-control" id="code" placeholder="..." maxlength="12" value="">
                </div>
                <div class="mb-3 text-center">
                    <button type="button" class="btn btn-primary" id="vao-phong">Vào phòng</button>
                    <?php echo ($_SESSION['room']) ? '<a href="/chat.php" class="btn btn-success">Phòng của tôi</a>' : '
                &nbsp;hoặc&nbsp; <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#tao-phong-modal"">Tạo phòng</button>' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tao-phong-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="">Tạo Phòng Chat</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="text-modal">
                    Bạn chưa có phòng hoặc mã phòng bạn nhập không tồn tại, bạn có muốn tạo phòng mới không?
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="tao-phong-btn">Tạo phòng</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    $("#tao-phong-btn").click(() => {
        $.get("/request.php?action=create", (data) => {
            if (data.message != '' && typeof data.message != 'undefined') {
                toastr[data.status](data.message);
            }
            if (data.code != '' && typeof data.code != 'undefined') {
                $("#tao-phong-btn").attr('disabled', true);
                $("#text-modal").html('<center>Tạo phòng thành công, mã phòng của bạn là: <span class="fw-bold text-danger fs-3" style="font-family: Tahoma">' + data.code + '</span><br><a href="/chat.php" style="text-decoration:none">Vào phòng đã tạo</a></center>');
            }
        });
    });

    $("#vao-phong").click(() => {
        var code = $("#code").val().trim();
        if (!code) {
            toastr["error"]("Vui lòng nhập mã phòng");
            return;
        }

        $("#overlay").fadeIn(300);

        $.get("/request.php?action=join&code=" + code, (data) => {

            $("#overlay").fadeOut(300);

            if (data.message != '' && typeof data.message != 'undefined') {
                toastr[data.status](data.message);

                if (data.message == 'Mã phòng không tồn tại') {
                    var taophonmodal = new bootstrap.Modal('#tao-phong-modal', {
                        keyboard: false
                    })
                    taophonmodal.show();
                }
            }
            if (data.status == 'success') {
                setTimeout(() => {
                    location.href = '/chat.php';
                }, 2000);
            }
        });
    });
</script>

<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/systems/footer.php');
?>