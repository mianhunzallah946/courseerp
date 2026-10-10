<?php
session_start();
include("./connection.php");

// Admin authentication check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./login.php");
    exit();
}

// ✅ Fetch all students
$sql = "SELECT user_id, fname, lname, email, role, course_name, membership
        FROM login
        WHERE role = 'student'
        ORDER BY fname ASC";
$res = mysqli_query($connec, $sql);
?>
<div class="d-flex" id="wrapper">
    <!-- Fixed Sidebar -->
    <?php include("sidebar.php"); ?>
    
    <!-- Main Content Area -->
    <div id="page-content-wrapper" class="w-100 bg-light">
        <?php include("header.php"); ?>
        
        <div class="container-fluid px-4 py-4">

  <!-- Page header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
      <h1 class="h3 fw-bold text-dark mb-1">Student & User Management</h1>
      <p class="text-secondary small mb-0">Manage student accounts, role permissions, and active payment/membership statuses</p>
    </div>
    <div class="d-flex gap-2">
      <a href="./add_course.php" class="btn btn-primary rounded-3 px-3 shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Add Course
      </a>
    </div>
  </div>

  <!-- Card -->
  <div class="card card-erp shadow-sm border-0 rounded-4 overflow-hidden">

    <!-- Toolbar -->
    <div class="table-toolbar">
      <div class="search-input position-relative">
        <span style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);
                     color:var(--clr-text-light);font-size:.8rem;pointer-events:none;">
          <i class="fas fa-magnifying-glass"></i>
        </span>
        <input type="text" id="studentSearch" class="form-control"
               style="padding-left:2rem;" placeholder="Search students…">
      </div>
      <div class="ms-auto d-flex gap-2">
        <a href="./addcourse.php" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Add Course
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle text-center" id="studentsTable">
        <thead>
          <tr>
            <th>ID</th>
            <th class="text-start">Full Name</th>
            <th class="text-start">Email</th>
            <th>Role</th>
            <th>Course</th>
            <th>Status</th>
            <th>Toggle</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($res) > 0) { ?>
            <?php while ($row = mysqli_fetch_assoc($res)) { ?>
              <tr>
                <td><span class="text-muted">#<?= $row['user_id']; ?></span></td>
                <td class="text-start fw-500"><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                <td class="text-start text-muted"><?= htmlspecialchars($row['email']); ?></td>
                <td><span class="badge badge-blue-subtle"><?= htmlspecialchars(ucfirst($row['role'])); ?></span></td>
                <td><?= htmlspecialchars($row['course_name']); ?></td>

                <!-- ✅ Status badge — updated live by JS -->
                <td id="status-<?= $row['user_id']; ?>">
                  <?php if ($row['membership'] === 'paid') { ?>
                    <span class="badge badge-success-subtle px-3">✅ Paid</span>
                  <?php } else { ?>
                    <span class="badge badge-danger-subtle px-3">❌ Unpaid</span>
                  <?php } ?>
                </td>

                <!-- 🔘 Toggle — AJAX, no page refresh -->
                <td>
                  <label class="switch">
                    <input
                      type="checkbox"
                      class="membership-toggle"
                      data-user-id="<?= $row['user_id']; ?>"
                      <?= ($row['membership'] === 'paid') ? 'checked' : ''; ?>
                    >
                    <span class="slider"></span>
                  </label>
                </td>

                <td>
                  <a href="./deleteuser.php?delid=<?= $row['user_id']; ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('⚠️ Delete this student permanently?');">
                    <i class="fas fa-trash-can"></i>
                  </a>
                </td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                No students found.
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div><!-- end table-responsive -->

  </div><!-- end card -->
</div><!-- end container-fluid -->

<script>
document.querySelectorAll('.membership-toggle').forEach(function(toggle) {
  toggle.addEventListener('change', function() {
    var userId     = this.getAttribute('data-user-id');
    var newStatus  = this.checked ? 'paid' : 'free';
    var statusCell = document.getElementById('status-' + userId);

    var formData = new FormData();
    formData.append('user_id',    userId);
    formData.append('membership', newStatus);

    fetch('updatemembership.php', { method: 'POST', body: formData })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.status === 'success') {
          if (newStatus === 'paid') {
            statusCell.innerHTML = '<span class="badge badge-success-subtle px-3">✅ Paid</span>';
            showToast('success', 'Membership Updated', 'Student marked as Paid.');
          } else {
            statusCell.innerHTML = '<span class="badge badge-danger-subtle px-3">❌ Unpaid</span>';
            showToast('warning', 'Membership Updated', 'Student marked as Unpaid.');
          }
        } else {
          showToast('error', 'Update Failed', 'Could not update membership. Try again.');
          toggle.checked = !toggle.checked;
        }
      })
      .catch(function(err) {
        console.error(err);
        showToast('error', 'Network Error', 'Request failed. Check your connection.');
        toggle.checked = !toggle.checked;
      });
  });
});

// ── Live Search Filter ──
document.getElementById('studentSearch')?.addEventListener('input', function() {
  var q     = this.value.toLowerCase();
  var rows  = document.querySelectorAll('#studentsTable tbody tr');
  rows.forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>

<?php include("./footer.php"); ?>
</div><!-- end page-content-wrapper -->
</div><!-- end wrapper -->
