document.addEventListener("DOMContentLoaded", () => {
  // sidebar
  const navLinks = document.querySelectorAll(".sidebar a");
  const sections = document.querySelectorAll(".section");

  navLinks.forEach(link => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      navLinks.forEach(lnk => lnk.classList.remove("active"));
      link.classList.add("active");
      sections.forEach(sec => sec.classList.remove("active"));
      const targetId = link.getAttribute("data-section");
      const targetSection = document.getElementById(targetId);
      if (targetSection) {
        targetSection.classList.add("active");
      }

      switch (targetId) {
        case "classes":
          loadClasses();
          break;
        case "groups":
          loadGroupSets();
          break;
        case "students":
          loadStudents();
          break;
        case "bazaar":
          loadRewards();
          break;
        case "admin":
          loadAdminRoles();
          break;
        default:
          break;
      }
    });
  });

  // classes
  const addClassForm = document.getElementById("addClassForm");
  addClassForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const className = document.getElementById("className").value.trim();
    const classDescription = document.getElementById("classDescription").value.trim();

    try {
      const res = await fetch("classes.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name: className,
          description: classDescription
        })
      });
      const data = await res.json();

      if (res.ok) {
        addClassForm.reset();
        loadClasses();
      } else {
        console.error("Error creating class:", data.error);
        alert("Failed to create class.");
      }
    } catch (err) {
      console.error("Fetch error:", err);
      alert("Error connecting to server.");
    }
  });

  // group set approval
  const requireApprovalCheckbox = document.getElementById("requireApproval");
  const approvalOptionsDiv = document.getElementById("approvalOptions");
  if (requireApprovalCheckbox && approvalOptionsDiv) {
    requireApprovalCheckbox.addEventListener("change", () => {
      approvalOptionsDiv.style.display = requireApprovalCheckbox.checked ? "block" : "none";
    });
  }

  // group set
  const createGroupSetForm = document.getElementById("createGroupSetForm");
  if (createGroupSetForm) {
    createGroupSetForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const groupSetName = document.getElementById("groupSetName").value.trim();
      const allowSelfSignup = document.getElementById("allowSelfSignup").checked;
      const requireApproval = document.getElementById("requireApproval").checked;
      const leaderApproval = document.getElementById("leaderApproval").checked;
      const teacherApproval = document.getElementById("teacherApproval").checked;

      const classId = 1; // this is hardcoded as a fixed classId, eventually gonna change this to dynamic
      try {
  
        const res = await fetch("group_sets.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            class_id: classId,
            name: groupSetName,
            allow_self_signup: allowSelfSignup,
            require_approval: requireApproval,
            require_teacher_approval: teacherApproval,
            require_leader_approval: leaderApproval
          })
        });
        const data = await res.json();

        if (res.ok) {
          createGroupSetForm.reset();
          approvalOptionsDiv.style.display = "none";
          loadGroupSets(); 
        } else {
          console.error("Error creating group set:", data.error);
          alert("Failed to create group set.");
        }
      } catch (err) {
        console.error("Fetch error:", err);
        alert("Error creating group set.");
      }
    });
  }
});

// classes
async function loadClasses() {
  try {
    const res = await fetch("classes.php");
    const data = await res.json();
    const classesTableBody = document.getElementById("classesTableBody");
    classesTableBody.innerHTML = "";

    data.forEach(cls => {
      const row = document.createElement("tr");
      const inviteCode = cls.invite_hash || "N/A";  
      row.innerHTML = `
        <td>${cls.name}</td>
        <td>${cls.id}</td>
        <td>${cls.student_count ?? 0}</td>
        <td>
          <input type="text" class="invite-code-input" value="${inviteCode}" readonly />
          <button onclick="copyText('${inviteCode}')">Copy</button>
        </td>
        <td>
          <button onclick="editClass(${cls.id}, '${cls.name}', '${cls.description}')">Edit</button>
          <button onclick="deleteClass(${cls.id})">Delete</button>
          <button onclick="manageClass(${cls.id}, '${cls.name}')">Manage</button>
        </td>
      `;
      classesTableBody.appendChild(row);
    });
  } catch (err) {
    console.error("Error loading classes:", err);
  }
}

