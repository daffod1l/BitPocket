function addNewSchool() {
    const newSchool = prompt("Enter the name of your School/University/Institute:");
    if (newSchool) {
        const schoolSelect = document.getElementById("school-name");
        const newOption = document.createElement("option");
        newOption.value = newSchool;
        newOption.textContent = newSchool;
        schoolSelect.appendChild(newOption);
        schoolSelect.value = newSchool;
    }
}


function validatePassword() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const passwordMessage = document.getElementById('password-message');
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (password === confirmPassword) {
        if (!passwordRegex.test(password)) {
            passwordMessage.innerHTML = "Password must be at least 8 characters long, contain one uppercase letter, one lowercase letter, one number, and one special character.";
            passwordMessage.style.color = "red";
            return false;
        } 
        else {
            passwordMessage.innerHTML = "Password is valid.";
            passwordMessage.style.color = "green";
            return true;
        }
    } 
    else {
        passwordMessage.innerHTML = "Passwords do not match!";
        passwordMessage.style.color = "red";
        return false;
    }
}


document.getElementById('password').addEventListener('input', validatePassword);
document.getElementById('confirm-password').addEventListener('input', validatePassword);


document.addEventListener("DOMContentLoaded", function () {
    const passwordField = document.getElementById("password");
    const confirmPasswordField = document.getElementById("confirm-password");
    const togglePassword = document.getElementById("eye-icon-password");
    const toggleConfirmPassword = document.getElementById("eye-icon-confirm-password");

    if (passwordField && togglePassword) {
        togglePassword.addEventListener("click", function () {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                togglePassword.classList.remove("fa-eye");
                togglePassword.classList.add("fa-eye-slash");
            } 
            else {
                passwordField.type = "password";
                togglePassword.classList.remove("fa-eye-slash");
                togglePassword.classList.add("fa-eye");
            }
        });
    }

    if (confirmPasswordField && toggleConfirmPassword) {
        toggleConfirmPassword.addEventListener("click", function () {
            if (confirmPasswordField.type === "password") {
                confirmPasswordField.type = "text";
                toggleConfirmPassword.classList.remove("fa-eye");
                toggleConfirmPassword.classList.add("fa-eye-slash");
            } 
            else {
                confirmPasswordField.type = "password";
                toggleConfirmPassword.classList.remove("fa-eye-slash");
                toggleConfirmPassword.classList.add("fa-eye");
            }
        });
    }
});