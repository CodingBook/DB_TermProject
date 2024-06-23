<?php
    session_start();
    include 'TP_pdo.php';

    if (!isset($_SESSION['user']['cno'])) {
        header('Location: TP_Login.php');
        exit;
    }

    $cno = $_SESSION['user']['cno'];

    $orderHistory = [];
    if ($dbConnectionSuccess) {
        $sql = "SELECT c.id, c.datetime, od.foodName, od.quantity, od.totalPrice
                FROM Cart c
                JOIN OrderDetail od ON c.id = od.id
                WHERE c.cno = :cno
                ORDER BY c.datetime DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cno' => $cno]);
        $orderHistory = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
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
        <?php include 'TP_navbar.php'; ?>
    </div>

    <div class="container mt-5">
        <h2>주문 기록</h2>
        <?php if (empty($orderHistory)): ?>
            <div class="alert alert-info" role="alert">
                주문 기록이 없습니다.
            </div>
        <?php else: ?>
            <?php foreach ($orderHistory as $orderID => $details): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        주문 ID: <?php echo htmlspecialchars($orderID); ?> | 주문 날짜: <?php echo htmlspecialchars($details[0]['datetime']); ?>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>음식 이름</th>
                                    <th>수량</th>
                                    <th>총 가격</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($details as $detail): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detail['foodName']); ?></td>
                                        <td><?php echo htmlspecialchars($detail['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($detail['totalPrice'])); ?>원</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>