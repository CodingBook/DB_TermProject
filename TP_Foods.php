<?php
    /* Foods 페이지 */

    session_start();  // 세션 시작
    include 'TP_pdo.php';  // PDO 설정 불러오기

    // Food 테이블의 데이터를 조회
    $foods = [];
    if ($dbConnectionSuccess) {
        $sql = "SELECT f.foodName, f.price, GROUP_CONCAT(c.categoryName SEPARATOR ', ') AS categories
                FROM Food f
                JOIN Contain ct ON f.foodName = ct.foodName
                JOIN Category c ON ct.categoryName = c.categoryName
                GROUP BY f.foodName, f.price;";
        $stmt = $pdo->query($sql);
        $foods = $stmt->fetchAll();
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


    <div class="container mt-5">
        <h2>음식 목록</h2>
        <?php if (!$dbConnectionSuccess): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $dbConnectionError; ?>
            </div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>카테고리</th>
                        <th>음식 이름</th>
                        <th>가격</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foods as $food): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($food['categories']); ?></td>
                            <td><?php echo htmlspecialchars($food['foodName']); ?></td>
                            <td><?php echo htmlspecialchars($food['price']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>