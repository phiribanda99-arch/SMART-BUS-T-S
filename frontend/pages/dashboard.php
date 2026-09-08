<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

if (($_SESSION['user_role'] ?? 'passenger') === 'passenger') {
    header('Location: ../../passenger-dashboard.php');
    exit;
}

require __DIR__ . '/../../backend/includes/db_connect.php';

$notice = '';
$noticeType = '';
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $notice = 'Invalid request. Please try again.';
        $noticeType = 'error';
    } else {
        try {
            $action = $_POST['action'] ?? '';
            if ($action === 'add_bus') {
                $registration = trim($_POST['registration_number'] ?? '');
                $make = trim($_POST['make'] ?? '');
                $model = trim($_POST['model'] ?? '');
                $capacity = max(1, (int) ($_POST['seating_capacity'] ?? 0));

                if ($registration === '' || $make === '' || $model === '') {
                    throw new InvalidArgumentException('Registration number, make, and model are required.');
                }

                $exists = $pdo->prepare('SELECT id FROM buses WHERE registration_number = :registration LIMIT 1');
                $exists->execute(['registration' => $registration]);
                if ($exists->fetch()) {
                    throw new InvalidArgumentException('A bus with that registration number already exists.');
                }

                $stmt = $pdo->prepare('INSERT INTO buses (registration_number, make, model, year, bus_type, seating_capacity) VALUES (:registration, :make, :model, :year, :type, :capacity)');
                $stmt->execute([
                    'registration' => $registration,
                    'make' => $make,
                    'model' => $model,
                    'year' => (int) ($_POST['year'] ?? 0) ?: null,
                    'type' => trim($_POST['bus_type'] ?? ''),
                    'capacity' => $capacity
                ]);
                $notice = 'Bus registered successfully.';
                $noticeType = 'success';
            } elseif ($action === 'add_route') {
                $code = trim($_POST['route_code'] ?? '');
                $origin = trim($_POST['origin'] ?? '');
                $destination = trim($_POST['destination'] ?? '');
                $fare = (float) ($_POST['fare'] ?? 0);

                if ($code === '' || $origin === '' || $destination === '') {
                    throw new InvalidArgumentException('Route code, origin, and destination are required.');
                }

                $exists = $pdo->prepare('SELECT id FROM routes WHERE route_code = :code LIMIT 1');
                $exists->execute(['code' => $code]);
                if ($exists->fetch()) {
                    throw new InvalidArgumentException('A route with that code already exists.');
                }

                $stmt = $pdo->prepare('INSERT INTO routes (route_code, origin, origin_district, destination, destination_district, distance_km, estimated_duration, fare) VALUES (:code, :origin, :origin_district, :destination, :destination_district, :distance, :duration, :fare)');
                $stmt->execute([
                    'code' => $code,
                    'origin' => $origin,
                    'origin_district' => trim($_POST['origin_district'] ?? ''),
                    'destination' => $destination,
                    'destination_district' => trim($_POST['destination_district'] ?? ''),
                    'distance' => (float) ($_POST['distance_km'] ?? 0),
                    'duration' => trim($_POST['estimated_duration'] ?? ''),
                    'fare' => $fare
                ]);
                $notice = 'Route created successfully.';
                $noticeType = 'success';
            } elseif ($action === 'add_schedule') {
                $routeId = (int) ($_POST['route_id'] ?? 0);
                $busId = (int) ($_POST['bus_id'] ?? 0);
                $travelDate = $_POST['travel_date'] ?? '';
                $departure = $_POST['departure_time'] ?? '';
                $arrival = $_POST['arrival_time'] ?? '';
                $fare = (float) ($_POST['schedule_fare'] ?? 0);

                if ($routeId <= 0 || $busId <= 0 || $travelDate === '' || $departure === '' || $arrival === '') {
                    throw new InvalidArgumentException('Route, bus, date, departure, and arrival are required.');
                }

                $routeCheck = $pdo->prepare('SELECT id FROM routes WHERE id = :id AND status = "active" LIMIT 1');
                $routeCheck->execute(['id' => $routeId]);
                if (!$routeCheck->fetch()) {
                    throw new InvalidArgumentException('Selected route is not active or does not exist.');
                }

                $busCheck = $pdo->prepare('SELECT id, seating_capacity FROM buses WHERE id = :id AND status <> "Inactive" LIMIT 1');
                $busCheck->execute(['id' => $busId]);
                $busInfo = $busCheck->fetch();
                if (!$busInfo) {
                    throw new InvalidArgumentException('Selected bus is not available.');
                }

                if (strtotime($departure) >= strtotime($arrival)) {
                    throw new InvalidArgumentException('Departure time must be earlier than arrival time.');
                }

                $duplicateCheck = $pdo->prepare('SELECT id FROM schedules WHERE route_id = :route_id AND bus_id = :bus_id AND travel_date = :travel_date AND departure_time = :departure_time LIMIT 1');
                $duplicateCheck->execute([
                    'route_id' => $routeId,
                    'bus_id' => $busId,
                    'travel_date' => $travelDate,
                    'departure_time' => $departure
                ]);
                if ($duplicateCheck->fetch()) {
                    throw new InvalidArgumentException('A schedule with that route, bus, date, and departure time already exists.');
                }

                $stmt = $pdo->prepare('INSERT INTO schedules (route_id, bus_id, travel_date, departure_time, arrival_time, fare, available_seats) SELECT :route, :bus_value, :date, :departure, :arrival, :fare, seating_capacity FROM buses WHERE id = :bus_lookup');
                $stmt->execute([
                    'route' => $routeId,
                    'bus_value' => $busId,
                    'bus_lookup' => $busId,
                    'date' => $travelDate,
                    'departure' => $departure,
                    'arrival' => $arrival,
                    'fare' => $fare
                ]);
                $notice = 'Schedule allocated successfully.';
                $noticeType = 'success';
            }
        } catch (Throwable $exception) {
            $notice = $exception->getMessage();
            $noticeType = 'error';
        }
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
$routes = $pdo->query('SELECT id, route_code, origin, origin_district, destination, destination_district FROM routes WHERE status = "active" ORDER BY route_code')->fetchAll();
$buses = $pdo->query('SELECT id, registration_number, make, model, year, bus_type, seating_capacity, status FROM buses WHERE status <> "Inactive" ORDER BY registration_number')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Smart Bus</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="topbar">
        <div>Smart Bus Admin</div>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <span style="margin-left: 16px;">|</span>
            <a href="../../logout.php">Logout</a>
        </div>
    </div>

    <div class="container dashboard">
        <h2>Dashboard</h2>
        <div class="action-bar">
            <a class="btn primary-button" href="../../modules/admin/management.php">Open Management Modules</a>
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
                <h3>Bus Management</h3>
                <form method="post" class="route-form">
                    <input type="hidden" name="action" value="add_bus">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input name="registration_number" placeholder="Registration number" required>
                    <input name="make" placeholder="Make" required>
                    <input name="model" placeholder="Model" required>
                    <input name="year" type="number" placeholder="Year">
                    <input name="bus_type" placeholder="Bus type">
                    <input name="seating_capacity" type="number" min="1" placeholder="Seats" required>
                    <button class="primary-button" type="submit">Register Bus</button>
                </form>

                <div class="table-wrap">
                    <h4>Registered Buses at Lusaka Intercity Terminal</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Registration</th>
                                <th>Make and Model</th>
                                <th>Type</th>
                                <th>Seats</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($buses as $bus): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($bus['registration_number']); ?></td>
                                    <td><?php echo htmlspecialchars(trim($bus['make'] . ' ' . $bus['model'])); ?><?php if ($bus['year']): ?> (<?php echo (int) $bus['year']; ?>)<?php endif; ?></td>
                                    <td><?php echo htmlspecialchars($bus['bus_type'] ?: 'Standard'); ?></td>
                                    <td><?php echo (int) $bus['seating_capacity']; ?></td>
                                    <td><?php echo htmlspecialchars($bus['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$buses): ?><tr><td colspan="5">No buses have been registered yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel">
                <h3>Create Route</h3>
                <form method="post" class="route-form">
                    <input type="hidden" name="action" value="add_route">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input name="route_code" placeholder="Route code" required>
                    <input name="origin" placeholder="Origin province" required>
                    <input name="origin_district" placeholder="Origin district" required>
                    <input name="destination" placeholder="Destination province" required>
                    <input name="destination_district" placeholder="Destination district" required>
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
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <select name="route_id" required><option value="">Select route</option><?php foreach ($routes as $route): ?><option value="<?php echo (int) $route['id']; ?>"><?php echo htmlspecialchars($route['route_code'] . ' - ' . $route['origin'] . ', ' . $route['origin_district'] . ' to ' . $route['destination'] . ', ' . $route['destination_district']); ?></option><?php endforeach; ?></select>
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

