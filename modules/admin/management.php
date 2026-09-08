<?php
session_start();
require __DIR__ . '/../../includes/db_connect.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'operator'], true)) {
    header('Location: ../../index.php');
    exit;
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

$module = $_GET['module'] ?? 'overview';
$allowedModules = ['overview', 'users', 'buses', 'routes', 'schedules', 'bookings', 'payments'];
if (!in_array($module, $allowedModules, true)) {
    $module = 'overview';
}
$notice = '';
$noticeType = 'success';

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

                $stmt = $pdo->prepare('INSERT INTO buses (registration_number, make, model, year, bus_type, seating_capacity, status) VALUES (:registration, :make, :model, :year, :type, :capacity, :status)');
                $stmt->execute([
                    'registration' => $registration,
                    'make' => $make,
                    'model' => $model,
                    'year' => (int) ($_POST['year'] ?? 0) ?: null,
                    'type' => trim($_POST['bus_type'] ?? ''),
                    'capacity' => $capacity,
                    'status' => $_POST['status'] ?? 'Available'
                ]);
                $module = 'buses';
                $notice = 'Bus added successfully.';
            } elseif ($action === 'add_route') {
                $code = trim($_POST['route_code'] ?? '');
                $origin = trim($_POST['origin'] ?? '');
                $destination = trim($_POST['destination'] ?? '');

                if ($code === '' || $origin === '' || $destination === '') {
                    throw new InvalidArgumentException('Route code, origin, and destination are required.');
                }

                $exists = $pdo->prepare('SELECT id FROM routes WHERE route_code = :code LIMIT 1');
                $exists->execute(['code' => $code]);
                if ($exists->fetch()) {
                    throw new InvalidArgumentException('A route with that code already exists.');
                }

                $stmt = $pdo->prepare('INSERT INTO routes (route_code, origin, origin_district, destination, destination_district, distance_km, estimated_duration, fare, status) VALUES (:code, :origin, :origin_district, :destination, :destination_district, :distance, :duration, :fare, :status)');
                $stmt->execute([
                    'code' => $code,
                    'origin' => $origin,
                    'origin_district' => trim($_POST['origin_district'] ?? ''),
                    'destination' => $destination,
                    'destination_district' => trim($_POST['destination_district'] ?? ''),
                    'distance' => (float) ($_POST['distance_km'] ?? 0),
                    'duration' => trim($_POST['estimated_duration'] ?? ''),
                    'fare' => (float) ($_POST['fare'] ?? 0),
                    'status' => $_POST['status'] ?? 'active'
                ]);
                $module = 'routes';
                $notice = 'Route added successfully.';
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

                $stmt = $pdo->prepare('INSERT INTO schedules (route_id, bus_id, travel_date, departure_time, arrival_time, fare, available_seats, status) SELECT :route, id, :date, :departure, :arrival, :fare, seating_capacity, :status FROM buses WHERE id = :bus');
                $stmt->execute([
                    'route' => $routeId,
                    'bus' => $busId,
                    'date' => $travelDate,
                    'departure' => $departure,
                    'arrival' => $arrival,
                    'fare' => $fare,
                    'status' => $_POST['status'] ?? 'scheduled'
                ]);
                $module = 'schedules';
                $notice = 'Schedule added successfully.';
            } elseif ($action === 'update_user_status') {
                $stmt = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
                $stmt->execute(['status' => $_POST['status'], 'id' => (int) $_POST['user_id']]);
                $module = 'users';
                $notice = 'User status updated.';
            } elseif ($action === 'update_payment_status') {
                $stmt = $pdo->prepare('UPDATE payments SET payment_status = :status WHERE id = :id');
                $stmt->execute(['status' => $_POST['payment_status'], 'id' => (int) $_POST['payment_id']]);
                $module = 'payments';
                $notice = 'Payment status updated.';
            }
        } catch (Throwable $exception) {
            $notice = $exception->getMessage();
            $noticeType = 'error';
        }
    }
}

$counts = [];
foreach (['users', 'buses', 'routes', 'schedules', 'bookings', 'payments'] as $table) {
    $counts[$table] = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
}
$users = $module === 'users' ? $pdo->query('SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC')->fetchAll() : [];
$buses = $pdo->query('SELECT id, registration_number, make, model, seating_capacity, status FROM buses ORDER BY registration_number')->fetchAll();
$routes = $pdo->query('SELECT id, route_code, origin, origin_district, destination, destination_district, distance_km, fare, status FROM routes ORDER BY route_code')->fetchAll();
$schedules = $pdo->query('SELECT s.id, s.travel_date, s.departure_time, s.arrival_time, s.fare, s.available_seats, s.status, r.route_code, b.registration_number FROM schedules s JOIN routes r ON r.id = s.route_id JOIN buses b ON b.id = s.bus_id ORDER BY s.travel_date, s.departure_time')->fetchAll();
$bookings = $module === 'bookings' ? $pdo->query('SELECT b.id, b.booking_reference, u.full_name, r.route_code, s.travel_date, b.seat_number, b.amount, b.booking_status FROM bookings b JOIN users u ON u.id = b.user_id JOIN schedules s ON s.id = b.schedule_id JOIN routes r ON r.id = s.route_id ORDER BY b.booked_at DESC')->fetchAll() : [];
$payments = $module === 'payments' ? $pdo->query('SELECT p.id, p.amount, p.payment_method, p.transaction_reference, p.payment_status, p.payment_date, b.booking_reference, u.full_name FROM payments p JOIN bookings b ON b.id = p.booking_id JOIN users u ON u.id = b.user_id ORDER BY p.payment_date DESC')->fetchAll() : [];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Management Modules - Smart Bus</title>
    <link rel="stylesheet" href="../../assets/style.css">
