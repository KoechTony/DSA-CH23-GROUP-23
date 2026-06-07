<?php
require_once 'db_connect.php';

echo "<h2>🌱 SocialNetwork Lite Database Seeder</h2>";

// 1. Clear out old data to ensure a clean slate reset
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE Audit_Stack");
$conn->query("TRUNCATE TABLE Reports_Queue");
$conn->query("TRUNCATE TABLE Posts");
$conn->query("TRUNCATE TABLE Users");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "✅ Database tables cleared and reset.<br>";

// 2. Insert Users (Nodes) - Removed the email column to perfectly match your database schema
$conn->query("INSERT INTO Users (user_id, username) VALUES 
(1, 'alice_w'),
(2, 'bob_ke'),
(3, 'charlie_dev'),
(4, 'david_99')");
echo "✅ Mock user network nodes provisioned.<br>";

// 3. Insert Posts
$conn->query("INSERT INTO Posts (post_id, user_id, content, post_status) VALUES 
(101, 2, 'This spam post contains links to scam websites click here!', 'active'),
(102, 1, 'I think the assignment deadline is on Friday guys.', 'active'),
(103, 4, 'Win a free iPhone instantly! Click here now to claim your prize!', 'active'),
(104, 3, 'Hey everyone, check out my brand new software layout repository.', 'active')");
echo "✅ Mock post records published.<br>";

// 4. Insert Reports into the Queue (FIFO / Priority Matrix mapping)
$conn->query("INSERT INTO Reports_Queue (report_id, post_id, reported_by_user_id, status, reported_at) VALUES 
(1, 101, 3, 'pending', '2026-06-07 10:00:00'), -- Bob reported by Charlie (Friends in graph)
(2, 102, 4, 'pending', '2026-06-07 11:15:00'), -- Alice reported by David (Neutral nodes)
(3, 103, 1, 'pending', '2026-06-07 09:30:00'), -- David reported by Alice (High Toxicity Scam)
(4, 104, 2, 'pending', '2026-06-07 12:00:00')  -- Charlie reported by Bob (Friends in graph)");
echo "✅ Moderation queue populated with strategic cases.<br>";

echo "<br>🎉 <strong>Database seeding complete!</strong> Your system is primed for a perfect presentation.";
echo "<br>👉 <a href='admin_dashboard.php' style='color: blue; font-weight: bold;'>Go to Admin Dashboard</a>";
?>