<?php  // TP_pdo.php
$host = 'localhost'; 
$db = 'D202002473_TP'; // 데이터베이스 이름
$user = 'D202002473'; // 사용자 이름
$pass = '1234'; // 비밀번호
$charset = 'utf8mb4'; // 문자 인코딩

// DSN 설정 (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO 옵션 설정
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 예외 모드로 오류 처리
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 기본 페치 모드 설정
    PDO::ATTR_EMULATE_PREPARES   => false, // 에뮬레이트된 준비문 비활성화
];

try {
    // PDO 인스턴스 생성
    $pdo = new PDO($dsn, $user, $pass, $options);
    $dbConnectionSuccess = true;
} catch (PDOException $e) {
    // 연결 오류 처리
    $dbConnectionSuccess = false;
    $dbConnectionError = '오류: ' . $e->getMessage();
}
?>