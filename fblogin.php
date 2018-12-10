<?php
require "./.dk189/config.php";

$sid = isset($_GET["sid"]) ? $_GET["sid"] : "";

$sid = !empty($sid) ? json_decode(decrypt($sid)) : false;

$sid = !!$sid ? $sid : false;

$login = null;

if (!!$sid && \count($_POST) > 0) {
    try {
        $senderId = $sid->sender;

        $_POST['username'] = $_POST['loginTxtUsername'];
        $_POST['password'] = $_POST['loginTxtPassword'];

        $Machine = new \TNU\Machine();

        if ($Machine->login($_POST['username'], md5($_POST['password']))) {
            $now = microtime(true);
            $nowTxt = "" . $now . "";
            $nowMd5 = md5($nowTxt);
            $cert = array(
                "prefixTime" => strrev($nowMd5),
                "username" => $_POST['username'],
                "currentTime" => $now,
                "password" => $_POST['password'],
                "suffixTime" => $nowMd5,
            );
            $certTxt = encrypt(jval($cert));

            $student = $Machine->getStudent();

            $login = true;

            $resp = $fdb->set("facebook-bot/sqt-ictu/users/" . $senderId, "token", $certTxt);
            $resp = $fdb->set("facebook-bot/sqt-ictu/users/" . $senderId, "student", $student);

            foreach ($Machine->getSemesterOfStudy() as $semester) {
                if ($semester->KyHienTai) {
                    $resp = $fdb->set("facebook-bot/sqt-ictu/users/" . $senderId, "semester", $semester);
                    break;
                }
            }

            $resp = $fdb->set(
                "sessions", md5($certTxt) . $nowMd5,
                array(
                    "token" => $certTxt,
                    "username" => $_POST['username'],
                    "password" => $_POST['password'],
                    "currentTime" => $now,
                )
            );
            $fb->sendMessage($senderId, "Liên kết thành công với tài khoản `" . $student->HoTen . "(" . $student->MaSinhVien . ")" . "`!");
        } else {
            $login = false;
        }
    } catch (\Exception $ex) {
        $login = false;
    }
}

?><!DOCTYPE xhtml>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Liên kết tài khoản Facebook - Lịch sinh viên</title>

        <!-- Disable tap highlight on IE -->
        <meta name="msapplication-tap-highlight" content="no">

        <!-- Add to homescreen for Chrome on Android -->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="Lịch ICTU">
        <link rel="icon" type="image/png" sizes="192x192" href="/Assets/icons/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/Assets/icons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/Assets/icons/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/Assets/icons/favicon-16x16.png">

        <!-- Add to homescreen for Safari on iOS -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="Lịch ICTU">
        <link rel="apple-touch-icon" href="/Assets/icons/apple-icon.png">
        <link rel="apple-touch-icon" sizes="57x57" href="/Assets/icons/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/Assets/icons/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/Assets/icons/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/Assets/icons/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/Assets/icons/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/Assets/icons/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/Assets/icons/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/Assets/icons/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/Assets/icons/apple-icon-180x180.png">

        <!-- Tile icon for Win8 (144x144 + tile color) -->
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/Assets/icons/ms-icon-144x144.png">

        <!-- Color the status bar on mobile devices -->
        <meta name="theme-color" content="#009688">

        <!-- Vendors css -->
        <link rel="stylesheet" href="/Assets/vendors/bootstrap-4.1.0/dist/css/bootstrap.min.css" />
        <link rel="stylesheet" href="/Assets/vendors/fullcalendar-3.9.0/fullcalendar.min.css" />
        <link rel="stylesheet" href="/Assets/vendors/fontawesome-free-5.0.10/web-fonts-with-css/css/fontawesome-all.min.css" />

        <!-- Material Design icons -->
        <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:regular,bold,italic,thin,light,bolditalic,black,medium&amp;lang=en"> -->
        <link rel="stylesheet" href="/Assets/fonts/Material-Icons/Material-Icons.css">
        <!-- <link rel="stylesheet" href="/Assets/vendors/mdl/material.min.css"> -->
        <link rel="stylesheet" href="/Assets/vendors/mdl/material.teal-purple.min.css">

        <!-- Custom css -->
        <link rel="stylesheet" href="/Assets/styles/mdl.css" />
        <link rel="stylesheet" href="/Assets/styles/main.css" />
        <link rel="stylesheet" href="/Assets/styles/signin.css" />
    </head>
    <body class="text-center">
        <form class="form-signin" method="post">
            <img class="mb-4" src="/Assets/icons/android-icon-192x192.png" alt="" width="72" height="72" />
            <h1 class="h3 mb-3 font-weight-normal">Liên kết tài khoản sinh viên</h1>

            <?php if (!$sid) {?>
                <div class="jumbotron">
                    <h1 class="display-4">Lỗi truy cập!</h1>
                    <p class="lead">Hình như bạn đã làm sai hướng dẫn?</p>
                    <hr class="my-4">
                    <a class="btn btn-primary btn-lg" href="/" role="button">Về trang chủ</a>
                </div>
            <?php } ?>
            <?php if (!!$sid && ($sid->time + 24*60*60) < time()) {?>
                <div class="jumbotron">
                    <h1 class="display-4">Hết thời gian!</h1>
                    <p class="lead">Phiên đăng nhập của bạn đã hết hạn. Vui lòng thực hiện lại lệnh `login` và làm theo hướng dẫn.</p>
                    <hr class="my-4">
                    <a class="btn btn-primary btn-lg" href="/" role="button">Về trang chủ</a>
                </div>
            <?php } ?>

            <?php if ($login === true) {?>
                <div class="jumbotron">
                    <h1 class="display-4">Thành công!</h1>
                    <p class="lead">Liên kết thành công với tài khoản sinh viên.</p>
                    <hr class="my-4">
                    <a class="btn btn-primary btn-lg" href="https://m.me/sqt.tkb" role="button">Trở về hộp chat</a>
                    <a class="btn btn-secondary btn-lg" href="javascript:this.close()" role="button">Đóng của sổ</a>
                </div>
            <?php } ?>

            <?php if (!$login && !!$sid && ($sid->time + 24*60*60) >= time()) {?>
                <?php if ($login === false) {?>
                    <div class="alert alert-danger" role="alert">
                    Thông tin đăng nhập không hợp lệ!
                    </div>
                <?php } ?>
                <style>
                .form-signin {
                    max-width: 330px;
                }
                </style>
                <label for="inputEmail" class="sr-only">Tên đăng nhập</label>
                <input type="text" id="loginTxtUsername" name="loginTxtUsername" class="form-control" placeholder="Tên đăng nhập" value="<?php echo isset($_POST['loginTxtUsername']) ? $_POST['loginTxtUsername'] : "";?>" required autofocus />
                <label for="inputPassword" class="sr-only">Mật khẩu</label>
                <input type="password" id="loginTxtPassword" name="loginTxtPassword" class="form-control" placeholder="Mật khẩu" value="<?php echo isset($_POST['loginTxtPassword']) ? $_POST['loginTxtPassword'] : "";?>" required />
                <button class="btn btn-lg btn-primary btn-block" type="submit">Liên kết</button>
            <?php } ?>

            <p class="mt-5 mb-3 text-muted">&copy; SQT-ICTU</p>
        </form>
    </body>
</html>
