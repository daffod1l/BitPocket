<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// this is if a student is trying to log in
if (!isset($_SESSION['role']) || 
   ( $_SESSION['role'] !== 'Teacher' && $_SESSION['role'] !== 'admin' )) {
    exit("You do not have permission to view this page.");
}

require_once 'db.php'; 

$teacherId = $_SESSION['user_id'];

// home page stats
$sql = "SELECT COUNT(*) AS total_classes FROM classes WHERE teacher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$numClasses = $row ? $row['total_classes'] : 0;

$sql = "
  SELECT COUNT(DISTINCT cm.user_id) AS total_students
  FROM class_members cm
  JOIN classes c ON c.id = cm.class_id
  WHERE c.teacher_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$numStudents = $row ? $row['total_students'] : 0;

$sql = "
  SELECT COUNT(*) AS total_groups
  FROM group_sets gs
  JOIN classes c ON gs.class_id = c.id
  WHERE c.teacher_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$numGroups = $row ? $row['total_groups'] : 0;

$sql = "
  SELECT COUNT(*) AS pending_approvals
  FROM group_members gm
  JOIN class_groups cg ON gm.group_id = cg.id
  JOIN group_sets gs ON cg.group_set_id = gs.id
  JOIN classes c ON gs.class_id = c.id
  WHERE gm.is_pending = 1
    AND c.teacher_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$numApprovals = $row ? $row['pending_approvals'] : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Teacher Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header>
    <div class="nav-left">
      <h1>Teacher Dashboard</h1>
    </div>
    <div class="nav-right">
      <a href="logout.php">
      <button class="btn-logout">Logout</button>
      </a>
    </div>
  </header>

  <nav class="sidebar">
    <ul>
      <li><a href="#" data-section="dashboard-home" class="active">Home</a></li>
      <li><a href="#" data-section="classes">Classes</a></li>
      <li><a href="#" data-section="groups">Groups/Guilds</a></li>
      <li><a href="#" data-section="students">Students</a></li>
      <li><a href="#" data-section="bazaar">Bazaar</a></li>
      <li><a href="#" data-section="admin">Admin</a></li>
    </ul>
  </nav>

  <main>
  <section id="dashboard-home" class="section active">
      <h2>Welcome Back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h2>
      <div class="cards-container">
        <div class="card">
          <h3>My Classes</h3>
          <p>Number of classes: <strong><?php echo $numClasses; ?></strong></p>
        </div>
        <div class="card">
          <h3>Students</h3>
          <p>Total enrolled: <strong><?php echo $numStudents; ?></strong></p>
        </div>
        <div class="card">
          <h3>Groups/Guilds</h3>
          <p>Active groups: <strong><?php echo $numGroups; ?></strong></p>
        </div>
        <div class="card">
          <h3>Pending Approvals</h3>
          <p>Group join requests: <strong><?php echo $numApprovals; ?></strong></p>
        </div>
      </div>
    </section>

  <section id="classes" class="section">
    <h2>Classes</h2>

    <?php
    $sql = "SELECT id, name, description, invite_hash FROM classes WHERE teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacherClasses = $result->fetch_all(MYSQLI_ASSOC);
    ?>

    <div class="form-container">
      <h3>Add New Class</h3>
      <form action="create_class.php" method="POST">
        <label for="className">Class Name:</label>
        <input type="text" name="className" required />

        <label for="classDescription">Description:</label>
        <textarea name="classDescription"></textarea>

        <button type="submit">Create Class</button>
      </form>
    </div>

  <div class="table-container">
    <h3>My Classes</h3>
    <table>
      <thead>
        <tr>
          <th>Class Name</th>
          <th>Description</th>
          <th>Invite Link</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($teacherClasses as $cls): ?>
        <tr>
          <td><?php echo htmlspecialchars($cls['name']); ?></td>
          <td><?php echo htmlspecialchars($cls['description']); ?></td>
          <td>
            <input type="text" value="https://my-site.com/invite/<?php echo $cls['invite_hash']; ?>" readonly />
          </td>
          <td>
            <a href="edit_class.php?id=<?php echo $cls['id']; ?>">Edit</a>
            <a href="delete_class.php?id=<?php echo $cls['id']; ?>" onclick="return confirm('Delete this class?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

    <section id="groups" class="section">
      <h2>Groups / Guilds</h2>

      <div class="form-container">
        <h3>Create Group Set</h3>
        <form id="createGroupSetForm">
          <label for="groupSetName">Group Set Name:</label>
          <input type="text" id="groupSetName" name="groupSetName" required />

          <label>
            <input type="checkbox" id="allowSelfSignup" />
            Allow Self Signup
          </label>
          <label>
            <input type="checkbox" id="requireApproval" />
            Require Approval
          </label>

          <div id="approvalOptions" style="display: none;">
            <label>
              <input type="checkbox" id="leaderApproval" />
              Require Guild Master (Leader) Approval
            </label>
            <label>
              <input type="checkbox" id="teacherApproval" />
              Require Teacher Approval
            </label>
          </div>

          <button type="submit">Create Group Set</button>
        </form>
      </div>

      <div class="table-container">
        <h3>Existing Group Sets</h3>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Self Signup</th>
              <th>Approval Required</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="groupSetsTableBody">
          </tbody>
        </table>
      </div>
    </section>

    <section id="students" class="section">
      <h2>Students</h2>

      <div class="table-container">
        <h3>All Students</h3>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Classes</th>
              <th>Role(s)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="studentsTableBody">
          </tbody>
        </table>
      </div>
    </section>

    <section id="bazaar" class="section">
      <h2>Bazaar</h2>
      <p>This is where teachers or admins can assign rewards, items, or badges.</p>
      <div class="table-container">
        <h3>Rewards / Items</h3>
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Description</th>
              <th>Cost (points)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="bazaarItemsTable">
          </tbody>
        </table>
      </div>
    </section>

    <section id="admin" class="section">
      <h2>Admin Dashboard</h2>
      <p>Manage roles and special permissions here.</p>

      <div class="table-container">
        <h3>User Roles</h3>
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Current Roles</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="adminRolesTable">
          </tbody>
        </table>
      </div>

      <div class="form-container">
        <h3>Invite New Users</h3>
        <label for="inviteRole">Choose Role:</label>
        <select id="inviteRole">
          <option value="teacher">Teacher</option>
          <option value="admin">Admin</option>
          <option value="club-admin">Club Admin</option>
        </select>
        <button id="generateInviteLinkBtn">Generate Invite Link</button>

        <div id="inviteLinkDisplay" class="invite-link-display" style="display: none;">
          <label>Share this link:</label>
          <input type="text" id="inviteLink" readonly />
          <button id="copyInviteLinkBtn">Copy</button>
        </div>
      </div>
    </section>
  </main>

  <script src="scripts.js"></script>
</body>
</html>
