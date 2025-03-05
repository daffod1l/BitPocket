<?php
require 'config.php';

$first_name = $last_name = $email = $role = $school_name = $security_question = $security_answer = '';
$password = $confirm_password = '';
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $first_name = trim($_POST['first-name']);
    $last_name = trim($_POST['last-name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    $role = $_POST['role'];
    $school_name = $_POST['school-name'];
    $security_question = $_POST['security-question'];
    $security_answer = trim($_POST['security-answer']);

    
    if ($password !== $confirm_password) {
        $errorMessage = "Passwords do not match!";
    } 
    else {
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        
        if (!preg_match($passwordRegex, $password)) {
            $errorMessage = "Password must be at least 8 characters long, contain one uppercase letter, one lowercase letter, one number, and one special character.";
        } 
        else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $hashed_answer = password_hash($security_answer, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (first_name, last_name, email, password, role, school_name, security_question, security_answer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $hashed_password, $role, $school_name, $security_question, $hashed_answer);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful!'); window.location.href = 'login.php';</script>";
            } else {
                echo "<script>alert('Error: Could not register.');</script>";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en-US">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    
    <link rel="stylesheet" href="styles.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>

<div class="container">
    <div class="registration-container">
        <h2 class="page-title">Please register to PrizeVersity</h2>

        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="first-name">First Name <span class="required">*</span></label>
                <input type="text" id="first-name" name="first-name" value="<?php echo htmlspecialchars($first_name); ?>" placeholder="First Name" required>
            </div>

            <div class="form-group">
                <label for="last-name">Last Name <span class="required">*</span></label>
                <input type="text" id="last-name" name="last-name" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="Last Name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
            </div>

            <div class="form-group password-container">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" placeholder="Enter your password" required>
                <i id="eye-icon-password" class="fa fa-eye toggle-password"></i>
            </div>

            <div class="form-group password-container">
                <label for="confirm-password">Confirm Password <span class="required">*</span></label>
                <input type="password" id="confirm-password" name="confirm-password" value="<?php echo htmlspecialchars($confirm_password); ?>" placeholder="Confirm your password" required>
                <i id="eye-icon-confirm-password" class="fa fa-eye toggle-password"></i>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <div class="form-group" style="margin-top: 30px;">
                <label><strong>Are you a teacher or a student?</strong> <span class="required">*</span></label>
            </div>

            <div class="form-group radio-group">
                <div class="radio-option">
                    <input type="radio" id="teacher" name="role" value="Teacher" <?php if ($role == 'Teacher') echo 'checked'; ?> required>
                    <label for="teacher">Teacher</label>
                </div>

                <div class="radio-option">
                    <input type="radio" id="student" name="role" value="Student" <?php if ($role == 'Student') echo 'checked'; ?> required>
                    <label for="student">Student</label>
                </div>
            </div>

            <div class="form-group">
                <label for="school-name"><strong>School/University/Institute Name:</strong> <span class="required">*</span></label>
                <select id="school-name" name="school-name" required>
                    <option value="" disabled selected>Select your institution</option>
                    <option value="school1" <?php if ($school_name == 'school1') echo 'selected'; ?>>University of Michigan</option>
                    <option value="school2" <?php if ($school_name == 'school2') echo 'selected'; ?>>Michigan State University</option>
                    <option value="school3" <?php if ($school_name == 'school3') echo 'selected'; ?>>Wayne State University</option>
                    <option value="school4" <?php if ($school_name == 'school4') echo 'selected'; ?>>Michigan Technological University</option>
                    <option value="school5" <?php if ($school_name == 'school5') echo 'selected'; ?>>Western Michigan University</option>
                </select>
                <button type="button" class="add-new-btn" onclick="addNewSchool()">Add New</button>
            </div>

            <div class="form-group">
                <label for="security-question"><strong>Security Question:</strong> <span class="required">*</span></label>
                <select id="security-question" name="security-question" required>
                    <option value="" disabled selected>Select a question</option>
                    <option value="mother-maiden-name" <?php if ($security_question == 'mother-maiden-name') echo 'selected'; ?>>What is your mother's maiden name?</option>
                    <option value="birth-city" <?php if ($security_question == 'birth-city') echo 'selected'; ?>>In which city were you born?</option>
                    <option value="student-id" <?php if ($security_question == 'student-id') echo 'selected'; ?>>What is your student ID number?</option>
                </select>
            </div>

            <div class="form-group">
                <label for="security-answer"><strong>Answer:</strong> <span class="required">*</span></label>
                <input type="text" id="security-answer" name="security-answer" value="<?php echo htmlspecialchars($security_answer); ?>" placeholder="Answer the chosen security question" required>
            </div>

            <!-- NOTE: Need to register the site to get the site and secret keys for Google reCAPTCHA. -->
            <div class="form-group recaptcha-center">
                <div class="g-recaptcha" data-sitekey="reCAPTCHA site key"></div>
            </div>

            <div class="form-group buttons-group">
                <button type="submit" class="register-btn">Register</button>
                <a href="login.php" class="login-btn">Login</a>
            </div>
        </form>
    </div>

    <div class="image-container">
        <img src="logo.png" alt="PrizeVersity" class="image">
    </div>
</div>

<script src="script.js"></script>
</body>
</html>