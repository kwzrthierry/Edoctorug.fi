<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan</title>
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
            overflow-x: hidden;
        }

        .back-arrow {
            font-size: 24px;
            text-decoration: none;
            color: #007bff;
            margin-bottom: 20px;
            display: inline-block;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .back-arrow:hover {
            transform: translateX(-10px);
            color: #0056b3;
        }

        .apply-loan-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.5s ease-in-out;
            position: relative;
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

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }

        .btn-primary {
            border-radius: 30px;
            background: linear-gradient(to right, #007bff, #0056b3);
            border: none;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #0056b3, #007bff);
        }

        .loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader::after {
            content: '';
            border: 5px solid #007bff;
            border-top: 5px solid transparent;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo {
            margin-bottom: 20px;
            height: 60px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .row .form-group {
            margin-bottom: 15px;
        }

        @media (max-width: 576px) {
            .form-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <button onclick="goBack()" class="back-button">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="apply-loan-container">
        <img src="../assets/images/client-01.png" alt="Logo" class="logo">
        <h3 class="text-center">Apply for Loan</h3>
        <form id="applyLoanForm" enctype="multipart/form-data">
            <div class="loader" style="display: none;">Loading...</div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="loanName">Full Name as on National ID</label>
                    <input type="text" class="form-control" id="loanName" name="loanName" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanPhone">Mobile Phone Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">+256</span>
                        </div>
                        <input type="text" class="form-control phone-input" id="loanPhone" name="loanPhone" required>
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanEmail">Email</label>
                    <input type="email" class="form-control" id="loanEmail" name="loanEmail" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanNationalIdNumber">National ID Number</label>
                    <input type="text" class="form-control" id="loanNationalIdNumber" name="loanNationalIdNumber" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanNationalId">National ID</label>
                    <input type="file" class="form-control" id="loanNationalId" name="loanNationalId" accept="image/*,.pdf" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanAmount">Loan Amount</label>
                    <input type="number" class="form-control" id="loanAmount" name="loanAmount" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanCountry">Country</label>
                    <input type="text" class="form-control" id="loanCountry" name="loanCountry" value="Uganda" readonly>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanRegion">Region</label>
                    <select class="form-control" id="loanRegion" name="loanRegion" required>
                        <option value="">Select Region</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanDistrict">District</label>
                    <select class="form-control" id="loanDistrict" name="loanDistrict" required>
                        <option value="">Select District</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanCounty">County</label>
                    <select class="form-control" id="loanCounty" name="loanCounty" required>
                        <option value="">Select County</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="loanVillage">Village</label>
                    <select class="form-control" id="loanVillage" name="loanVillage" required>
                        <option value="">Select Village</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="refereeName">Referee's Name</label>
                    <input type="text" class="form-control" id="refereeName" name="refereeName" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="refereePhone">Referee's Phone Number</label>
                    <input type="text" class="form-control" id="refereePhone" name="refereePhone" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-4">Apply for Loan</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function() {
        const geoNamesUsername = 'kwzrthierry'; // Your GeoNames username
        const ugandaGeonameId = 226074; // Geoname ID for Uganda

        // Fetch regions and populate the region dropdown
        fetchRegions();

        function fetchRegions() {
            $('.loader').show(); // Show loader
            $.getJSON(`http://api.geonames.org/childrenJSON?geonameId=${ugandaGeonameId}&username=${geoNamesUsername}`, function(data) {
                const regions = data.geonames;
                $('#loanRegion').empty().append('<option value="">Select Region</option>'); // Clear previous options
                regions.forEach(region => {
                    $('#loanRegion').append(`<option value="${region.geonameId}">${region.name}</option>`);
                });
                $('.loader').hide(); // Hide loader
            }).fail(function() {
                alert('Failed to load regions.'); // Optional error handling
                $('.loader').hide(); // Hide loader
            });
        }

        // Fetch and populate districts based on selected region
        $('#loanRegion').change(function() {
            const selectedRegionId = $(this).val(); // Get the selected region ID
            $('#loanDistrict').empty().append('<option value="">Select District</option>');
            $('#loanVillage').empty().append('<option value="">Select Village</option>');

            if (selectedRegionId) {
                $('.loader').show(); // Show loader
                // Fetch districts for the selected region
                $.getJSON(`http://api.geonames.org/childrenJSON?geonameId=${selectedRegionId}&username=${geoNamesUsername}`, function(data) {
                    const districts = data.geonames;
                    districts.forEach(district => {
                        $('#loanDistrict').append(`<option value="${district.geonameId}">${district.name}</option>`);
                    });
                    $('.loader').hide(); // Hide loader
                }).fail(function() {
                    alert('Failed to load districts.'); // Optional error handling
                    $('.loader').hide(); // Hide loader
                });
            }
        });
        $('#loanDistrict').change(function() {
            const selectedDistrictId = $(this).val();
            $('#loanCounty').empty().append('<option value="">Select County</option>');
            $('#loanSubcounty').empty().append('<option value="">Select Subcounty</option>');
            $('#loanVillage').empty().append('<option value="">Select Village</option>');

            if (selectedDistrictId) {
                $('.loader').show(); // Show loader
                $.getJSON(`http://api.geonames.org/childrenJSON?geonameId=${selectedDistrictId}&username=${geoNamesUsername}`, function(data) {
                    const counties = data.geonames;
                    counties.forEach(county => {
                        $('#loanCounty').append(`<option value="${county.geonameId}">${county.name}</option>`);
                    });
                    $('.loader').hide(); // Hide loader
                }).fail(function() {
                    alert('Failed to load counties.'); // Optional error handling
                    $('.loader').hide(); // Hide loader
                });
            }
        });

        // Fetch and populate villages based on selected county
        $('#loanCounty').change(function() {
            const selectedCountyId = $(this).val();
            $('#loanVillage').empty().append('<option value="">Select Village</option>');

            if (selectedCountyId) {
                $('.loader').show(); // Show loader
                // Fetch villages for the selected county
                $.getJSON(`http://api.geonames.org/childrenJSON?geonameId=${selectedCountyId}&username=${geoNamesUsername}`, function(data) {
                    const villages = data.geonames;
                    villages.forEach(village => {
                        $('#loanVillage').append(`<option value="${village.geonameId}">${village.name}</option>`);
                    });
                    $('.loader').hide(); // Hide loader
                }).fail(function() {
                    alert('Failed to load villages.'); // Optional error handling
                    $('.loader').hide(); // Hide loader
                });
            }
        });
    });

    function goBack() {
        window.location.href = '../services.html';
    }
</script>
</body>
</html>
