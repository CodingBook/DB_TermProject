<!-- TP_navbar.php -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="TP_Login.php" style="position: relative; display: inline-block; font-size: 3.0rem; margin-left: 1.0rem;">
            C-ON System
        </a>

        <?php if (isset($_SESSION['user'])): ?>
            <span class="navbar-text mx-auto" style="font-size: 18px;">안녕하세요, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>님</span>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="TP_foods.php" style="font-size: 1.5rem;">음식</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="TP_cart.php" style="font-size: 1.5rem;">장바구니</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="TP_order_history.php" style="font-size: 1.5rem;">주문 기록</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="TP_logout.php" style="font-size: 1.5rem;">로그아웃</a>
                    </li>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['cno'] == "c0"): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="TP_logout.php" style="font-size: 1.5rem;">통계</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</nav>