<?php
session_start();
require __DIR__ . '/includes/db_connect.php';

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (($_SESSION['user_role'] ?? '') !== 'passenger') {
    header('Location: dashboard.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$notice = '';
$noticeType = '';
$ticket = null;
$selectedScheduleId = (int) ($_GET['schedule_id'] ?? 0);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'book') {
    $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
    $seatNumber = trim($_POST['seat_number'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'Mobile Money';
    $allowedMethods = ['Cash', 'Mobile Money', 'Bank', 'Card'];

    if (!$scheduleId || $seatNumber === '' || !in_array($paymentMethod, $allowedMethods, true)) {
        $notice = 'Select a schedule, seat, and valid payment method.';
        $noticeType = 'error';
    } else {
        try {
            $pdo->beginTransaction();
            $scheduleStmt = $pdo->prepare(
                'SELECT s.*, r.route_code, r.origin, r.origin_district, r.destination, r.destination_district
                 FROM schedules s JOIN routes r ON r.id = s.route_id
                 WHERE s.id = :id AND s.status = "scheduled" FOR UPDATE'
            );
            $scheduleStmt->execute(['id' => $scheduleId]);
            $schedule = $scheduleStmt->fetch();

            if (!$schedule || (int) $schedule['available_seats'] < 1) {
                throw new RuntimeException('This schedule is no longer available.');
            }

            $seatStmt = $pdo->prepare(
                'SELECT id FROM bookings WHERE schedule_id = :schedule_id AND seat_number = :seat_number
                 AND booking_status IN ("Pending", "Confirmed") LIMIT 1'
            );
            $seatStmt->execute(['schedule_id' => $scheduleId, 'seat_number' => $seatNumber]);
            if ($seatStmt->fetch()) {
                throw new RuntimeException('That seat has already been booked. Choose another seat.');
            }

            $reference = 'BK-' . strtoupper(bin2hex(random_bytes(4)));
            $bookingStmt = $pdo->prepare(
                'INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, amount, booking_status)
                 VALUES (:reference, :user_id, :schedule_id, :seat_number, :amount, "Confirmed")'
            );
            $bookingStmt->execute([
                'reference' => $reference,
                'user_id' => $userId,
                'schedule_id' => $scheduleId,
                'seat_number' => $seatNumber,
                'amount' => $schedule['fare']
            ]);
            $bookingId = (int) $pdo->lastInsertId();

            $paymentStmt = $pdo->prepare(
                'INSERT INTO payments (booking_id, amount, payment_method, transaction_reference, payment_status)
                 VALUES (:booking_id, :amount, :method, :transaction, "Paid")'
            );
            $paymentStmt->execute([
                'booking_id' => $bookingId,
                'amount' => $schedule['fare'],
                'method' => $paymentMethod,
                'transaction' => 'TX-' . strtoupper(bin2hex(random_bytes(4)))
            ]);

            $ticketNumber = 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
            $ticketStmt = $pdo->prepare(
                'INSERT INTO tickets (booking_id, ticket_number, qr_code) VALUES (:booking_id, :ticket, :qr)'
            );
            $ticketStmt->execute([
                'booking_id' => $bookingId,
                'ticket' => $ticketNumber,
                'qr' => 'SMARTBUS:' . $ticketNumber
            ]);

            $updateStmt = $pdo->prepare(
                'UPDATE schedules SET available_seats = available_seats - 1 WHERE id = :id AND available_seats > 0'
            );
            $updateStmt->execute(['id' => $scheduleId]);

            $historyStmt = $pdo->prepare(
                'INSERT INTO travel_history (booking_id, user_id, schedule_id, travel_date, trip_status)
                 VALUES (:booking_id, :user_id, :schedule_id, :travel_date, "completed")'
            );
            $historyStmt->execute([
                'booking_id' => $bookingId,
                'user_id' => $userId,
                'schedule_id' => $scheduleId,
                'travel_date' => $schedule['travel_date']
            ]);

            $pdo->commit();
            $notice = 'Payment successful. Your booking and ticket have been created.';
            $noticeType = 'success';
            $ticket = [
                'ticket_number' => $ticketNumber,
                'reference' => $reference,
                'origin' => $schedule['origin'],
                'destination' => $schedule['destination'],
                'travel_date' => $schedule['travel_date'],
                'departure_time' => $schedule['departure_time'],
                'seat_number' => $seatNumber,
                'fare' => $schedule['fare'],
                'payment_method' => $paymentMethod
            ];
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $notice = $exception->getMessage();
            $noticeType = 'error';
        }
    }
}

$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$fromDistrict = trim($_GET['from_district'] ?? '');
$toDistrict = trim($_GET['to_district'] ?? '');
$date = trim($_GET['travel_date'] ?? '');
$busId = (int) ($_GET['bus_id'] ?? 0);
$busList = $pdo->query('SELECT id, registration_number, make, model FROM buses WHERE status <> "Inactive" ORDER BY registration_number')->fetchAll();
$schedules = [];
if ($from !== '' || $to !== '' || $fromDistrict !== '' || $toDistrict !== '' || $date !== '' || $busId > 0) {
    $stmt = $pdo->prepare(
        'SELECT s.id, s.travel_date, s.departure_time, s.arrival_time, s.fare, s.available_seats,
            r.route_code, r.origin, r.origin_district, r.destination, r.destination_district, b.registration_number, b.make, b.model
         FROM schedules s
         JOIN routes r ON r.id = s.route_id
         JOIN buses b ON b.id = s.bus_id
         WHERE s.status = "scheduled"
           AND (:from = "" OR r.origin LIKE :from_like)
           AND (:from_district = "" OR r.origin_district LIKE :from_district_like)
           AND (:to = "" OR r.destination LIKE :to_like)
           AND (:to_district = "" OR r.destination_district LIKE :to_district_like)
           AND (:travel_date = "" OR s.travel_date = :travel_date)
           AND (:bus_id = 0 OR s.bus_id = :bus_id)
         ORDER BY s.travel_date, s.departure_time'
    );
    $stmt->execute([
        'from' => $from,
        'from_like' => '%' . $from . '%',
        'from_district' => $fromDistrict,
        'from_district_like' => '%' . $fromDistrict . '%',
        'to' => $to,
        'to_like' => '%' . $to . '%',
        'to_district' => $toDistrict,
        'to_district_like' => '%' . $toDistrict . '%',
        'travel_date' => $date,
        'bus_id' => $busId
    ]);
    $schedules = $stmt->fetchAll();
}

$historyStmt = $pdo->prepare(
    'SELECT r.origin, r.destination, s.travel_date, b.seat_number, b.booking_reference, b.booking_status
     FROM travel_history h
     JOIN bookings b ON b.id = h.booking_id
     JOIN schedules s ON s.id = b.schedule_id
     JOIN routes r ON r.id = s.route_id
     WHERE h.user_id = :user_id ORDER BY h.recorded_at DESC LIMIT 10'
);
$historyStmt->execute(['user_id' => $userId]);
$history = $historyStmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Passenger Dashboard - Smart Bus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="passenger-page">
    <div class="topbar">
        <div>Smart Bus Passenger</div>
        <div>Welcome, <?php echo e($_SESSION['user_name'] ?? 'Passenger'); ?> <span style="margin-left: 16px;">|</span> <a href="logout.php">Logout</a></div>
    </div>
    <div class="container dashboard">
        <h2>Passenger Dashboard</h2>
        <?php if ($notice): ?><div class="alert <?php echo $noticeType === 'success' ? 'alert-success' : 'alert-error'; ?>"><?php echo e($notice); ?></div><?php endif; ?>

        <div class="panel">
            <h3>Search and Book a Trip</h3>
            <form class="route-form" method="get">
                <div class="form-group"><label for="from">From Province</label><input id="from" name="from" value="<?php echo e($from); ?>" placeholder="Lusaka"></div>
                <div class="form-group"><label for="from_district">From District</label><input id="from_district" name="from_district" value="<?php echo e($fromDistrict); ?>" placeholder="Lusaka District"></div>
                <div class="form-group"><label for="to">To Province</label><input id="to" name="to" value="<?php echo e($to); ?>" placeholder="Copperbelt"></div>
                <div class="form-group"><label for="to_district">To District</label><input id="to_district" name="to_district" value="<?php echo e($toDistrict); ?>" placeholder="Kitwe District"></div>
                <div class="form-group"><label for="travel_date">Travel Date</label><input id="travel_date" name="travel_date" type="date" value="<?php echo e($date); ?>"></div>
                <div class="form-group"><label for="bus_id">Select Bus</label><select id="bus_id" name="bus_id"><option value="0">All available buses</option><?php foreach ($busList as $bus): ?><option value="<?php echo (int) $bus['id']; ?>"<?php echo $busId === (int) $bus['id'] ? ' selected' : ''; ?>><?php echo e($bus['registration_number'] . ' - ' . trim($bus['make'] . ' ' . $bus['model'])); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>&nbsp;</label><button class="primary-button" type="submit">Search Trips</button></div>
            </form>
        </div>

        <?php if ($schedules): ?>
            <div class="panel"><h3>Available Trips</h3><div class="route-list">
                <?php foreach ($schedules as $schedule): ?>
                    <div class="route-card">
                        <h4><?php echo e($schedule['route_code']); ?>: <?php echo e($schedule['origin']); ?>, <?php echo e($schedule['origin_district']); ?> to <?php echo e($schedule['destination']); ?>, <?php echo e($schedule['destination_district']); ?></h4>
                        <p><?php echo e($schedule['travel_date']); ?> | <?php echo e($schedule['departure_time']); ?> - <?php echo e($schedule['arrival_time']); ?></p>
                        <p>Selected bus: <?php echo e($schedule['registration_number'] . ' - ' . trim($schedule['make'] . ' ' . $schedule['model'])); ?> | <?php echo (int) $schedule['available_seats']; ?> seats available</p>
                        <p class="route-fare">K<?php echo number_format((float) $schedule['fare'], 2); ?></p>
                        <?php if ((int) $schedule['available_seats'] > 0): ?>
                            <form method="post" class="booking-form">
                                <input type="hidden" name="action" value="book">
                                <input type="hidden" name="schedule_id" value="<?php echo (int) $schedule['id']; ?>">
                                <label for="seat-<?php echo (int) $schedule['id']; ?>">Seat number</label>
                                <input id="seat-<?php echo (int) $schedule['id']; ?>" name="seat_number" required placeholder="e.g. 12">
                                <label for="payment-<?php echo (int) $schedule['id']; ?>">Payment method</label>
                                <select id="payment-<?php echo (int) $schedule['id']; ?>" name="payment_method"><option>Mobile Money</option><option>Card</option><option>Bank</option><option>Cash</option></select>
                                <button class="primary-button" type="submit">Confirm Booking and Pay</button>
                            </form>
                        <?php else: ?><p class="alert alert-error">Fully booked</p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div></div>
        <?php elseif ($from !== '' || $to !== '' || $date !== ''): ?><div class="panel"><p>No scheduled trips matched your search.</p></div><?php endif; ?>

        <?php if ($ticket): ?><div class="panel"><h3>Generated Ticket</h3><div class="ticket-card">
            <p><strong>Ticket:</strong> <?php echo e($ticket['ticket_number']); ?></p><p><strong>Booking:</strong> <?php echo e($ticket['reference']); ?></p>
            <p><strong>Journey:</strong> <?php echo e($ticket['origin']); ?> to <?php echo e($ticket['destination']); ?></p><p><strong>Date and time:</strong> <?php echo e($ticket['travel_date']); ?> <?php echo e($ticket['departure_time']); ?></p>
            <p><strong>Seat:</strong> <?php echo e($ticket['seat_number']); ?> | <strong>Paid:</strong> K<?php echo number_format((float) $ticket['fare'], 2); ?> via <?php echo e($ticket['payment_method']); ?></p>
        </div></div><?php endif; ?>

        <div class="panel"><h3>Travel History</h3><table><thead><tr><th>Booking</th><th>Journey</th><th>Date</th><th>Seat</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($history as $item): ?><tr><td><?php echo e($item['booking_reference']); ?></td><td><?php echo e($item['origin']); ?> to <?php echo e($item['destination']); ?></td><td><?php echo e($item['travel_date']); ?></td><td><?php echo e($item['seat_number']); ?></td><td><?php echo e($item['booking_status']); ?></td></tr><?php endforeach; ?>
            <?php if (!$history): ?><tr><td colspan="5">No travel history yet.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
</body>
</html>
