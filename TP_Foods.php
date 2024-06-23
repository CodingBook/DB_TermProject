<?php
    /* Foods 페이지 */

    session_start();  // 세션 시작
    include 'TP_pdo.php';  // PDO 설정 불러오기

    // 검색 및 필터링 처리
    $foodName = isset($_GET['foodName']) ? $_GET['foodName'] : '';
    $minPrice = isset($_GET['minPrice']) ? $_GET['minPrice'] : '';
    $maxPrice = isset($_GET['maxPrice']) ? $_GET['maxPrice'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';

    // 기본 SQL 쿼리 (1단계)
    $sql = "SELECT DISTINCT f.foodName, f.price
            FROM Food f
            JOIN Contain ct ON f.foodName = ct.foodName
            JOIN Category c ON ct.categoryName = c.categoryName
            WHERE 1=1";

    // 조건 추가
    if ($foodName) { $sql .= " AND f.foodName LIKE :foodName"; }
    if ($minPrice) { $sql .= " AND f.price >= :minPrice"; }
    if ($maxPrice) { $sql .= " AND f.price <= :maxPrice"; }
    if ($category) { $sql .= " AND c.categoryName = :category"; }

    // Food 테이블의 데이터를 조회 (1단계)
    $filteredFoods = [];
    if ($dbConnectionSuccess) {
        $stmt = $pdo->prepare($sql);

        // 바인드 파라미터 설정
        if ($foodName) { $stmt->bindValue(':foodName', '%' . $foodName . '%'); }
        if ($minPrice) { $stmt->bindValue(':minPrice', $minPrice); }
        if ($maxPrice) { $stmt->bindValue(':maxPrice', $maxPrice); }
        if ($category) { $stmt->bindValue(':category', $category); }

        $stmt->execute();
        $filteredFoods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 음식 이름 리스트 추출
    $foodNames = array_column($filteredFoods, 'foodName');
    $foodNamesPlaceholder = implode(',', array_fill(0, count($foodNames), '?'));

    // 기본 SQL 쿼리 (2단계)
    $sql2 = "SELECT f.foodName, f.price, GROUP_CONCAT(c.categoryName SEPARATOR ', ') AS categories
             FROM Food f
             JOIN Contain ct ON f.foodName = ct.foodName
             JOIN Category c ON ct.categoryName = c.categoryName";

    // 음식 이름 조건 추가 (2단계)
    if (!empty($foodNames)) {
        $sql2 .= " WHERE f.foodName IN ($foodNamesPlaceholder)";
    }

    $sql2 .= " GROUP BY f.foodName, f.price";

    // Food 테이블의 데이터를 조회 (2단계)
    $foods = [];
    if ($dbConnectionSuccess && !empty($foodNames)) {
        $stmt = $pdo->prepare($sql2);
        $stmt->execute($foodNames);
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <script>
        $(document).ready(function() {
            $('.add-to-cart-form').on('submit', function(event) {
                event.preventDefault(); // 기본 폼 제출을 막음

                var form = $(this);
                $.ajax({
                    type: 'POST',
                    url: 'TP_Cart.php',
                    data: form.serialize(),
                    success: function(response) {
                        alert('장바구니에 담겼습니다.');
                    },
                    error: function() {
                        alert('장바구니 담기에 실패했습니다.');
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div>
        <!-- 상단 구역 포함 -->
        <?php include 'TP_navbar.php'; ?>
    </div>

    <div class="container mt-5">
        <h2>음식 목록</h2>

        <!-- 검색 및 필터링 폼 -->
        <form method="GET" action="TP_Foods.php" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="foodName" class="form-control" placeholder="음식 이름" value="<?php echo htmlspecialchars($foodName); ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" name="minPrice" class="form-control" placeholder="최소 가격" value="<?php echo htmlspecialchars($minPrice); ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" name="maxPrice" class="form-control" placeholder="최대 가격" value="<?php echo htmlspecialchars($maxPrice); ?>">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-control">
                        <option value="">카테고리 선택</option>
                        <option value="과일" <?php echo $category == '과일' ? 'selected' : ''; ?>>과일</option>
                        <option value="한식" <?php echo $category == '한식' ? 'selected' : ''; ?>>한식</option>
                        <option value="일식" <?php echo $category == '일식' ? 'selected' : ''; ?>>일식</option>
                        <option value="서양식" <?php echo $category == '서양식' ? 'selected' : ''; ?>>서양식</option>
                        <option value="아시안식" <?php echo $category == '아시안식' ? 'selected' : ''; ?>>아시안식</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">검색</button>
                </div>
            </div>
        </form>

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
                        <th>수량</th>
                        <th>담기</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foods as $food): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($food['categories']); ?></td>
                            <td><?php echo htmlspecialchars($food['foodName']); ?></td>
                            <td><?php echo htmlspecialchars($food['price']); ?></td>
                            <td style="width: 100px;">
                                <form method="POST" action="TP_Cart.php" class="form-inline add-to-cart-form">
                                    <input type="hidden" name="foodName" value="<?php echo htmlspecialchars($food['foodName']); ?>">
                                    <input type="hidden" name="price"    value="<?php echo htmlspecialchars($food['price']); ?>">
                                    <input type="number" name="quantity" class="form-control" value="1" min="1">
                            </td>
                            <td style="width: 100px;">
                                    <button type="submit" class="btn btn-success">담기</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>