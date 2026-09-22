<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$host = "localhost";
$username = "root";
$password = "";

mysqli_report(MYSQLI_REPORT_OFF);

// 1. Connect without selecting database
$conn = @new mysqli($host, $username, $password);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// 2. Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS sbts_db";
if (!$conn->query($sql)) {
    echo json_encode(["status" => "error", "message" => "Failed to create database: " . $conn->error]);
    exit;
}

// 3. Select database
$conn->select_db("sbts_db");

// 4. Create separate tables for passengers, drivers (operators), admins, buses, and schedules/bookings
$tables = [
    "admins" => "CREATE TABLE IF NOT EXISTS admins (
        uid VARCHAR(50) PRIMARY KEY,
        firstName VARCHAR(100),
        lastName VARCHAR(100),
        email VARCHAR(150) UNIQUE,
        password VARCHAR(100),
        status VARCHAR(50),
        memberSince VARCHAR(50)
    )",
    "passengers" => "CREATE TABLE IF NOT EXISTS passengers (
        uid VARCHAR(50) PRIMARY KEY,
        firstName VARCHAR(100),
        lastName VARCHAR(100),
        email VARCHAR(150) UNIQUE,
        password VARCHAR(100),
        status VARCHAR(50),
        bookings INT DEFAULT 0,
        spent DECIMAL(10,2) DEFAULT 0.00,
        phone VARCHAR(50) NULL,
        nationality VARCHAR(100) NULL,
        passport VARCHAR(100) NULL,
        memberSince VARCHAR(50)
    )",
    "drivers" => "CREATE TABLE IF NOT EXISTS drivers (
        uid VARCHAR(50) PRIMARY KEY,
        firstName VARCHAR(100),
        lastName VARCHAR(100),
        email VARCHAR(150) UNIQUE,
        password VARCHAR(100),
        staffId VARCHAR(50) NULL,
        status VARCHAR(50),
        memberSince VARCHAR(50)
    )",
    "buses" => "CREATE TABLE IF NOT EXISTS buses (
        code VARCHAR(50) PRIMARY KEY,
        reg VARCHAR(50),
        model VARCHAR(100),
        capacity INT,
        status VARCHAR(50),
        operator VARCHAR(150)
    )",
    "routes" => "CREATE TABLE IF NOT EXISTS routes (
        code VARCHAR(50) PRIMARY KEY,
        name VARCHAR(150),
        stations VARCHAR(255),
        distance INT,
        fare DECIMAL(10,2),
        status VARCHAR(50)
    )",
    "schedules" => "CREATE TABLE IF NOT EXISTS schedules (
        id VARCHAR(50) PRIMARY KEY,
        routeCode VARCHAR(50),
        busCode VARCHAR(50),
        driver VARCHAR(150),
        time VARCHAR(50),
        day VARCHAR(50),
        status VARCHAR(50),
        bookedSeats TEXT NULL
    )",
    "bookings" => "CREATE TABLE IF NOT EXISTS bookings (
        ref VARCHAR(50) PRIMARY KEY,
        scheduleId VARCHAR(50) NULL,
        passengerName VARCHAR(150),
        routeName VARCHAR(150),
        seats VARCHAR(100),
        amount DECIMAL(10,2),
        status VARCHAR(50),
        date VARCHAR(50),
        phone VARCHAR(50)
    )",
    "transactions" => "CREATE TABLE IF NOT EXISTS transactions (
        txId VARCHAR(50) PRIMARY KEY,
        bookingRef VARCHAR(50),
        passengerName VARCHAR(150),
        method VARCHAR(50),
        amount DECIMAL(10,2),
        status VARCHAR(50),
        timestamp VARCHAR(100)
    )",
    "recent_activity" => "CREATE TABLE IF NOT EXISTS recent_activity (
        id INT AUTO_INCREMENT PRIMARY KEY,
        `desc` VARCHAR(255),
        `user` VARCHAR(150),
        `time` VARCHAR(100),
        `status` VARCHAR(50)
    )"
];

