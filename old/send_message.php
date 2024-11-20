<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve form data
    $name = $_POST['contactName'];
    $email = $_POST['contactEmail'];
    $message = $_POST['contactMessage'];

    // Mailjet configuration
    $apiKey = '011d2876304c6d5ce2a96d8ad5a5956c';
    $apiSecret = '4647168034066ebe852841169105580c';

    // Email details
    $to = "kwzrthieery@gmail.com";
    $subject = "Message from Contact Form";

    // Mailjet email data
    $emailData = [
        'Messages' => [
            [
                'From' => [
                    'Email' => 'tk.tech.rw@gmail.com',
                    'Name' => 'TK tech'
                ],
                'To' => [
                    [
                        'Email' => $to,
                        'Name' => 'Recipient'
                    ]
                ],
                'Subject' => $subject,
                'TextPart' => "Name: $name\nEmail: $email\nMessage:\n$message",
                'HTMLPart' => "<html>
                                    <body>
                                        <p><strong>Name:</strong> $name</p>
                                        <p><strong>Email:</strong> $email</p>
                                        <p><strong>Message:</strong><br>$message</p>
                                    </body>
                                </html>"
            ]
        ]
    ];

    // Send email using Mailjet
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$apiKey:$apiSecret");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        // Error sending email
        echo '<script>
                setTimeout(function() {
                    alert("Message could not be sent. Please try again later.");
                }, 500);
            </script>';
        echo "Message could not be sent. Mailjet Error: " . curl_error($ch);
    } else {
        // Check Mailjet response
        $responseData = json_decode($response, true);
        if ($responseData['Messages'][0]['Status'] === 'success') {
            // Database connection details
            require 'test 3/db_connection.php';

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // SQL to insert message into database
            $stmt = $conn->prepare("INSERT INTO messages (name, message) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $message);

            if ($stmt->execute()) {
                // Message inserted into database successfully
                echo '<script>
                        setTimeout(function() {
                            alert("Message sent and saved. Thank you!");
                        }, 500);
                    </script>';
            } else {
                // Error inserting into database
                echo '<script>
                        setTimeout(function() {
                            alert("Message could not be saved. Please try again later.");
                        }, 500);
                    </script>';
            }

            // Close statement and connection
            $stmt->close();
            $conn->close();
        } else {
            // Error sending email
            echo '<script>
                    setTimeout(function() {
                        alert("Message could not be sent. Please try again later.");
                    }, 500);
                </script>';
            echo "Message could not be sent. Mailjet Error: " . $responseData['Messages'][0]['Errors'][0]['Text'];
        }
    }
    curl_close($ch);
}
?>
