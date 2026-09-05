<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (($_SESSION['user_role'] ?? 'passenger') === 'passenger') {
    header('Location: passenger-dashboard.php');
    exit;
}

require __DIR__ . '/includes/db_connect.php';

$notice = '';
$noticeType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'add_bus') {
            $stmt = $pdo->prepare('INSERT INTO buses (registration_number, make, model, year, bus_type, seating_capacity) VALUES (:registration, :make, :model, :year, :type, :capacity)');
            $stmt->execute([
                'registration' => trim($_POST['registration_number'] ?? ''),
                'make' => trim($_POST['make'] ?? ''),
                'model' => trim($_POST['model'] ?? ''),
                'year' => (int) ($_POST['year'] ?? 0) ?: null,
                'type' => trim($_POST['bus_type'] ?? ''),
                'capacity' => (int) ($_POST['seating_capacity'] ?? 0)
            ]);
            $notice = 'Bus registered successfully.';
            $noticeType = 'success';
        } elseif ($action === 'add_route') {
            $stmt = $pdo->prepare('INSERT INTO routes (route_code, origin, destination, distance_km, estimated_duration, fare) VALUES (:code, :origin, :destination, :distance, :duration, :fare)');
            $stmt->execute([
                'code' => trim($_POST['route_code'] ?? ''),
                'origin' => trim($_POST['origin'] ?? ''),
                'destination' => trim($_POST['destination'] ?? ''),
                'distance' => (float) ($_POST['distance_km'] ?? 0),
                'duration' => trim($_POST['estimated_duration'] ?? ''),
                'fare' => (float) ($_POST['fare'] ?? 0)
            ]);
            $notice = 'Route created successfully.';
            $noticeType = 'success';
        } elseif ($action === 'add_schedule') {
            $stmt = $pdo->prepare('INSERT INTO schedules (route_id, bus_id, travel_date, departure_time, arrival_time, fare, available_seats) SELECT :route, :bus_value, :date, :departure, :arrival, :fare, seating_capacity FROM buses WHERE id = :bus_lookup');
            $stmt->execute([
                'route' => (int) $_POST['route_id'],
                'bus_value' => (int) $_POST['bus_id'],
                'bus_lookup' => (int) $_POST['bus_id'],
                'date' => $_POST['travel_date'],
                'departure' => $_POST['departure_time'],
                'arrival' => $_POST['arrival_time'],
                'fare' => (float) $_POST['schedule_fare']
            ]);
            $notice = 'Schedule allocated successfully.';
            $noticeType = 'success';
        }
    } catch (PDOException $exception) {
        $notice = 'Could not save the record. Check required fields and unique values.';
        $noticeType = 'error';
    }
}

$summary = [
    'routes' => 0,
    'buses' => 0,
    'drivers' => 0,
    'bookings' => 0,
];

foreach ($summary as $table => $value) {
    $stmt = $pdo->query('SELECT COUNT(*) AS total FROM ' . $table);
    $row = $stmt->fetch();
    $summary[$table] = (int) ($row['total'] ?? 0);
}

$schedules = $pdo->query(
    'SELECT s.id, r.route_code, r.origin, r.destination,
            CONCAT(s.travel_date, " ", s.departure_time) AS departure_datetime,
            s.available_seats, s.fare
     FROM schedules s
     JOIN routes r ON r.id = s.route_id
     ORDER BY s.travel_date ASC, s.departure_time ASC
     LIMIT 5'
)->fetchAll();
$routes = $pdo->query('SELECT id, route_code, origin, destination FROM routes WHERE status = "active" ORDER BY route_code')->fetchAll();
$buses = $pdo->query('SELECT id, registration_number, seating_capacity FROM buses WHERE status <> "Inactive" ORDER BY registration_number')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Smart Bus</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="topbar">
        <div>Smart Bus Admin</div>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <span style="margin-left: 16px;">|</span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container dashboard">
        <h2>Dashboard</h2>
        <div class="action-bar">
            <a class="btn primary-button" href="modules/admin/management.php">Open Management Modules</a>
        </div>
        <?php if ($notice): ?><div class="alert <?php echo $noticeType === 'success' ? 'alert-success' : 'alert-error'; ?>"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Routes</h3>
                <strong><?php echo $summary['routes']; ?></strong>
            </div>
            <div class="stat-card">
                <h3>Buses</h3>
                <strong><?php echo $summary['buses']; ?></strong>
            </div>
            <div class="stat-card">
                <h3>Drivers</h3>
                <strong><?php echo $summary['drivers']; ?></strong>
            </div>
            <div class="stat-card">
                <h3>Bookings</h3>
                <strong><?php echo $summary['bookings']; ?></strong>
            </div>
        </div>

        <div class="management-grid">
            <div class="panel">
                <h3>Register Bus</h3>
                <form method="post" class="route-form">
                    <input type="hidden" name="action" value="add_bus">
                    <input name="registration_number" placeholder="Registration number" required>
                    <input name="make" placeholder="Make" required>
                    <input name="model" placeholder="Model" required>
                    <input name="year" type="number" placeholder="Year">
                    <input name="bus_type" placeholder="Bus type">
                    <input name="seating_capacity" type="number" min="1" placeholder="Seats" required>
                    <button class="primary-button" type="submit">Register Bus</button>
                </form>
            </div>
            <div class="panel">
                <h3>Create Route</h3>
                <form method="post" class="route-form">
                    <input type="hidden" name="action" value="add_route">
                    <input name="route_code" placeholder="Route code" required>
                    <input name="origin" placeholder="Origin" required>
                    <input name="destination" placeholder="Destination" required>
                    <input name="distance_km" type="number" step="0.01" placeholder="Distance km">
                    <input name="estimated_duration" placeholder="Duration">
                    <input name="fare" type="number" step="0.01" placeholder="Fare" required>
                    <button class="primary-button" type="submit">Save Route</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <h3>Allocate Route Schedule</h3>
            <form method="post" class="route-form">
                <input type="hidden" name="action" value="add_schedule">
                <select name="route_id" required><option value="">Select route</option><?php foreach ($routes as $route): ?><option value="<?php echo (int) $route['id']; ?>"><?php echo htmlspecialchars($route['route_code'] . ' - ' . $route['origin'] . ' to ' . $route['destination']); ?></option><?php endforeach; ?></select>
                <select name="bus_id" required><option value="">Select bus</option><?php foreach ($buses as $bus): ?><option value="<?php echo (int) $bus['id']; ?>"><?php echo htmlspecialchars($bus['registration_number'] . ' (' . $bus['seating_capacity'] . ' seats)'); ?></option><?php endforeach; ?></select>
                <input name="travel_date" type="date" required>
                <input name="departure_time" type="time" required>
                <input name="arrival_time" type="time" required>
                <input name="schedule_fare" type="number" step="0.01" placeholder="Fare" required>
                <button class="primary-button" type="submit">Allocate Schedule</button>
            </form>
        </div>

        <div class="panel">
            <h3>Upcoming Schedules</h3>
            <table>
                <thead>
                    <tr>
                        <th>Route</th>
                        <th>Origin → Destination</th>
                        <th>Departure</th>
                        <th>Seats Left</th>
                        <th>Fare</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($schedules): ?>
                        <?php foreach ($schedules as $schedule): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($schedule['route_code']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['origin']); ?> → <?php echo htmlspecialchars($schedule['destination']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['departure_datetime']); ?></td>
                                <td><?php echo (int) $schedule['available_seats']; ?></td>
                                <td>$<?php echo number_format((float) $schedule['fare'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No schedules found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
