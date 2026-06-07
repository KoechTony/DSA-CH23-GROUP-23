<?php
require_once 'db_connect.php';
require_once 'algorithms.php'; 

// =========================================================
// BACKEND ACTION PROCESSING (Queue & Stack Logic)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete' && isset($_POST['report_id']) && isset($_POST['post_id'])) {
        $report_id = intval($_POST['report_id']);
        $post_id = intval($_POST['post_id']);
        $conn->query("UPDATE Reports_Queue SET status = 'reviewed' WHERE report_id = $report_id");
        $conn->query("UPDATE Posts SET post_status = 'deleted' WHERE post_id = $post_id");
        $conn->query("INSERT INTO Audit_Stack (post_id, report_id, admin_action) VALUES ($post_id, $report_id, 'deleted_post')");
    }

    if ($action === 'dismiss' && isset($_POST['report_id']) && isset($_POST['post_id'])) {
        $report_id = intval($_POST['report_id']);
        $post_id = intval($_POST['post_id']);
        $conn->query("UPDATE Reports_Queue SET status = 'reviewed' WHERE report_id = $report_id");
        $conn->query("INSERT INTO Audit_Stack (post_id, report_id, admin_action) VALUES ($post_id, $report_id, 'dismissed_report')");
    }

    if ($action === 'undo') {
        $top_stack_result = $conn->query("SELECT * FROM Audit_Stack ORDER BY action_time DESC LIMIT 1");
        if ($top_stack_result->num_rows > 0) {
            $last_action = $top_stack_result->fetch_assoc();
            $audit_id = $last_action['audit_id'];
            $post_id = $last_action['post_id'];
            $report_id = $last_action['report_id'];
            $admin_action = $last_action['admin_action'];

            $conn->query("DELETE FROM Audit_Stack WHERE audit_id = $audit_id");
            if ($admin_action === 'deleted_post') {
                $conn->query("UPDATE Posts SET post_status = 'active' WHERE post_id = $post_id");
            }
            $conn->query("UPDATE Reports_Queue SET status = 'pending' WHERE report_id = $report_id");
        }
    }

    header("Location: admin_dashboard.php");
    exit();
}

// =========================================================
// GRAPH NETWORK INITIALIZATION & SEEDING
// =========================================================
$networkGraph = new SocialGraph();

// Add mock social connections (Edges) for graph simulation
$networkGraph->addRelationship('alice_w', 'charlie_dev'); // Alice and Charlie are friends
$networkGraph->addRelationship('bob_ke', 'charlie_dev');  // Bob and Charlie are friends
// Note: alice_w and bob_ke have NO direct link (unconnected nodes)

// =========================================================
// DATA RETRIEVAL & PROCESSING
// =========================================================

// 1. Fetch Queue data from DB (Mapping fields for our graph logic)
$queue_query = "SELECT r.report_id, r.post_id, p.content, u.username, r.reported_at, 
                (SELECT username FROM Users WHERE user_id = r.reported_by_user_id) AS reporter_username
                FROM Reports_Queue r
                JOIN Posts p ON r.post_id = p.post_id
                JOIN Users u ON p.user_id = u.user_id
                WHERE r.status = 'pending' AND p.post_status = 'active'"; 
$queue_result = $conn->query($queue_query);

$reports_array = array();
$priorityHeap = new MaxHeap(); 

if ($queue_result->num_rows > 0) {
    while($row = $queue_result->fetch_assoc()) {
        
        // Calculate priority heap score
        $priorityScore = 10; 
        if (stripos($row['content'], 'scam') !== false) $priorityScore += 50;
        if (stripos($row['content'], 'click here') !== false) $priorityScore += 30;
        
        $row['priority'] = $priorityScore;
        $reports_array[] = $row;
        $priorityHeap->insert($row); 
    }
}

$criticalReport = $priorityHeap->extractMax();
$reports_array = mergeSort($reports_array);

// Apply Search Filter
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search_query !== '') {
    $filtered_reports = array();
    foreach ($reports_array as $report) {
        if (stripos($report['username'], $search_query) !== false || stripos($report['content'], $search_query) !== false) {
            $filtered_reports[] = $report;
        }
    }
    $reports_array = $filtered_reports;
}