async function deleteClass(classId) {
  if (!confirm("Are you sure you want to delete this class?")) return;
  try {
    const res = await fetch(`classes.php?id=${classId}`, {
      method: "DELETE"
    });
    const data = await res.json();
    if (res.ok) {
      loadClasses();
    } else {
      console.error("Error deleting class:", data.error);
      alert("Failed to delete class.");
    }
  } catch (err) {
    console.error("Fetch error:", err);
    alert("Error calling delete class.");
  }
}

function editClass(classId, currentName, currentDesc) {
  const newName = prompt("Enter new name:", currentName);
  if (newName === null) return;
  const newDesc = prompt("Enter new description:", currentDesc);
  if (newDesc === null) return;

  updateClass(classId, newName, newDesc);
}

async function updateClass(classId, name, description) {
  try {
    const res = await fetch(`classes.php?id=${classId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name, description })
    });
    const data = await res.json();
    if (res.ok) {
      loadClasses();
    } else {
      console.error("Error updating class:", data.error);
      alert("Failed to update class.");
    }
  } catch (err) {
    console.error("Fetch error:", err);
    alert("Error updating class.");
  }
}

function copyText(text) {
  navigator.clipboard.writeText(text)
    .then(() => alert("Copied!"))
    .catch(() => alert("Failed to copy."));
}

async function manageClass(classId, className) {
  try {
    // Show the "manageClassModal" div
    const modal = document.getElementById("manageClassModal");
    modal.style.display = "block";

    // Set the title
    const title = document.getElementById("manageClassTitle");
    title.textContent = `Manage Membership for Class: ${className} (ID: ${classId})`;

    // Clear existing lists
    document.getElementById("enrolledList").innerHTML = "";
    document.getElementById("notEnrolledList").innerHTML = "";

    // Fetch from e.g. class_members.php?class_id=###
    const res = await fetch(`class_members.php?class_id=${classId}`);
    const data = await res.json();

    if (!res.ok) {
      alert("Failed to load class membership: " + (data.error || "Unknown error"));
      return;
    }

    const enrolled = data.enrolled || [];
    const notEnrolled = data.not_enrolled || [];

    // Populate the "enrolled" list
    const enrolledList = document.getElementById("enrolledList");
    enrolled.forEach(stu => {
      const li = document.createElement("li");
      li.innerHTML = `
        ${stu.first_name} ${stu.last_name} (ID: ${stu.id})
        <button onclick="removeStudentFromClass(${classId}, ${stu.id})">Remove</button>
      `;
      enrolledList.appendChild(li);
    });

    // Populate the "notEnrolled" list
    const notEnrolledList = document.getElementById("notEnrolledList");
    notEnrolled.forEach(stu => {
      const li = document.createElement("li");
      li.innerHTML = `
        ${stu.first_name} ${stu.last_name} (ID: ${stu.id})
        <button onclick="addStudentToClass(${classId}, ${stu.id})">Add</button>
      `;
      notEnrolledList.appendChild(li);
    });

  } catch (err) {
    console.error("Error managing class:", err);
    alert("Error loading class membership.");
  }
  document.getElementById("manageClassTitle").textContent = `Manage Class: ${className}`;
  document.getElementById("manageClassModal").style.display = "block";
}

// A function to hide the modal
function closeManageClass() {
  document.getElementById("manageClassModal").style.display = "none";
}

async function removeStudentFromClass(classId, userId) {
  const confirmRemove = confirm("Are you sure you want to remove this student?");
  if (!confirmRemove) return;

  try {
    const res = await fetch(`class_members.php?class_id=${classId}&user_id=${userId}`, {
      method: "DELETE"
    });
    const data = await res.json();

    if (!res.ok) {
      alert("Failed to remove student: " + (data.error || "Unknown error"));
      return;
    }

    // reload the manage view
    manageClass(classId, ""); // pass className or refetch it
  } catch (err) {
    console.error("Error removing student:", err);
  }
}

async function addStudentToClass(classId, userId) {
  try {
    const res = await fetch(`class_members.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ class_id: classId, user_id: userId })
    });
    const data = await res.json();

    if (!res.ok) {
      alert("Failed to add student: " + (data.error || "Unknown error"));
      return;
    }

    // reload the manage view
    manageClass(classId, "");
  } catch (err) {
    console.error("Error adding student:", err);
  }
}