foreach ($tables as $name => $createSql) {
    // Drop existing table if it has an incompatible schema (e.g. from sbts_database.sql layout missing firstName column)
    $res = $conn->query("SHOW TABLES LIKE '$name'");
    if ($res && $res->num_rows > 0) {
        if ($name === 'admins' || $name === 'passengers') {
            $colRes = $conn->query("SHOW COLUMNS FROM `$name` LIKE 'firstName'");
            if (!$colRes || $colRes->num_rows == 0) {
                $conn->query("DROP TABLE `$name`");
            }
        }
    }

    if (!$conn->query($createSql)) {
        echo json_encode(["status" => "error", "message" => "Failed to create table $name: " . $conn->error]);
        exit;
    }
}

// Ensure scheduleId exists in bookings table
$res = $conn->query("SHOW COLUMNS FROM bookings LIKE 'scheduleId'");
if ($res && $res->num_rows == 0) {
    $conn->query("ALTER TABLE bookings ADD COLUMN scheduleId VARCHAR(50) NULL");
}

// Seeding helper with count check
function checkAndSeed($conn, $table, $checkQuery, $seedData) {
    $res = $conn->query($checkQuery);
    $row = $res->fetch_assoc();
    if ($row['count'] == 0) {
        foreach ($seedData as $item) {
            $columns = [];
            $values = [];
            foreach ($item as $col => $val) {
                if (is_array($val)) {
                    $val = json_encode($val);
                }
                $columns[] = "`$col`";
                $values[] = "'" . $conn->real_escape_string($val) . "'";
            }
            $insSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
            $conn->query($insSql);
        }
    }
}

// Seed admin
checkAndSeed($conn, "admins", "SELECT COUNT(*) as count FROM admins", [
    ["uid" => "USR-001", "firstName" => "System", "lastName" => "Administrator", "email" => "admin@sbts.zm", "password" => "Admin@123", "status" => "Active", "memberSince" => "Jan 1, 2026"]
]);

// Seed passenger
checkAndSeed($conn, "passengers", "SELECT COUNT(*) as count FROM passengers", [
    ["uid" => "USR-002", "firstName" => "Mwenya", "lastName" => "Chileshe", "email" => "mwenya@gmail.com", "password" => "Password123!", "status" => "Active", "bookings" => 2, "spent" => 500.00, "phone" => "+260971234567", "nationality" => "Zambian", "passport" => "PN123456", "memberSince" => "Mar 15, 2026"]
]);

// Seed drivers/operators
checkAndSeed($conn, "drivers", "SELECT COUNT(*) as count FROM drivers", [
    ["uid" => "USR-003", "firstName" => "John", "lastName" => "Banda", "email" => "john.banda@sbts.zm", "password" => "Driver123!", "staffId" => "DRV-101", "status" => "Active", "memberSince" => "Feb 10, 2026"],
    ["uid" => "USR-004", "firstName" => "Grace", "lastName" => "Chanda", "email" => "grace.chanda@sbts.zm", "password" => "Driver123!", "staffId" => "DRV-102", "status" => "Active", "memberSince" => "Feb 12, 2026"]
]);

// Seed buses
checkAndSeed($conn, "buses", "SELECT COUNT(*) as count FROM buses", [
    ["code" => "SB-501", "reg" => "ABC-1234", "model" => "Scania K360", "capacity" => 50, "status" => "Active", "operator" => "John Banda"],
    ["code" => "SB-301", "reg" => "XYZ-5678", "model" => "Toyota Coaster", "capacity" => 30, "status" => "Active", "operator" => "John Banda"],
    ["code" => "SB-601", "reg" => "LMN-9012", "model" => "Volvo B9R", "capacity" => 55, "status" => "Maintenance", "operator" => "Unassigned"],
    ["code" => "SB-401", "reg" => "DEF-3456", "model" => "Mercedes Benz", "capacity" => 40, "status" => "Active", "operator" => "Grace Chanda"],
    ["code" => "SB-701", "reg" => "GHI-7890", "model" => "Hino Rainbow", "capacity" => 45, "status" => "Inactive", "operator" => "Unassigned"]
]);