$stack_result = $conn->query("SELECT * FROM Audit_Stack ORDER BY action_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialNetwork Lite - Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800">

    <nav class="bg-indigo-600 text-white shadow-md px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold tracking-wide">🛡️ SocialNetwork Lite Control Panel</h1>
        <span class="bg-indigo-700 text-xs px-3 py-1.5 rounded-full font-medium">Solo Mode Active</span>
    </nav>

    <!-- Priority Queue Alert Banner -->
    <?php if ($criticalReport): ?>
    <div class="max-w-7xl mx-auto px-6 mt-6">
        <div class="bg-gradient-to-r from-red-500 to-amber-600 text-white p-5 rounded-xl shadow-md flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-white/20 text-xs uppercase px-2 py-0.5 rounded font-bold tracking-wider animate-pulse">🚨 CRITICAL HEAP ALERT</span>
                    <span class="text-xs bg-red-700/50 px-2 py-0.5 rounded font-medium">Priority Weight: <?php echo $criticalReport['priority']; ?></span>
                </div>
                <p class="text-sm font-medium opacity-90">@<?php echo htmlspecialchars($criticalReport['username']); ?> posted high-risk text:</p>
                <p class="text-lg font-bold italic mt-0.5">"<?php echo htmlspecialchars($criticalReport['content']); ?>"</p>
            </div>
            <form method="POST" class="flex gap-2 shrink-0">
                <input type="hidden" name="report_id" value="<?php echo $criticalReport['report_id']; ?>">
                <input type="hidden" name="post_id" value="<?php echo $criticalReport['post_id']; ?>">
                <button type="submit" name="action" value="delete" class="bg-white text-red-700 hover:bg-gray-100 font-bold py-2 px-4 rounded-lg text-sm shadow cursor-pointer transition">Fast Delete</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Search Tool -->
    <div class="max-w-7xl mx-auto px-6 mt-6">
        <form method="GET" class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex gap-3">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="🔍 Search queue by username or keywords..." class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition cursor-pointer">Search</button>
            <?php if ($search_query !== ''): ?>
                <a href="admin_dashboard.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <main class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT PANEL: Moderation Queue -->
        <section class="lg:col-span-2 space-y-4">
            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                <h2 class="text-lg font-semibold text-gray-700">📥 Pending Moderation Queue</h2>
                <div class="flex gap-2">
                    <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-1 rounded-full font-semibold">Sorted: MergeSort</span>
                    <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-semibold">FIFO Logic</span>
                </div>
            </div>

            <?php 
            if (count($reports_array) > 0) {
                foreach ($reports_array as $row) {
                    // RUN GRAPH ANALYSIS: Check relationship between reporter and author
                    $isFriendReport = $networkGraph->isDirectlyConnected($row['username'], $row['reporter_username']);
                    ?>
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between transition hover:shadow-md">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="font-bold text-gray-900">@<?php echo htmlspecialchars($row['username']); ?></span>
                                <p class="text-xs text-gray-400">Reported by: <span class="underline">@<?php echo htmlspecialchars($row['reporter_username']); ?></span></p>
                            </div>
                            
                            <!-- Dynamic Graph Relationship Evidence Indicator -->
                            <?php if ($isFriendReport): ?>
                                <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2.5 py-1 rounded-full">⚠️ Graph Alert: Direct Friends</span>
                            <?php else: ?>
                                <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2.5 py-1 rounded-full">🌐 Graph Status: Neutral Nodes</span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-gray-600 mb-4 bg-gray-50 p-3 rounded-lg italic">"<?php echo htmlspecialchars($row['content']); ?>"</p>
                        
                        <form method="POST" class="flex gap-2 justify-end">
                            <input type="hidden" name="report_id" value="<?php echo $row['report_id']; ?>">
                            <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>">
                            <button type="submit" name="action" value="dismiss" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg text-sm transition cursor-pointer">Dismiss Report</button>
                            <button type="submit" name="action" value="delete" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition cursor-pointer">Delete Post</button>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='text-gray-500 italic bg-white p-4 rounded-xl border border-gray-200 text-center py-8'>No pending items in queue.</p>";
            }
            ?>
        </section>

        <!-- RIGHT PANEL: Action History Stack -->
        <section class="space-y-4">
            <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                <h2 class="text-lg font-semibold text-gray-700">⏳ Action History Stack</h2>
                <span class="bg-purple-100 text-purple-800 text-xs px-2.5 py-1 rounded-full font-semibold">LIFO Logic</span>
            </div>

            <form method="POST">
                <button type="submit" name="action" value="undo" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow transition duration-200 flex items-center justify-center gap-2 cursor-pointer">↩️ Undo Last Admin Action</button>
            </form>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 space-y-3">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Recent Stack Operations</h3>
                
                <?php
                if ($stack_result->num_rows > 0) {
                    while($stack_row = $stack_result->fetch_assoc()) {
                        ?>
                        <div class="border-l-4 border-indigo-500 pl-3 py-1.5 bg-indigo-50/30 rounded-r-md">
                            <p class="text-sm font-semibold text-gray-800">
                                <?php echo ($stack_row['admin_action'] == 'deleted_post') ? '❌ Deleted Post' : '✅ Dismissed Report'; ?> 
                                #<?php echo $stack_row['post_id']; ?>
                            </p>
                            <p class="text-xs text-gray-400"><?php echo $stack_row['action_time']; ?></p>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='text-xs text-gray-400 italic text-center py-4'>Stack history is empty.</p>";
                }
                ?>
            </div>
        </section>

    </main>

</body>
</html>