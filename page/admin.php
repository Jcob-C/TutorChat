<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/AI.php';
require_once __DIR__ . '/../utils/PageBlocker.php';
require_once __DIR__ . '/../utils/PopupMessage/.php';

session_start();

// Create mysqli connection
$conn = new mysqli(host, user, pass, db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

redirectUnauthorized($conn);
redirectLearner();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['logout'])) {
            $_SESSION = [];
            session_destroy();
            header('Location: login.php');
            exit();
        }
        // Add Topic
        if (isset($_POST['action']) && $_POST['action'] === 'add_topic') {
            $title = $conn->real_escape_string(trim($_POST['title']));
            $plan = $conn->real_escape_string(trim($_POST['plan']));
            $available = isset($_POST['available']) ? 1 : 0;
            
            $sql = "INSERT INTO topics (title, plan, available) VALUES ('$title', '$plan', $available)";
            if ($conn->query($sql)) {
                setPopupMessage('Topic added successfully!');
            } else {
                setPopupMessage('Error: ' . $conn->error);
            }
            header('Location: admin.php');
            exit();
        }
        
        // Edit Topic
        if (isset($_POST['action']) && $_POST['action'] === 'edit_topic') {
            $id = intval($_POST['topic_id']);
            $title = $conn->real_escape_string(trim($_POST['title']));
            $plan = $conn->real_escape_string(trim($_POST['plan']));
            $available = isset($_POST['available']) ? 1 : 0;
            
            $sql = "UPDATE topics SET title = '$title', plan = '$plan', available = $available WHERE id = $id";
            if ($conn->query($sql)) {
                setPopupMessage('Topic updated successfully!');
            } else {
                setPopupMessage('Error: ' . $conn->error);
            }
            header('Location: admin.php');
            exit();
        }
        
        // Delete Topic
        if (isset($_POST['action']) && $_POST['action'] === 'delete_topic') {
            $id = intval($_POST['topic_id']);
            $sql = "DELETE FROM topics WHERE id = $id";
            if ($conn->query($sql)) {
                setPopupMessage('Topic deleted successfully!');
            } else {
                setPopupMessage('Error: ' . $conn->error);
            }
            header('Location: admin.php');
            exit();
        }
        
        // Add User
        if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
            $email = $conn->real_escape_string(trim($_POST['email']));
            $nickname = $conn->real_escape_string(trim($_POST['nickname']));
            $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
            $pass_escaped = $conn->real_escape_string($pass);
            $acc_role = $conn->real_escape_string($_POST['acc_role']);
            $activated = isset($_POST['activated']) ? 1 : 0;
            
            $sql = "INSERT INTO users (email, nickname, pass, acc_role, activated) VALUES ('$email', '$nickname', '$pass_escaped', '$acc_role', $activated)";
            if ($conn->query($sql)) {
                setPopupMessage('User added successfully!');
            } else {
                setPopupMessage('Error: ' . $conn->error);
            }
            header('Location: admin.php');
            exit();
        }
        
        // Edit User
        if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
            $id = intval($_POST['user_id']);
            $email = $conn->real_escape_string(trim($_POST['email']));
            $nickname = $conn->real_escape_string(trim($_POST['nickname']));
            $acc_role = $conn->real_escape_string($_POST['acc_role']);
            $activated = isset($_POST['activated']) ? 1 : 0;
            
            if (!empty($_POST['pass'])) {
                $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
                $pass_escaped = $conn->real_escape_string($pass);
                $sql = "UPDATE users SET email = '$email', nickname = '$nickname', pass = '$pass_escaped', acc_role = '$acc_role', activated = $activated WHERE id = $id";
            } else {
                $sql = "UPDATE users SET email = '$email', nickname = '$nickname', acc_role = '$acc_role', activated = $activated WHERE id = $id";
            }
            
            if ($conn->query($sql)) {
                setPopupMessage('User updated successfully!');
            } else {
                setPopupMessage('Error: ' . $conn->error);
            }
            header('Location: admin.php');
            exit();
        }
        
        // Delete User
        if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
            $id = intval($_POST['user_id']);
            $sql = "DELETE FROM users WHERE id = $id";
            if ($conn->query($sql)) {
                setPopupMessage('User deleted successfully!');
            } else {
                setPopupMessage('Error: ' . $conn->error);
            }
            header('Location: admin.php');
            exit();
        }
        
        // Generate Lesson Plan
        if (isset($_POST['action']) && $_POST['action'] === 'generate_plan') {
            $topic = trim($_POST['topic_title']);
            if ($topic == '') {
                throw new Exception();
            }
            $plan = generateLessonPlan($topic);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'plan' => $plan], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        }
        
    } catch (Exception $e) {
        setPopupMessage('Error: ' . $e->getMessage());
        header('Location: admin.php');
        exit();
    }
}

// Fetch all topics
$topics_result = $conn->query("SELECT * FROM topics ORDER BY id DESC");
$topics = [];
if ($topics_result) {
    while ($row = $topics_result->fetch_assoc()) {
        $topics[] = $row;
    }
}

