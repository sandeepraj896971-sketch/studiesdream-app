<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if(!isset($_SESSION['admin_id'])) { 
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }
    header("Location: login.php"); 
    exit; 
}
require_once '../common/config.php';

// 1. Database Schema Check inside the script (to ensure the table exists)
$schema_query = "CREATE TABLE IF NOT EXISTS ext_mock_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    quiz_type ENUM('manual', 'embed', 'gform') DEFAULT 'manual',
    question TEXT NULL,
    option_a VARCHAR(255) NULL,
    option_b VARCHAR(255) NULL,
    option_c VARCHAR(255) NULL,
    option_d VARCHAR(255) NULL,
    correct_answer VARCHAR(10) NULL,
    html_code TEXT NULL,
    gform_url VARCHAR(255) NULL,
    is_free BOOLEAN DEFAULT 1,
    mrp DECIMAL(10,2) DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($schema_query);

// Just in case ext_mock_tests already exists without these columns, let's alter it safely
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN quiz_type ENUM('manual', 'embed', 'gform') DEFAULT 'manual'"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN question TEXT NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN option_a VARCHAR(255) NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN option_b VARCHAR(255) NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN option_c VARCHAR(255) NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN option_d VARCHAR(255) NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN correct_answer VARCHAR(10) NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN html_code TEXT NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN gform_url VARCHAR(255) NULL"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN mrp DECIMAL(10,2) DEFAULT 0"); } catch(Exception $e) {}
try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN price DECIMAL(10,2) DEFAULT 0"); } catch(Exception $e) {}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $quiz_type = $conn->real_escape_string($_POST['quiz_type'] ?? 'manual');
    $mrp = (float)($_POST['mrp'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    
    $subject = $conn->real_escape_string($_POST['subject'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    
    $question = isset($_POST['question']) ? $conn->real_escape_string($_POST['question']) : '';
    $option_a = isset($_POST['option_a']) ? $conn->real_escape_string($_POST['option_a']) : '';
    $option_b = isset($_POST['option_b']) ? $conn->real_escape_string($_POST['option_b']) : '';
    $option_c = isset($_POST['option_c']) ? $conn->real_escape_string($_POST['option_c']) : '';
    $option_d = isset($_POST['option_d']) ? $conn->real_escape_string($_POST['option_d']) : '';
    $correct_answer = isset($_POST['correct_answer']) ? $conn->real_escape_string($_POST['correct_answer']) : '';
    
    $html_code = isset($_POST['html_code']) ? $conn->real_escape_string($_POST['html_code']) : '';
    $gform_url = isset($_POST['gform_url']) ? $conn->real_escape_string($_POST['gform_url']) : '';
    
    $sql = "INSERT INTO ext_mock_tests (title, quiz_type, question, option_a, option_b, option_c, option_d, correct_answer, html_code, gform_url, is_free, mrp, price, subject, duration) 
            VALUES ('$title', '$quiz_type', '$question', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer', '$html_code', '$gform_url', $is_free, $mrp, $price, '$subject', $duration)";
            
    if ($conn->query($sql)) {
        $test_id = $conn->insert_id;
        
        if ($quiz_type === 'manual' && !empty($question)) {
            $conn->query("CREATE TABLE IF NOT EXISTS mock_test_questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                test_id INT,
                question TEXT,
                opt_a VARCHAR(255),
                opt_b VARCHAR(255),
                opt_c VARCHAR(255),
                opt_d VARCHAR(255),
                correct_opt VARCHAR(10)
            )");
            $conn->query("INSERT INTO mock_test_questions (test_id, question, opt_a, opt_b, opt_c, opt_d, correct_opt) 
                          VALUES ($test_id, '$question', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer')");
        }
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'MySQL Error: ' . mysqli_error($conn), 'sql' => $sql]);
    }
    exit;
}

if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM ext_mock_tests WHERE id=$id");
    header("Location: mock_tests.php");
    exit;
}
include 'common/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Mock Tests</h1>
</div>

<div class="bg-white rounded shadow p-4 mb-6">
    <h2 class="font-bold mb-4">Add New Mock Test</h2>
    <div id="alert_box" class="hidden p-3 rounded mb-4 font-bold text-sm"></div>
    
    <form id="addQuizForm" onsubmit="submitQuizForm(event)" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" required class="w-full border p-2 rounded">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Subject / Topic</label>
                <input type="text" name="subject" class="w-full border p-2 rounded" placeholder="e.g. Mathematics, History">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Duration (minutes)</label>
                <input type="number" name="duration" class="w-full border p-2 rounded" value="0" placeholder="0 for unlimited">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">MRP (₹)</label>
                <input type="number" step="0.01" name="mrp" class="w-full border p-2 rounded" value="0">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Selling Price (₹)</label>
                <input type="number" step="0.01" name="price" class="w-full border p-2 rounded" value="0">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Quiz Type</label>
            <select name="quiz_type" id="quiz_type" class="w-full border p-2 rounded" onchange="toggleFields()">
                <option value="manual">Manual Type</option>
                <option value="embed">HTML Embed</option>
                <option value="gform">Google Forms</option>
            </select>
        </div>
        
        <div id="html_field" style="display: none;">
            <h3 class="font-bold text-gray-700 mt-4 border-b pb-1 mb-2">HTML Quiz Details</h3>
            <label class="block text-sm font-medium mb-1">HTML Code</label>
            <textarea name="html_code" rows="4" class="w-full border p-2 rounded" placeholder="<iframe src='...'></iframe>"></textarea>
        </div>
        
        <!-- Google Form Field -->
        <div id="gform_field" style="display: none;">
            <h3 class="font-bold text-gray-700 mt-4 border-b pb-1 mb-2">Google Form Details</h3>
            <label class="block text-sm font-medium mb-1">Google Form URL</label>
            <input type="url" name="gform_url" class="w-full border p-2 rounded">
        </div>
        
        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_free" value="1" checked class="form-checkbox">
                <span class="ml-2">Is Free</span>
            </label>
        </div>
        <button type="submit" id="submitBtn" class="bg-blue-600 text-white px-4 py-2 rounded font-bold">Save Mock Test</button>
    </form>
    
    <script>
        function toggleFields() {
            var type = document.getElementById('quiz_type').value;
            document.getElementById('html_field').style.display = 'none';
            document.getElementById('gform_field').style.display = 'none';
            
            if (type === 'html' || type === 'embed') {
                document.getElementById('html_field').style.display = 'block';
            } else if (type === 'gform') {
                document.getElementById('gform_field').style.display = 'block';
            }
        }
        
        function submitQuizForm(e) {
            e.preventDefault();
            
            const form = document.getElementById('addQuizForm');
            const btn = document.getElementById('submitBtn');
            const alertBox = document.getElementById('alert_box');
            
            const formData = new FormData(form);
            formData.append('action', 'add');
            
            // Console log to debug payload
            console.log("Submitting Admin Form Data:");
            for (let pair of formData.entries()) {
                console.log(pair[0] + ', ' + pair[1]);
            }
            
            btn.innerHTML = 'Saving...';
            btn.disabled = true;
            
            fetch('mock_tests.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = 'Save Mock Test';
                btn.disabled = false;
                
                alertBox.classList.remove('hidden');
                
                if(data.success) {
                    alertBox.className = 'p-3 rounded mb-4 font-bold text-sm bg-green-100 text-green-700';
                    alertBox.innerHTML = 'Mock Test Saved Successfully!';
                    form.reset();
                    toggleFields();
                    setTimeout(() => location.reload(), 1500);
                } else if(data.error) {
                    alertBox.className = 'p-3 rounded mb-4 font-bold text-sm bg-red-100 text-red-700 border border-red-200';
                    alertBox.innerHTML = `<b>Database Error:</b> ${data.error}`;
                    console.error("SQL Failed:", data.sql);
                }
            })
            .catch(err => {
                console.error(err);
                btn.innerHTML = 'Save Mock Test';
                btn.disabled = false;
                alertBox.classList.remove('hidden');
                alertBox.className = 'p-3 rounded mb-4 font-bold text-sm bg-red-100 text-red-700';
                alertBox.innerHTML = 'AJAX Request Failed. Check console.';
            });
        }
        
        // initialize view
        toggleFields();
    </script>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Title</th>
                <th class="p-3 text-left">Subject / Duration</th>
                <th class="p-3 text-left">Type</th>
                <th class="p-3 text-left">Price</th>
                <th class="p-3 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rows = $conn->query("SELECT * FROM ext_mock_tests ORDER BY id DESC");
            if($rows) {
                while($n = $rows->fetch_assoc()):
            ?>
            <tr class="border-t">
                <td class="p-3 font-semibold"><?php echo htmlspecialchars($n['title']); ?></td>
                <td class="p-3 text-sm text-gray-600">
                    <div><?php echo htmlspecialchars($n['subject']); ?></div>
                    <div class="text-xs text-gray-400"><?php echo $n['duration'] > 0 ? $n['duration'].' mins' : 'No limit'; ?></div>
                </td>
                <td class="p-3 text-sm text-blue-600">
                    <span class="text-gray-500 font-bold uppercase"><i class="fas fa-list"></i> <?php echo htmlspecialchars($n['quiz_type'] ?? 'unknown'); ?></span>
                    <?php if($n['quiz_type'] === 'manual' || $n['type'] === 'internal'): ?>
                        <br><a href="manage_mock_test.php?id=<?php echo $n['id']; ?>" class="text-xs text-primary underline"><i class="fas fa-edit"></i> Manage Quiz</a>
                    <?php endif; ?>
                </td>
                <td class="p-3 text-sm"><?php echo $n['is_free'] ? '<span class="text-green-600 font-bold">Free</span>' : '<span class="text-red-500 font-bold">₹'.$n['price'].'</span>'; ?></td>
                <td class="p-3">
                    <a href="?del=<?php echo $n['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Delete this test?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; } ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>
