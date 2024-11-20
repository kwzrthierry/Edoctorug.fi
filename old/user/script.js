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