// Fetch all users
$users_result = $conn->query("SELECT * FROM users ORDER BY id DESC");
$users = [];
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'topics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TutorChat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/Style.css">
    <link rel="stylesheet" href="../assets/PopupMessage.css">
</head>
<body>
    <?php displayPopupMessage(); ?>
    
    <header class="bg-white py-3 mb-4 shadow-sm">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <h1 class="h3 mb-0 text-nowrap"><i class="bi bi-chat-dots-fill icon-primary"></i> TutorChat Admin</h1>
                <nav class="d-flex flex-wrap gap-3 align-items-center">
                    <a href="?tab=topics" class="text-decoration-none <?php echo $activeTab === 'topics' ? 'fw-bold' : ''; ?>">
                        <i class="bi bi-book me-1"></i>Topics
                    </a>
                    <a href="?tab=users" class="text-decoration-none <?php echo $activeTab === 'users' ? 'fw-bold' : ''; ?>">
                        <i class="bi bi-person-circle me-1"></i>Users
                    </a>
                    <form method="post">
                        <button type="submit" name="logout" class="text-decoration-none text-danger">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if ($activeTab === 'topics'): ?>
            <!-- Topics Management -->
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Topics Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTopicModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Topic
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topics as $topic): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($topic['id']); ?></td>
                                    <td><?php echo htmlspecialchars($topic['title']); ?></td>
                                    <td>
                                        <?php if ($topic['available']): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Unavailable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick='editTopic(<?php echo htmlspecialchars(json_encode($topic)); ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteTopic(<?php echo $topic['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- Users Management -->
            <div class="card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">Users Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle me-1"></i>Add User
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Nickname</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nickname']); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($user['acc_role']); ?></span></td>
                                    <td>
                                        <?php if ($user['activated']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Topic Modal -->
    <div class="modal fade" id="addTopicModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_topic">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="addTopicTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lesson Plan</label>
                            <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="generatePlan('addTopicTitle', 'addTopicPlan')">
                                <i class="bi bi-magic me-1"></i>Generate Plan
                            </button>
                            <textarea class="form-control mt-2" name="plan" id="addTopicPlan" rows="10" required></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="available" id="addTopicAvailable">
                            <label class="form-check-label" for="addTopicAvailable">Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Topic</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Topic Modal -->
    <div class="modal fade" id="editTopicModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_topic">
                        <input type="hidden" name="topic_id" id="editTopicId">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="editTopicTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lesson Plan</label>
                            <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="generatePlan('editTopicTitle', 'editTopicPlan')">
                                <i class="bi bi-magic me-1"></i>Generate Plan
                            </button>
                            <textarea class="form-control mt-2" name="plan" id="editTopicPlan" rows="10" required></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="available" id="editTopicAvailable">
                            <label class="form-check-label" for="editTopicAvailable">Available</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Topic Modal -->
    <div class="modal fade" id="deleteTopicModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_topic">
                        <input type="hidden" name="topic_id" id="deleteTopicId">
                        <p>Are you sure you want to delete this topic? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nickname</label>
                            <input type="text" class="form-control" name="nickname" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" class="form-control" name="pass" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="acc_role" required>
                                <option value="learner">Learner</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activated" id="addUserActivated" checked>
                            <label class="form-check-label" for="addUserActivated">Activated</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editUserEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nickname</label>
                            <input type="text" class="form-control" name="nickname" id="editUserNickname" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (leave empty to keep current)</label>
                            <input type="text" class="form-control" name="pass">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="acc_role" id="editUserRole" required>
                                <option value="learner">Learner</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="activated" id="editUserActivated">
                            <label class="form-check-label" for="editUserActivated">Activated</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../utils/PopupMessage/.js"></script>
    <script>
        function editTopic(topic) {
            document.getElementById('editTopicId').value = topic.id;
            document.getElementById('editTopicTitle').value = topic.title;
            document.getElementById('editTopicPlan').value = topic.plan;
            document.getElementById('editTopicAvailable').checked = topic.available == 1;
            new bootstrap.Modal(document.getElementById('editTopicModal')).show();
        }

        function deleteTopic(id) {
            document.getElementById('deleteTopicId').value = id;
            new bootstrap.Modal(document.getElementById('deleteTopicModal')).show();
        }

        function editUser(user) {
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editUserEmail').value = user.email;
            document.getElementById('editUserNickname').value = user.nickname;
            document.getElementById('editUserRole').value = user.acc_role;
            document.getElementById('editUserActivated').checked = user.activated == 1;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }

        function deleteUser(id) {
            document.getElementById('deleteUserId').value = id;
            new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
        }

        async function generatePlan(titleFieldId, planFieldId) {
            const title = document.getElementById(titleFieldId).value;
            if (!title) {
                displayPopupMessage('Please enter a topic title first');
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';

            try {
                const formData = new FormData();
                formData.append('action', 'generate_plan');
                formData.append('topic_title', title);

                const response = await fetch('admin.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    document.getElementById(planFieldId).value = data.plan;
                    displayPopupMessage('Plan generated successfully!');
                } else {
                    displayPopupMessage('Failed to generate plan');
                }
            } catch (error) {
                displayPopupMessage('Error generating plan: ' + error.message);
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>