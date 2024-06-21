<?php
    /* 로그인 페이지 */

    session_start();  // 세션 시작
    include 'TP_pdo.php';  // PDO 설정 불러오기

    // 로그인 처리
    $loginError = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cno = $_POST['cno'];
        $passwd = $_POST['passwd'];

        // SQL 쿼리 준비 및 실행
        $sql = "SELECT * FROM Customer WHERE cno = :cno AND passwd = :passwd";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cno' => $cno, 'passwd' => $passwd]);

        // 결과 확인
        if ($stmt->rowCount() > 0) {
            // 로그인 성공 시 세션에 정보 저장 및 TP_foods.php로 리디렉션
            $_SESSION['user'] = $stmt->fetch();
            header("Location: TP_Foods.php");
            exit();
        } else {
            // 로그인 실패
            $loginError = "로그인 실패! 회원 번호나 비밀번호를 확인해주세요.";
        }
    }
?>

<!DOCTYPE html>
<html lang="kr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>[TP] C-ON of 202002473</title>
    <!-- BootStrap5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <div>
        <!-- 상단 구역 포함 -->
        <?php include 'TP_navbar.php'; ?>
    </div>

    <!-- 로그인 구역 -->
    <div class="container mt-5">
        <h2>로그인</h2>
        <?php if (!$dbConnectionSuccess): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $dbConnectionError; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($loginError)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $loginError; ?>
            </div>
        <?php endif; ?>

        <form action="TP_login.php" method="post">
            <div class="mb-3">
                <label for="cno" class="form-label">회원 번호</label>
                <input type="text" class="form-control" id="cno" name="cno" required>
            </div>
            <div class="mb-3">
                <label for="passwd" class="form-label">비밀번호</label>
                <input type="password" class="form-control" id="passwd" name="passwd" required>
            </div>
            <button type="submit" class="btn btn-primary">로그인</button>
        </form>
    </div>
</body>
</html>