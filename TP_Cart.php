<?php
    session_start();
    include 'TP_pdo.php';

    // 사용자가 로그인된 상태인지 확인
    if (!isset($_SESSION['user']['cno'])) {
        header('Location: TP_Login.php');
        exit;
    }

    // 장바구니 세션 초기화
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // POST 요청으로 데이터가 전송되었는지 확인
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['foodName']) && isset($_POST['quantity']) && isset($_POST['price'])) {
        $foodName = $_POST['foodName'];
        $quantity = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        // 장바구니에 동일한 항목이 있는지 확인
        $itemFound = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['foodName'] === $foodName) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $itemFound = true;
                break;
            }
        }

        // 새로운 항목 추가
        if (!$itemFound) {
            $_SESSION['cart'][] = [
                'foodName' => $foodName,
                'quantity' => $quantity,
                'price' => $price
            ];
        }
    }

    // 항목 제거 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
        $foodNameToRemove = $_POST['remove'];
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($foodNameToRemove) {
            return $item['foodName'] !== $foodNameToRemove;
        });
    }

    // 결제 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
        if ($dbConnectionSuccess && isset($_SESSION['user']['cno'])) {
            // Cart 기록 저장
            $cno = $_SESSION['user']['cno'];
            //$datetime = date('Y-m-d H:i:s');

            $stmt = $pdo->query("SELECT MAX(id) AS max_id FROM Cart");
            $result = $stmt->fetch();
            $maxId = isset($result['max_id']) ? $result['max_id'] : 0;
            $id = str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);

            $sql = "INSERT INTO Cart (id, orderDateTime, cno) VALUES (:id, NOW(), :cno)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id, 'cno' => $cno]);

            // 주문 세부 기록 저장
            foreach ($_SESSION['cart'] as $cartItem) {
                $stmt = $pdo->query("SELECT MAX(itemNo) AS max_item_no FROM OrderDetail");
                $result = $stmt->fetch();
                $itemNo = $result['max_item_no'] + 1;

                $sql = "INSERT INTO OrderDetail (itemNo, id, quantity, totalPrice, foodName) VALUES (:itemNo, :id, :quantity, :totalPrice, :foodName)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'itemNo' => $itemNo,
                    'id' => $id,
                    'quantity' => $cartItem['quantity'],
                    'totalPrice' => $cartItem['quantity'] * $cartItem['price'],
                    'foodName' => $cartItem['foodName']
                ]);
            }

            $_SESSION['cart'] = [];
            echo "<script>alert('주문이 완료되었습니다.');</script>";
        }
    }

    // 장바구니 내용 조회
    $cartItems = $_SESSION['cart'];

    // 총 가격 계산
    $totalPrice = 0;
    foreach ($cartItems as $item) {
        $totalPrice += $item['price'] * $item['quantity'];
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
        <h2>장바구니</h2>
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info" role="alert">
                장바구니가 비어 있습니다.
            </div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>음식 이름</th>
                        <th>수량</th>
                        <th>단가</th>
                        <th>총 가격</th>
                        <th>액션</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['foodName']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($item['price'])); ?>원</td>
                            <td><?php echo htmlspecialchars(number_format($item['price'] * $item['quantity'])); ?>원</td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <button type="submit" name="remove" value="<?php echo htmlspecialchars($item['foodName']); ?>" class="btn btn-danger">제거</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mb-3">
                <strong>총 가격: <?php echo htmlspecialchars(number_format($totalPrice)); ?>원</strong>
            </div>
            <form method="POST">
                <button type="submit" name="order" class="btn btn-success">결제</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>