// Seed routes
checkAndSeed($conn, "routes", "SELECT COUNT(*) as count FROM routes", [
    ["code" => "LUS-KIT", "name" => "Lusaka to Kitwe", "stations" => "Lusaka - Intercity Bus Terminus → Kitwe - Main Bus Station", "distance" => 360, "fare" => 150.00, "status" => "Active"],
    ["code" => "LUS-LIV", "name" => "Lusaka to Livingstone", "stations" => "Lusaka - Intercity Bus Terminus → Livingstone - Bus Station", "distance" => 470, "fare" => 200.00, "status" => "Active"],
    ["code" => "LUS-CHI", "name" => "Lusaka to Chipata", "stations" => "Lusaka - Intercity Bus Terminus → Chipata - Bus Station", "distance" => 550, "fare" => 180.00, "status" => "Active"],
    ["code" => "KIT-NDL", "name" => "Kitwe to Ndola", "stations" => "Kitwe - Main Bus Station → Ndola - Broadway Bus Station", "distance" => 60, "fare" => 40.00, "status" => "Active"],
    ["code" => "LUS-MFU", "name" => "Lusaka to Mfuwe", "stations" => "Lusaka - Intercity Bus Terminus → Mfuwe - Bus Station", "distance" => 620, "fare" => 250.00, "status" => "Inactive"]
]);

// Seed schedules
checkAndSeed($conn, "schedules", "SELECT COUNT(*) as count FROM schedules", [
    ["id" => "SCH-101", "routeCode" => "LUS-KIT", "busCode" => "SB-501", "driver" => "John Banda", "time" => "08:30", "day" => "Today", "status" => "Boarding", "bookedSeats" => [5, 6, 12, 18, 20]],
    ["id" => "SCH-102", "routeCode" => "LUS-LIV", "busCode" => "SB-401", "driver" => "Grace Chanda", "time" => "10:00", "day" => "Today", "status" => "On Time", "bookedSeats" => [1, 2, 3]],
    ["id" => "SCH-103", "routeCode" => "LUS-CHI", "busCode" => "SB-301", "driver" => "John Banda", "time" => "13:15", "day" => "Today", "status" => "On Time", "bookedSeats" => [7, 8]],
    ["id" => "SCH-104", "routeCode" => "KIT-NDL", "busCode" => "SB-501", "driver" => "John Banda", "time" => "15:30", "day" => "Today", "status" => "Delayed", "bookedSeats" => []],
    ["id" => "SCH-105", "routeCode" => "LUS-MFU", "busCode" => "SB-701", "driver" => "Unassigned", "time" => "06:00", "day" => "Tomorrow", "status" => "Cancelled", "bookedSeats" => []]
]);

// Seed bookings
checkAndSeed($conn, "bookings", "SELECT COUNT(*) as count FROM bookings", [
    ["ref" => "SBTS-BK-9831", "scheduleId" => "SCH-101", "passengerName" => "Mwenya Chileshe", "routeName" => "Lusaka to Kitwe", "seats" => "5, 6", "amount" => 300.00, "status" => "Confirmed", "date" => "2026-06-22", "phone" => "+260971234567"],
    ["ref" => "SBTS-BK-8422", "scheduleId" => "SCH-102", "passengerName" => "Mwenya Chileshe", "routeName" => "Lusaka to Livingstone", "seats" => "2", "amount" => 200.00, "status" => "Completed", "date" => "2026-06-15", "phone" => "+260971234567"]
]);

