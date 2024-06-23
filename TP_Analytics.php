<?php
    session_start();
    include 'TP_pdo.php';

    if (!isset($_SESSION['user']['cno'])) {
        header('Location: TP_Login.php');
        exit;
    }

    // Query 1: 카테고리별 통계
    $sql1 = "SELECT
                CASE
                    WHEN GROUPING(c.categoryName) = 1 THEN '종합'
                    ELSE c.categoryName
                END AS '카테고리',
                SUM(f.price) AS '가격 합산'
            FROM Food f
            JOIN Contain c ON f.foodName = c.foodName
            GROUP BY ROLLUP(c.categoryName);";
    $stmt1 = $pdo->query($sql1);
    $categoryTotalPrice = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Query 2: 카테고리별 판매량
    $sql2 = "SELECT ct.categoryName AS '카테고리', SUM(od.quantity) AS '판매개수'
            FROM Category ct
            JOIN Contain cn ON ct.categoryName = cn.categoryName
            JOIN OrderDetail od ON cn.foodName = od.foodName
            GROUP BY ROLLUP(ct.categoryName)";
    $stmt2 = $pdo->query($sql2);
    $categoryTotalSold = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Query 3: 고객별 주문 총액
    $sql3 = "SELECT c.cno, c.name, SUM(od.totalPrice) AS totalSpent
            FROM Customer c
            JOIN Cart ca ON c.cno = ca.cno
            JOIN OrderDetail od ON ca.id = od.id
            GROUP BY c.cno, c.name";
    $stmt3 = $pdo->query($sql3);
    $customerTotalSpent = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // Query 4: 고객별 주문 순위
    $sql4 = "SELECT
                categoryName AS '카테고리',
                totalSales AS '총 액수',
                RANK() OVER (ORDER BY totalSales DESC) AS '랭킹'
            FROM (
                SELECT c.categoryName, SUM(od.totalPrice) AS totalSales
                FROM OrderDetail od
                JOIN Contain c ON od.foodName = c.foodName
                GROUP BY c.categoryName
            ) AS subquery;";
    $stmt4 = $pdo->query($sql4);
    $customerOrderRank = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // Query 4: 음식별 누적 판매량
    $sql5 = "SELECT
                categoryName AS 카테고리,
                sumInCategory AS 카테고리_내_총합,
                maxInCategory AS 카테고리_내_최고
            FROM (
                SELECT c.categoryName,
                    SUM(od.totalPrice) OVER (PARTITION BY c.categoryName) AS sumInCategory,
                    MAX(od.totalPrice) OVER (PARTITION BY c.categoryName) AS maxInCategory
                FROM OrderDetail od
                JOIN Contain c ON od.foodName = c.foodName
            ) AS subquery
            GROUP BY categoryName, sumInCategory, maxInCategory;";
    $stmt5 = $pdo->query($sql5);
    $foodCumulativeQuantity = $stmt5->fetchAll(PDO::FETCH_ASSOC);

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
    <h3>카테고리별 통계</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>카테고리</th>
                    <th>가격 합산</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categoryTotalPrice as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['카테고리'] ?? '알 수 없음'); ?></td>
                        <td><?php echo number_format($row['가격 합산'] ?? 0); ?>원</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>카테고리별 판매량</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>카테고리</th>
                    <th>판매개수</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categoryTotalSold as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['카테고리'] ?? '종합(중복포함)'); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['판매개수'])); ?>개</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>고객별 주문 총액</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>고객 번호</th>
                    <th>고객 이름</th>
                    <th>총 주문 금액</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customerTotalSpent as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['cno']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['totalSpent'])); ?>원</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>고객별 주문 순위</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>카테고리</th>
                    <th>총 액수</th>
                    <th>랭킹</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customerOrderRank as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['카테고리']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['총 액수'])); ?>원</td>
                        <td><?php echo htmlspecialchars($row['랭킹']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>음식별 누적 판매량</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>카테고리</th>
                    <th>카테고리 내 총합</th>
                    <th>카테고리 내 최고</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($foodCumulativeQuantity as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['카테고리']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['카테고리_내_총합'])); ?>원</td>
                        <td><?php echo htmlspecialchars(number_format($row['카테고리_내_최고'])); ?>원</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>