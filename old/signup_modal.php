<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        .signup-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.5s ease-in-out;
        }
        .logo {
            margin-bottom: 20px; /* Space below logo */
            height: 60px; /* Set logo height */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .form-group label {
            font-weight: 600;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #007bff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
        }

        .input-group-text {
            border-radius: 5px 0 0 5px;
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .input-group input.form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
            animation: shake 0.5s;
        }

        .invalid-feedback {
            display: block;
            font-size: 14px;
            color: #dc3545;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-primary:focus {
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.5);
        }

        .btn-primary:disabled {
            background-color: #b8daff;
            border-color: #b8daff;
        }

        .btn-primary:disabled:hover {
            background-color: #b8daff;
            border-color: #b8daff;
        }

        .btn-outline-dark {
            color: #343a40;
            border-color: #343a40;
            transition: color 0.3s ease, border-color 0.3s ease;
        }

        .btn-outline-dark:hover {
            color: #fff;
            background-color: #343a40;
            border-color: #343a40;
        }

        .btn-outline-dark:focus {
            box-shadow: 0 0 0 0.2rem rgba(52, 58, 64, 0.5);
        }

        .btn-outline-dark:disabled {
            color: #868e96;
            border-color: #868e96;
        }

        .btn-outline-dark:disabled:hover {
            background-color: transparent;
            color: #868e96;
        }

        @keyframes shake {
            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-10px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(10px);
            }
        }

        .modal-content {
            border-radius: 8px;
            padding: 20px;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
        }

        /* Styles for the Back button */
        .back-button {
            position: absolute; /* Position it at the top left */
            top: 20px; /* Adjust as needed */
            left: 20px; /* Adjust as needed */
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 50%; /* Circular button */
            width: 40px; /* Button width */
            height: 40px; /* Button height */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .back-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
    </style>
</head>

<body>
    <!-- Back to Home link -->
    <button onclick="goBack()" class="back-button">
        <i class="fas fa-arrow-left"></i> <!-- Left arrow icon -->
    </button>
    <div class="signup-container" style="align-items: center;">
        <div class="text-center mb-4">
            <img src="../assets/images/client-01.png" alt="Logo" class="logo"> <!-- Add your logo here -->
        </div>
        <h3 class="mb-4">Sign Up</h3>
        <!-- Signup form -->
        <form id="signupForm">
            <div class="form-group">
                <label for="signupNationalIdNumber">National ID Number</label>
                <input type="text" class="form-control" id="signupNationalIdNumber" name="signupNationalIdNumber" required>
                <div class="invalid-feedback" id="nationalIdError"></div>
            </div>
            <div class="form-group">
                <label for="signupName">Full Name as on National ID</label>
                <input type="text" class="form-control" id="signupName" name="signupName" required>
            </div>
            <div class="form-group">
                <label for="signupEmail">Email</label>
                <input type="email" class="form-control" id="signupEmail" name="signupEmail" required>
                <div class="invalid-feedback" id="emailError"></div>
            </div>
            <!-- Form group for mobile phone number -->
            <div class="form-group">
                <label for="saveMoneyPhone">Mobile Phone Number</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">+256</span>
                    </div>
                    <input type="text" class="form-control phone-input" id="saveMoneyPhone" name="saveMoneyPhone" required>
                    <!-- Feedback for invalid phone number -->
                    <div class="invalid-feedback" id="phoneError">Please enter a valid phone number.</div>
                </div>
            </div>
            <div class="form-group">
                <label for="nationalIdFile">Upload National ID File</label>
                <input type="file" class="form-control-file" id="nationalIdFile" name="nationalIdFile" required>
                <div class="invalid-feedback" id="fileError"></div>
            </div>
            <div class="form-group">
                <label for="signupPassword">Password</label>
                <input type="password" class="form-control" id="signupPassword" name="signupPassword" required>
            </div>

            <!-- New Checkbox for Loan Option -->
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="loanCheckbox">
                <label class="form-check-label" for="loanCheckbox">I want to give loans</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-4">Sign Up</button>
        </form>
        <!-- Login link -->
        <div class="text-center mt-3">
            <p>Already have an account? <a href="login_modal.php">Login here</a></p>
        </div>
    </div>

    <!-- Loan Modal -->
    <div class="modal fade" id="loanModal" tabindex="-1" role="dialog" aria-labelledby="loanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loanModalLabel">Loan Provider Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>By selecting this option, you will receive leads on individuals who may need loans. However, you
                        will not be able to apply for loans yourself.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmLoanOption">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and FontAwesome JS -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="script.js"></script>
    <script>
        function goBack() {
            window.location.href = "../index.html"; // Redirect to index.html
        }
        // Handle signup form submission with AJAX
        $('#signupForm').submit(function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: 'signup.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    // Log the full response from the server
                    console.log('Server Response:', response);

                    // Handle the response from the server
                    if (response.status === 'success') {
                        alert('Signup successful! Redirecting to login...');
                        window.location.href = 'login_modal.php';
                    } else if (response.status === 'error') {
                        // Display errors returned from the server
                        if (response.errors.email) {
                            $('#emailError').text(response.errors.email);
                            $('#signupEmail').addClass('is-invalid');
                        }
                        if (response.errors.nationalId) {
                            $('#nationalIdError').text(response.errors.nationalId);
                            $('#signupNationalIdNumber').addClass('is-invalid');
                        }
                        if (response.errors.phone) {
                            $('#phoneError').text(response.errors.phone);
                            $('#saveMoneyPhone').addClass('is-invalid');
                        }
                    }
                },
                error: function (xhr, status, error) {
                    // Log the error details
                    console.log('AJAX Error:', {
                        xhr: xhr,
                        status: status,
                        error: error
                    });
                    alert('An error occurred while signing up. Please try again later.');
                }
            });
        });

        // Clear error messages on input
        $('#signupNationalIdNumber').on('input', function () {
            $(this).removeClass('is-invalid');
            $('#nationalIdError').text('');
        });

        $('#signupEmail').on('input', function () {
            $(this).removeClass('is-invalid');
            $('#emailError').text('');
        });

        $('#saveMoneyPhone').on('input', function () {
            var phone = $(this).val();
            if (phone.length >= 9) {
                $(this).removeClass('is-invalid');
                $('#phoneError').text('');
            } else if (phone.length > 0) {
                $(this).addClass('is-invalid');
                $('#phoneError').text('Please enter a valid phone number.');
            } else {
                $(this).removeClass('is-invalid');
                $('#phoneError').text('');
            }
        });

        // Hide the phone number error message on focus
        $('#saveMoneyPhone').on('focus', function () {
            $('#phoneError').text('');
        });

        // Handle the checkbox change event
        $('#loanCheckbox').change(function () {
            if ($(this).is(':checked')) {
                $('#loanModal').modal('show');
            }
        });

        // Uncheck the checkbox if the user clicks Cancel
        $('#loanModal').on('hidden.bs.modal', function () {
            if (!$('#confirmLoanOption').data('confirmed')) {
                $('#loanCheckbox').prop('checked', false);
            }
            $('#confirmLoanOption').removeData('confirmed'); // Reset the confirmation state
        });

        // Set confirmation when user clicks Confirm
        $('#confirmLoanOption').click(function () {
            $(this).data('confirmed', true);
            $('#loanModal').modal('hide');
        });
    </script>

</body>

</html>