// Seed transactions
checkAndSeed($conn, "transactions", "SELECT COUNT(*) as count FROM transactions", [
    ["txId" => "TXN-9831-MOMO", "bookingRef" => "SBTS-BK-9831", "passengerName" => "Mwenya Chileshe", "method" => "MTN MoMo", "amount" => 300.00, "status" => "Success", "timestamp" => "2026-06-22 08:12"],
    ["txId" => "TXN-8422-AIRTEL", "bookingRef" => "SBTS-BK-8422", "passengerName" => "Mwenya Chileshe", "method" => "Airtel Money", "amount" => 200.00, "status" => "Success", "timestamp" => "2026-06-15 09:30"]
]);

// Seed activity logs
checkAndSeed($conn, "recent_activity", "SELECT COUNT(*) as count FROM recent_activity", [
    ["desc" => "New ticket booking SBTS-BK-9831 confirmed", "user" => "Mwenya Chileshe", "time" => "15 mins ago", "status" => "success"],
    ["desc" => "Bus SB-601 marked under maintenance", "user" => "admin@sbts.zm", "time" => "1 hour ago", "status" => "warning"],
    ["desc" => "New bus departure SCH-103 scheduled to Chipata", "user" => "admin@sbts.zm", "time" => "3 hours ago", "status" => "info"]
]);

// Handle API requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_state') {
    // Read passengers, drivers, and admins tables and combine them into frontend users state
    $users = [];
    
    // 1. Admins
    $res = $conn->query("SELECT * FROM admins");
    while ($row = $res->fetch_assoc()) {
        $row['role'] = 'Admin';
        $row['bookings'] = 0;
        $row['spent'] = 0.00;
        $users[] = $row;
    }

    // 2. Passengers
    $res = $conn->query("SELECT * FROM passengers");
    while ($row = $res->fetch_assoc()) {
        $row['role'] = 'Passenger';
        $row['bookings'] = intval($row['bookings']);
        $row['spent'] = floatval($row['spent']);
        $users[] = $row;
    }

    // 3. Drivers
    $res = $conn->query("SELECT * FROM drivers");
    while ($row = $res->fetch_assoc()) {
        $row['role'] = 'Operator';
        $row['bookings'] = 0;
        $row['spent'] = 0.00;
        $users[] = $row;
    }

    $buses = [];
    $res = $conn->query("SELECT * FROM buses");
    while ($row = $res->fetch_assoc()) {
        $row['capacity'] = intval($row['capacity']);
        $buses[] = $row;
    }

    $routes = [];
    $res = $conn->query("SELECT * FROM routes");
    while ($row = $res->fetch_assoc()) {
        $row['distance'] = intval($row['distance']);
        $row['fare'] = floatval($row['fare']);
        $routes[] = $row;
    }

    $schedules = [];
    $res = $conn->query("SELECT * FROM schedules");
    while ($row = $res->fetch_assoc()) {
        $row['bookedSeats'] = json_decode($row['bookedSeats']);
        if ($row['bookedSeats'] === null) {
            $row['bookedSeats'] = [];
        }
        $schedules[] = $row;
    }

    $bookings = [];
    $res = $conn->query("SELECT * FROM bookings");
    while ($row = $res->fetch_assoc()) {
        $row['amount'] = floatval($row['amount']);
        $bookings[] = $row;
    }

    $transactions = [];
    $res = $conn->query("SELECT * FROM transactions");
    while ($row = $res->fetch_assoc()) {
        $row['amount'] = floatval($row['amount']);
        $transactions[] = $row;
    }

    $recentActivity = [];
    $res = $conn->query("SELECT * FROM recent_activity ORDER BY id DESC");
    while ($row = $res->fetch_assoc()) {
        $recentActivity[] = [
            "desc" => $row['desc'],
            "user" => $row['user'],
            "time" => $row['time'],
            "status" => $row['status']
        ];
    }

    echo json_encode([
        "users" => $users,
        "buses" => $buses,
        "routes" => $routes,
        "schedules" => $schedules,
        "bookings" => $bookings,
        "transactions" => $transactions,
        "recentActivity" => $recentActivity
    ]);
    exit;
}

