<?php
session_start();

// Database configuration
$host = 'localhost';
$db   = 'your_database_name'; // Replace with your actual database name
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle mark as read/unread
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $newStatus = $_POST['status'] === 'Read' ? 'Unread' : 'Read';
    
    $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle delete
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all messages
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

// Count unread
$unreadCount = count(array_filter($messages, fn($m) => $m['status'] === 'Unread'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages Inbox</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px; 
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
        }
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { 
            color: #333; 
            display: flex; 
            align-items: center; 
            gap: 10px;
            font-size: 1.8em;
        }
        .badge { 
            background: #d9534f; 
            color: white; 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-size: 0.6em;
            font-weight: 600;
        }
        .table-wrapper { 
            overflow-x: auto; 
            background: #fff; 
            border-radius: 0 0 10px 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 15px; 
            text-align: left; 
        }
        th { 
            background: #2c3e50; 
            color: #fff; 
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        tbody tr {
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        tbody tr:hover { 
            background-color: #f8f9fa; 
        }
        tbody tr:last-child { 
            border-bottom: none; 
        }
        
        .status-unread { 
            color: #d9534f; 
            font-weight: bold; 
        }
        .status-read { 
            color: #5cb85c; 
        }
        
        .message-cell { 
            max-width: 300px; 
            max-height: 60px; 
            overflow: hidden; 
            text-overflow: ellipsis;
            line-height: 1.5;
        }
        
        .email-link {
            color: #667eea;
            text-decoration: none;
        }
        .email-link:hover {
            text-decoration: underline;
        }
        
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        button {
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85em;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        button:hover { 
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn-status {
            background: #5bc0de;
            color: white;
        }
        
        .btn-status:hover {
            background: #46b8da;
        }
        
        .btn-delete {
            background: #d9534f;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c9302c;
        }
        
        .empty-state {
            padding: 80px 20px;
            text-align: center;
            color: #999;
        }
        
        .empty-state p {
            font-size: 1.2em;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .message-cell { max-width: 150px; }
            th, td { padding: 10px; font-size: 0.9em; }
            .actions { flex-direction: column; }
            button { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>
            📬 Contact Messages
            <?php if ($unreadCount > 0): ?>
                <span class="badge"><?= $unreadCount ?> Unread</span>
            <?php endif; ?>
        </h2>
    </div>

    <div class="table-wrapper">
        <?php if (empty($messages)): ?>
            <div class="empty-state">
                📭
                <p>No messages yet.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($msg['id']) ?></strong></td>
                            <td><?= htmlspecialchars($msg['name']) ?></td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="email-link">
                                    <?= htmlspecialchars($msg['email']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($msg['subject']) ?></td>
                            <td class="message-cell">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                            </td>
                            <td><?= date('M d, Y g:i A', strtotime($msg['created_at'])) ?></td>
                            <td>
                                <span class="<?= $msg['status'] == 'Unread' ? 'status-unread' : 'status-read' ?>">
                                    <?= htmlspecialchars($msg['status'] ?? 'Unread') ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $msg['status'] ?? 'Unread' ?>">
                                        <button type="submit" name="update_status" class="btn-status">
                                            <?= ($msg['status'] ?? 'Unread') == 'Unread' ? '✓ Mark Read' : '↺ Mark Unread' ?>
                                        </button>
                                    </form>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                        <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                        <button type="submit" name="delete" class="btn-delete">🗑 Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>