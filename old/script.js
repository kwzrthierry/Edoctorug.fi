$(document).ready(function() {
    // Initialize AOS
    AOS.init();
    
    // Smooth scrolling using jQuery easing
    $('a.nav-link').on('click', function(event) {
        if (this.hash !== '') {
            event.preventDefault();
            const hash = this.hash;
            $('html, body').animate(
                {
                    scrollTop: $(hash).offset().top
                },
                800,
                function() {
                    window.location.hash = hash;
                }
            );
        }
    });

    $('#reviewCarousel').carousel({
        interval: 3000 // 3 seconds
    });

    document.querySelectorAll('.star-rating .fa-star').forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating || 0;
            this.parentNode.querySelectorAll('.fa-star').forEach(s => {
                s.classList.remove('checked');
            });
            for (let i = 0; i < rating; i++) {
                this.parentNode.children[i].classList.add('checked');
            }
        });
    });

    $('.service').hover(
        function() {
            $(this).find('h4').css('color', '#ffd700');
        },
        function() {
            $(this).find('h4').css('color', '#333');
        }
    );

    // Star rating feedback
    $('#feedbackRating .fa').on('click', function() {
        var rating = $(this).data('rating');
        $('#feedbackRating .fa').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).addClass('checked');
            } else {
                $(this).removeClass('checked');
            }
        });
    });

    // Animated button hover effect
    $('.animated-button').hover(
        function() {
            $(this).addClass('hovered');
        },
        function() {
            $(this).removeClass('hovered');
        }
    );

    $('.animated-button').click(function() {
        $(this).animate(
            {
                opacity: 0.5
            },
            100
        ).animate(
            {
                opacity: 1
            },
            100
        );
    });

    // Limit phone number input to 9 digits and format it
    $('.form-control.phone-input').on('input', function() {
        var phoneNumber = $(this).val();
        // Remove non-digit characters
        phoneNumber = phoneNumber.replace(/\D/g, '');
        // Limit to 9 digits
        phoneNumber = phoneNumber.slice(0, 9);
        // Update the input field value
        $(this).val(phoneNumber);
    });

    $('.phone-input').on('keydown', function(event) {
        var phoneNumber = $(this).val();
        // Check if the user is trying to enter more than 9 digits
        if (phoneNumber.length >= 9 && event.keyCode !== 8 && event.keyCode !== 46) {
            // Prevent further keypresses
            event.preventDefault();
        }
    });

    // Handle apply loan form submission
    $('#applyLoanForm').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'apply_loan.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert(response);
                window.location.href = 'user/dashboard.php';
            },
            error: function(xhr, status, error) {
                alert('An error occurred while submitting your loan application.');
            }
        });
    });


    // Handle save money form submission
    $('#saveMoneyForm').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'save_money.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Handle the response from the server
                alert(response);
                // Redirect or perform other actions as needed
                window.location.href = 'user/dashboard.php';
            },
            error: function(xhr, status, error) {
                alert('An error occurred while saving money.');
            }
        });
    });

    // Handle click on "Reason" button
    $('.reasonBtn').click(function() {
        var loanId = $(this).data('id');
        // AJAX request to fetch reason
        $.ajax({
            url: 'get_reason.php',
            type: 'POST',
            data: { loanId: loanId },
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.success) {
                    if (response.reason.trim() === '') {
                        $('#reasonText').html('Reason not provided.');
                    } else {
                        $('#reasonText').html(response.reason);
                    }
                    $('#reasonModal').modal('show');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while fetching the reason.');
            }
        });
    });

    // Handle login form submission
    $('#loginForm').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Check if the response is 'success'
                if (response === 'success') {
                    // Check user type and redirect accordingly
                    $.ajax({
                        url: 'get_user_type.php',
                        type: 'GET',
                        success: function(userType) {
                            switch (userType.trim()) {
                                case 'user':
                                    window.location.href = 'user/dashboard.php';
                                    break;
                                case 'admin':
                                    window.location.href = 'dashboard-admin.php';
                                    break;
                                case 'support':
                                    window.location.href = 'customer_Support/dashboard.php';
                                    break;
                                case 'loaner':
                                    window.location.href = 'loaner/dashboard.php';
                                    break;
                                default:
                                    alert('Unknown user type.');
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('An error occurred while fetching user type.');
                        }
                    });
                } else {
                    // Display the response as an alert
                    alert(response);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while logging in. Please try again later.');
            }
        });
    });


    // Validate National ID number input
    $('#loanNationalIdNumber').on('input', function() {
        var nationalId = $(this).val();
        var isValid = validateNationalId(nationalId);

        if (isValid) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    // Validate National ID number input
    $('#signupNationalIdNumber').on('input', function() {
        var nationalId = $(this).val();
        var isValid = validateNationalId(nationalId);

        if (isValid) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    

    // Function to validate National ID number
    function validateNationalId(nationalId) {
        if (nationalId.length !== 14) {
            return false;
        }

        var firstChar = nationalId.charAt(0);
        if (!/[A-Za-z]/.test(firstChar)) {
            return false;
        }

        var secondChar = nationalId.charAt(1);
        if (secondChar !== 'M' && secondChar !== 'F') {
            return false;
        }

        var yearOfBirth = parseInt(nationalId.substring(2, 4), 10);
        if (isNaN(yearOfBirth)) {
            return false;
        }

        var currentYear = new Date().getFullYear();
        var fullYear = yearOfBirth <= (currentYear % 100) ? 2000 + yearOfBirth : 1900 + yearOfBirth;
        var age = currentYear - fullYear;
        if (age < 16) {
            return false;
        }

        var middleNumbers = nationalId.substring(4, 9);
        if (!/^\d{5}$/.test(middleNumbers)) {
            return false;
        }

        var lastTwoChars = nationalId.substring(12, 14);
        if (!/[A-Za-z]{2}/.test(lastTwoChars)) {
            return false;
        }

        return true;
    }
});