if ($action === 'save_state') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input || !isset($input['key']) || !isset($input['data'])) {
        echo json_encode(["status" => "error", "message" => "Invalid payload"]);
        exit;
    }

    $key = $input['key'];
    $data = $input['data'];

    if ($key === 'users') {
        // Clear all three tables
        $conn->query("TRUNCATE TABLE admins");
        $conn->query("TRUNCATE TABLE passengers");
        $conn->query("TRUNCATE TABLE drivers");

        // Distribute to passengers, drivers, and admins tables
        foreach ($data as $item) {
            $role = isset($item['role']) ? $item['role'] : 'Passenger';
            
            if ($role === 'Admin') {
                $cols = ["uid", "firstName", "lastName", "email", "password", "status", "memberSince"];
                $vals = [];
                foreach ($cols as $col) {
                    $val = isset($item[$col]) ? $item[$col] : '';
                    $vals[] = "'" . $conn->real_escape_string($val) . "'";
                }
                $insSql = "INSERT INTO admins (" . implode(", ", array_map(function($c){return "`$c`";}, $cols)) . ") VALUES (" . implode(", ", $vals) . ")";
                $conn->query($insSql);
            } else if ($role === 'Operator') {
                $cols = ["uid", "firstName", "lastName", "email", "password", "staffId", "status", "memberSince"];
                $vals = [];
                foreach ($cols as $col) {
                    $val = isset($item[$col]) ? $item[$col] : '';
                    $vals[] = "'" . $conn->real_escape_string($val) . "'";
                }
                $insSql = "INSERT INTO drivers (" . implode(", ", array_map(function($c){return "`$c`";}, $cols)) . ") VALUES (" . implode(", ", $vals) . ")";
                $conn->query($insSql);
            } else {
                // Passenger
                $cols = ["uid", "firstName", "lastName", "email", "password", "status", "bookings", "spent", "phone", "nationality", "passport", "memberSince"];
                $vals = [];
                foreach ($cols as $col) {
                    $val = isset($item[$col]) ? $item[$col] : '';
                    if ($col === 'spent') $val = floatval($val);
                    if ($col === 'bookings') $val = intval($val);
                    $vals[] = "'" . $conn->real_escape_string($val) . "'";
                }
                $insSql = "INSERT INTO passengers (" . implode(", ", array_map(function($c){return "`$c`";}, $cols)) . ") VALUES (" . implode(", ", $vals) . ")";
                $conn->query($insSql);
            }
        }
        echo json_encode(["status" => "success"]);
        exit;
    }

    $tableMap = [
        "buses" => "buses",
        "routes" => "routes",
        "schedules" => "schedules",
        "bookings" => "bookings",
        "transactions" => "transactions",
        "recentActivity" => "recent_activity"
    ];

    if (!isset($tableMap[$key])) {
        echo json_encode(["status" => "error", "message" => "Unknown table key: " . $key]);
        exit;
    }

    $table = $tableMap[$key];
    $conn->query("TRUNCATE TABLE $table");

    foreach ($data as $item) {
        $columns = [];
        $values = [];
        foreach ($item as $col => $val) {
            $dbCol = $col;
            if (is_array($val)) {
                $val = json_encode($val);
            }
            $columns[] = "`$conn->real_escape_string($dbCol)`";
            $values[] = "'" . $conn->real_escape_string($val) . "'";
        }

        if (count($columns) > 0) {
            $insSql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
            if (!$conn->query($insSql)) {
                echo json_encode(["status" => "error", "message" => "Insert failed for table $table: " . $conn->error]);
                exit;
            }
        }
    }

    echo json_encode(["status" => "success"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Action not found"]);
?>
