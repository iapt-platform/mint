<?php
require_once '../config.php';
require_once "../public/load_lang.php";

if (!isset($_COOKIE["userid"])) {
    echo "尚未登陆<a href='index.php'>登陆</a>";
} else {
    if (isset($_POST["pwd_set"])) {
        if ($_POST["password"] == $_POST["repassword"]) {
            $md5_password = md5($_POST["password"]);
            $PDO = new PDO(_FILE_DB_USERINFO_, _DB_USERNAME_,_DB_PASSWORD_);
            $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $query = "UPDATE "._TABLE_USER_INFO_." SET password = ? WHERE userid = ? ";
            $stmt = $PDO->prepare($query);
            $stmt->execute(array($md5_password, $_COOKIE["userid"]));
            if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                $error = $PDO->errorInfo();
                echo "修改密码失败。错误信息：$error";
            } else {
                echo "修改密码成功";
            }
        } else {
            echo "两次输入的密码不同。";
        }

    } else {
        if (isset($_COOKIE["pwd_set"])) {
            if ($_COOKIE["pwd_set"] = "on") {
                echo "<h2>wikipali.org</h2>";
                echo "<h2>重新设置密码</h2>";
                ?>
			<form action="pwd_set.php" method="post">
                <div>
                    <span id='tip_password' class='form_field_name'><?php echo $_local->gui->password; ?></span>
                    <input type="password" name="password"  value="" /><br>
                    <input type="password" name="repassword"  value="" />
                    <input type="hidden" name="pwd_set"  value="on" />
				</div>
                <div id="button_area">
                    <input type="submit" value="<?php echo $_local->gui->continue; ?>" style="background-color: var(--link-hover-color);border-color: var(--link-hover-color);" />
                </div>
			</form>
            <?php
}
        } else {
            setcookie("url", "pwd_set.php", time() + 120, "/");
            echo "为了验证是您本人的操作，请先登陆。<a href='index.php?op=login&url=pwd_set.php'>登陆</a>";
        }
    }

}

?>