// groups sets
async function loadGroupSets() {
  try {
    const res = await fetch("group_sets.php");
    const data = await res.json();
    const tableBody = document.getElementById("groupSetsTableBody");
    tableBody.innerHTML = "";

    data.forEach(gs => {
      const allowSignup = gs.allow_self_signup ? "Yes" : "No";

      let approvalStr = "No";
      if (gs.require_approval) {
        const teacher = gs.require_teacher_approval ? "Teacher Approval" : "";
        const leader = gs.require_leader_approval ? "Leader Approval" : "";
        if (teacher && leader) {
          approvalStr = teacher + ", " + leader;
        } else if (teacher || leader) {
          approvalStr = teacher || leader;
        } else {
          approvalStr = "Yes";
        }
      }

      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${gs.name}</td>
        <td>${allowSignup}</td>
        <td>${approvalStr}</td>
        <td>
          <button onclick="manageGroupSet(${gs.id})">Manage</button>
          <button onclick="deleteGroupSet(${gs.id})">Delete</button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  } catch (err) {
    console.error("Error loading group sets:", err);
  }
}

async function deleteGroupSet(gsId) {
  if (!confirm("Are you sure you want to delete this group set?")) return;
  try {
    const res = await fetch(`group_sets.php?id=${gsId}`, {
      method: "DELETE"
    });
    const data = await res.json();
    if (res.ok) {
      loadGroupSets();
    } else {
      console.error("Error deleting group set:", data.error);
      alert("Failed to delete group set.");
    }
  } catch (err) {
    console.error("Fetch error:", err);
    alert("Error deleting group set.");
  }
}

function manageGroupSet(gsId) {
  // this is a placeholder for later
  alert(`Here you would manage group set #${gsId}`);
}

// students
function loadStudents() {
  fetch("users.php")
    .then(res => res.json())
    .then(data => {
      const tableBody = document.getElementById("studentsTableBody");
      tableBody.innerHTML = "";

      data.forEach(user => {
        // if user.role is "student" etc...
        const displayRole = user.role.charAt(0).toUpperCase() + user.role.slice(1).toLowerCase();

        // Parse enrolled_classes
        let parsedClasses = [];
        if (user.enrolled_classes) {
          // "2:Chem101|5:Math202"
          parsedClasses = user.enrolled_classes.split("|").map(segment => {
            const [cidStr, cname] = segment.split(":");
            return { id: cidStr, name: cname };
          });
        }

        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${user.first_name} ${user.last_name}</td>
          <td>${user.email}</td>
          <td>${parsedClasses.map(pc => pc.name).join(", ") || "No classes yet"}</td>
          <td>${displayRole} <select id="roleSelect-${user.id}">
          <option value="student" ${user.role === "student" ? "selected" : ""}>Student</option>
          <option value="teacher" ${user.role === "Teacher" ? "selected" : ""}>Teacher</option>
          <option value="admin" ${user.role === "admin" ? "selected" : ""}>Admin</option>
        </select>
        <button onclick="updateGlobalRole(${user.id})">Apply</button>
      </td></td>
          <td>${user.balance || 0}</td>
          <td>
            <button onclick="adjustPoints(${user.id})">Adjust Points</button>
            <select id="classSelect-${user.id}">
              <option value="">Select class...</option>
              ${parsedClasses.map(pc => `
                <option value="${pc.id}">${pc.name}</option>
              `).join("")}
            </select>
            <button onclick="removeStudentFromSelectedClass(${user.id})">Remove</button>
          </td>
        `;
        tableBody.appendChild(row);
      });
    })
    .catch(err => console.error("Error loading students:", err));
}


async function adjustPoints(userId) {
  // This prompt can accept positive for awarding or negative for removing
  const rawInput = prompt("Enter a positive number to add points, or a negative number to remove points:");
  if (!rawInput) return;  // user canceled or no input

  const pointsChange = parseInt(rawInput, 10);
  if (isNaN(pointsChange)) {
    alert("Please enter a valid integer.");
    return;
  }

  try {
    const res = await fetch(`users.php?id=${userId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ balance_increment: pointsChange })
    });
    const data = await res.json();

    if (!res.ok) {
      alert("Failed to update balance: " + (data.error || "Unknown error"));
      return;
    }
    // Reload the table to see updated balance
    loadStudents();
  } catch (err) {
    console.error("Error adjusting points:", err);
    alert("Error adjusting points.");
  }
}

async function removeStudentFromSelectedClass(userId) {
  // Get the select element
  const select = document.getElementById(`classSelect-${userId}`);
  const classId = select.value; // e.g. "2"
  if (!classId) {
    alert("Please select a class to remove this student from.");
    return;
  }

  const confirmRemove = confirm(`Are you sure you want to remove this student from class ID=${classId}?`);
  if (!confirmRemove) return;

  try {
    // e.g. DELETE /class_members.php?class_id=2&user_id=7
    const res = await fetch(`class_members.php?class_id=${classId}&user_id=${userId}`, {
      method: "DELETE"
    });
    const data = await res.json();

    if (!res.ok) {
      alert("Failed to remove student: " + (data.error || "Unknown error"));
      return;
    }

    // Refresh the students
    loadStudents();
  } catch (err) {
    console.error("Error removing student:", err);
  }
}

async function updateGlobalRole(userId) {
  const select = document.getElementById(`roleSelect-${userId}`);
  const newRole = select.value; // e.g. "teacher"

  // If user tries to set the same role, you could skip, etc.
  // but let's just always do the update
  try {
    const res = await fetch(`users.php?id=${userId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ role: newRole })
    });
    const data = await res.json();

    if (!res.ok) {
      alert("Failed to change role: " + (data.error || "Unknown error"));
      return;
    }

    // reload
    loadStudents();
  } catch (err) {
    console.error("Error changing role:", err);
  }
}

//bazaar
async function loadRewards() {
  try {
    const res = await fetch("rewards.php"); 
    const data = await res.json();
    const tableBody = document.getElementById("bazaarItemsTable");
    tableBody.innerHTML = "";

    data.forEach(rw => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${rw.name}</td>
        <td>${rw.description}</td>
        <td>${rw.cost}</td>
        <td><button onclick="assignReward(${rw.id})">Assign to Student</button></td>
      `;
      tableBody.appendChild(row);
    });
  } catch (err) {
    console.error("Error loading rewards:", err);
  }
}

function assignReward(rewardId) {
  // going to have a UI for the teacher to pick students, this isnt implemented yet
  alert(`Assign reward #${rewardId} to some student (not yet implemented).`);
}

// admin
async function loadAdminRoles() {
  try {
    const res = await fetch("users.php");
    const data = await res.json();
    const tableBody = document.getElementById("adminRolesTable");
    tableBody.innerHTML = "";

    data.forEach(user => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${user.first_name} ${user.last_name}</td>
        <td>${user.role}</td>
        <td><button onclick="changeUserRole(${user.id}, '${user.role}')">Change Role</button></td>
      `;
      tableBody.appendChild(row);
    });
  } catch (err) {
    console.error("Error loading admin roles:", err);
  }
}

function changeUserRole(userId, currentRole) {
  const newRole = prompt("Enter new role (teacher/admin/student/club-admin):", currentRole);
  if (!newRole || newRole === currentRole) return;
  updateUserRole(userId, newRole);
}

async function updateUserRole(userId, role) {
  try {
    const res = await fetch(`users.php?id=${userId}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ role })
    });
    const data = await res.json();
    if (res.ok) {
      loadAdminRoles();
    } else {
      console.error("Error updating user role:", data.error);
      alert("Failed to update role.");
    }
  } catch (err) {
    console.error("Fetch error:", err);
  }
}

// invite link
const generateInviteLinkBtn = document.getElementById("generateInviteLinkBtn");
const inviteLinkDisplay = document.getElementById("inviteLinkDisplay");
const inviteLinkInput = document.getElementById("inviteLink");
const copyInviteLinkBtn = document.getElementById("copyInviteLinkBtn");

if (generateInviteLinkBtn) {
  generateInviteLinkBtn.addEventListener("click", () => {
    const roleSelect = document.getElementById("inviteRole");
    const chosenRole = roleSelect.value;
    const dummyHash = Math.random().toString(36).slice(2, 8).toUpperCase();
    const dummyLink = `https://my-site.com/invite/${chosenRole}/${dummyHash}`;

    inviteLinkInput.value = dummyLink;
    inviteLinkDisplay.style.display = "block";
  });
}

if (copyInviteLinkBtn) {
  copyInviteLinkBtn.addEventListener("click", () => {
    inviteLinkInput.select();
    document.execCommand("copy"); 
    alert("Invite link copied!");
  });
}

// logout 
function logout() {
  fetch("logout.php", { method: "POST" })
    .then(() => {
      window.location.href = "login.html";
    })
    .catch(err => console.error("Logout error:", err));
}