</head>

<body class="admin-page">
    <div class="topbar"><strong>Smart Bus Management</strong><span>Welcome, <?php echo h($_SESSION['user_name'] ?? 'Staff'); ?> | <a href="../../dashboard.php">Dashboard</a> | <a href="../../logout.php">Logout</a></span></div>
    <div class="container dashboard">
        <h2>Operations Modules</h2>
        <?php if ($notice): ?><div class="alert <?php echo $noticeType === 'success' ? 'alert-success' : 'alert-error'; ?>"><?php echo h($notice); ?></div><?php endif; ?>
        <div class="action-bar">
            <?php foreach ($allowedModules as $item): ?><a class="btn <?php echo $module === $item ? 'primary-button' : 'btn-secondary'; ?>" href="?module=<?php echo h($item); ?>"><?php echo ucfirst($item); ?></a><?php endforeach; ?>
        </div>

        <?php if ($module === 'overview'): ?>
            <div class="stats-grid">
                <?php foreach ($counts as $label => $count): ?><div class="stat-card">
                        <h3><?php echo ucfirst($label); ?></h3><strong><?php echo $count; ?></strong>
                    </div><?php endforeach; ?>
            </div>
            <div class="panel">
                <h3>Available modules</h3>
                <p>User registration and account status, bus fleet management, route and schedule planning, booking oversight, and payment tracking are available from the navigation above.</p>
            </div>
        <?php elseif ($module === 'users'): ?>
            <div class="panel">
                <h3>User Registration and Authentication</h3>
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr><?php foreach ($users as $user): ?><tr>
                            <td><?php echo h($user['full_name']); ?></td>
                            <td><?php echo h($user['email']); ?></td>
                            <td><?php echo h($user['role']); ?></td>
                            <td><?php echo h($user['status']); ?></td>
                            <td>
                                <form method="post"><input type="hidden" name="action" value="update_user_status"><input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><select name="status">
                                        <option <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>active</option>
                                        <option <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>inactive</option>
                                    </select><button type="submit">Save</button></form>
                            </td>
                        </tr><?php endforeach; ?>
                </table>
            </div>
        <?php elseif ($module === 'buses'): ?>
            <div class="panel">
                <h3>Add Bus</h3>
                <form method="post" class="route-form"><input type="hidden" name="action" value="add_bus"><input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>"><input name="registration_number" placeholder="Registration number" required><input name="make" placeholder="Make" required><input name="model" placeholder="Model" required><input name="year" type="number" placeholder="Year"><input name="bus_type" placeholder="Bus type"><input name="seating_capacity" type="number" min="1" placeholder="Seats" required><select name="status">
                        <option>Available</option>
                        <option>Assigned</option>
                        <option>On Trip</option>
                        <option>Maintenance</option>
                        <option>Inactive</option>
                    </select><button type="submit">Add Bus</button></form>
            </div>
            <div class="panel">
                <h3>Fleet</h3>
                <table>
                    <tr>
                        <th>Registration</th>
                        <th>Vehicle</th>
                        <th>Seats</th>
                        <th>Status</th>
                    </tr><?php foreach ($buses as $bus): ?><tr>
                            <td><?php echo h($bus['registration_number']); ?></td>
                            <td><?php echo h($bus['make'] . ' ' . $bus['model']); ?></td>
                            <td><?php echo (int) $bus['seating_capacity']; ?></td>
                            <td><?php echo h($bus['status']); ?></td>
                        </tr><?php endforeach; ?>
                </table>
            </div>
        <?php elseif ($module === 'routes'): ?>
            <div class="panel">
                <h3>Add Route</h3>
                <form method="post" class="route-form"><input type="hidden" name="action" value="add_route"><input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>"><input name="route_code" placeholder="Route code" required><input name="origin" placeholder="Origin" required><input name="origin_district" placeholder="Origin district" required><input name="destination" placeholder="Destination" required><input name="destination_district" placeholder="Destination district" required><input name="distance_km" type="number" step="0.01" placeholder="Distance km"><input name="estimated_duration" placeholder="Duration"><input name="fare" type="number" step="0.01" placeholder="Fare" required><select name="status">
                        <option>active</option>
                        <option>inactive</option>
                    </select><button type="submit">Add Route</button></form>
            </div>
            <div class="panel">
                <h3>Routes</h3>
                <table>
                    <tr>
                        <th>Code</th>
                        <th>Journey</th>
                        <th>Distance</th>
                        <th>Fare</th>
                        <th>Status</th>
                    </tr><?php foreach ($routes as $route): ?><tr>
                            <td><?php echo h($route['route_code']); ?></td>
                            <td><?php echo h($route['origin'] . ' (' . $route['origin_district'] . ') to ' . $route['destination'] . ' (' . $route['destination_district'] . ')'); ?></td>
                            <td><?php echo h($route['distance_km']); ?> km</td>
                            <td>K<?php echo number_format((float) $route['fare'], 2); ?></td>
                            <td><?php echo h($route['status']); ?></td>
                        </tr><?php endforeach; ?>
                </table>
            </div>
        <?php elseif ($module === 'schedules'): ?>
            <div class="panel">
                <h3>Add Schedule</h3>
                <form method="post" class="route-form"><input type="hidden" name="action" value="add_schedule"><input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>"><select name="route_id" required>
                        <option value="">Select route</option><?php foreach ($routes as $route): ?><option value="<?php echo (int) $route['id']; ?>"><?php echo h($route['route_code'] . ' - ' . $route['origin'] . ' to ' . $route['destination']); ?></option><?php endforeach; ?>
                    </select><select name="bus_id" required>
                        <option value="">Select bus</option><?php foreach ($buses as $bus): ?><option value="<?php echo (int) $bus['id']; ?>"><?php echo h($bus['registration_number'] . ' (' . $bus['seating_capacity'] . ' seats)'); ?></option><?php endforeach; ?>
                    </select><input name="travel_date" type="date" required><input name="departure_time" type="time" required><input name="arrival_time" type="time" required><input name="schedule_fare" type="number" step="0.01" placeholder="Fare" required><button type="submit">Add Schedule</button></form>
            </div>
            <div class="panel">
                <h3>Schedules</h3>
                <table>
                    <tr>
                        <th>Route</th>
                        <th>Bus</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Seats</th>
                        <th>Status</th>
                    </tr><?php foreach ($schedules as $schedule): ?><tr>
                            <td><?php echo h($schedule['route_code']); ?></td>
                            <td><?php echo h($schedule['registration_number']); ?></td>
                            <td><?php echo h($schedule['travel_date']); ?></td>
                            <td><?php echo h($schedule['departure_time'] . ' - ' . $schedule['arrival_time']); ?></td>
                            <td><?php echo (int) $schedule['available_seats']; ?></td>
                            <td><?php echo h($schedule['status']); ?></td>
                        </tr><?php endforeach; ?>
                </table>
            </div>
        <?php elseif ($module === 'bookings'): ?>
            <div class="panel">
                <h3>Ticket Booking and Ticket Oversight</h3>
                <table>
                    <tr>
                        <th>Reference</th>
                        <th>Passenger</th>
                        <th>Route</th>
                        <th>Date</th>
                        <th>Seat</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr><?php foreach ($bookings as $booking): ?><tr>
                            <td><?php echo h($booking['booking_reference']); ?></td>
                            <td><?php echo h($booking['full_name']); ?></td>
                            <td><?php echo h($booking['route_code']); ?></td>
                            <td><?php echo h($booking['travel_date']); ?></td>
                            <td><?php echo h($booking['seat_number']); ?></td>
                            <td>K<?php echo number_format((float) $booking['amount'], 2); ?></td>
                            <td><?php echo h($booking['booking_status']); ?></td>
                        </tr><?php endforeach; ?>
                </table>
            </div>
        <?php elseif ($module === 'payments'): ?>
            <div class="panel">
                <h3>Payment Tracking</h3>
                <table>
                    <tr>
                        <th>Booking</th>
                        <th>Passenger</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Transaction</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr><?php foreach ($payments as $payment): ?><tr>
                            <td><?php echo h($payment['booking_reference']); ?></td>
                            <td><?php echo h($payment['full_name']); ?></td>
                            <td>K<?php echo number_format((float) $payment['amount'], 2); ?></td>
                            <td><?php echo h($payment['payment_method']); ?></td>
                            <td><?php echo h($payment['transaction_reference']); ?></td>
                            <td><?php echo h($payment['payment_status']); ?></td>
                            <td>
                                <form method="post"><input type="hidden" name="action" value="update_payment_status"><input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>"><input type="hidden" name="payment_id" value="<?php echo (int) $payment['id']; ?>"><select name="payment_status">
                                        <option>Pending</option>
                                        <option>Paid</option>
                                        <option>Failed</option>
                                        <option>Refunded</option>
                                    </select><button type="submit">Save</button></form>
                            </td>
                        </tr><?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>