<?php
    /* 주문 기록 페이지 */

    session_start();  // 세션 시작
    include 'TP_pdo.php';  // PDO 설정 불러오기

    // 사용자가 로그인된 상태인지 확인
    if (!isset($_SESSION['user']['cno'])) {
        header('Location: TP_Login.php');
        exit;
    }

    // 사용자명 및 필터링 정보
    $cno = $_SESSION['user']['cno'];

    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

    $orderHistory = [];

    // 날짜를 기준으로 필터링해 SELECT
    if ($dbConnectionSuccess) {
        $sql = "SELECT c.id, c.orderDateTime, od.foodName, od.quantity, od.totalPrice
                FROM Cart c
                JOIN OrderDetail od ON c.id = od.id
                WHERE c.cno = :cno";

        $params = ['cno' => $cno];

        if ($startDate) {
            $sql .= " AND c.orderDateTime >= :startDate";
            $params['startDate'] = $startDate;
        }

        if ($endDate) {  // 종료 날짜 당일도 포함되도록 23:59:59 추가
            $endDate .= ' 23:59:59';
            $sql .= " AND c.orderDateTime <= :endDate";
            $params['endDate'] = $endDate;
        }

        $sql .= " ORDER BY c.orderDateTime DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orderHistory = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
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

        <h2>주문 기록</h2>

        <!-- 날짜 필터링 부분 -->
        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <label for="startDate" class="form-label">시작 날짜</label>
                    <input type="date" id="startDate" name="startDate" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                </div>
                <div class="col-md-3">
                    <label for="endDate" class="form-label">종료 날짜</label>
                    <input type="date" id="endDate" name="endDate" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
                <div class="col-md-1 align-self-end">
                    <button type="submit" class="btn btn-primary">필터 적용</button>
                </div>
                <div class="col-md-1 align-self-end">
                    <a href="TP_OrderDetail.php" class="btn btn-success">초기화</a>
                </div>
            </div>
        </form>

        <!-- 주문 목록 출력 -->
        <?php if (empty($orderHistory)): ?>
            <div class="alert alert-info" role="alert">
                주문 기록이 없습니다.
            </div>
        <?php else: ?>
            <?php foreach ($orderHistory as $orderID => $details): ?>
                <?php 
                    $totalPrice = 0;
                    foreach ($details as $detail) { $totalPrice += $detail['totalPrice']; }
                ?>
                <div class="card mb-3">
                    <div class="card-header">
                        주문 ID: <?php echo htmlspecialchars($orderID); ?> | 주문 날짜: <?php echo htmlspecialchars($details[0]['orderDateTime']); ?>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="col-md-8">음식 이름</th>
                                    <th class="col-md-2">수량</th>
                                    <th class="col-md-2">총 가격</th>
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
                    <div class="card-footer text-end">
                        <strong>총 가격: <?php echo htmlspecialchars(number_format($totalPrice)); ?>원</strong>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>