<?php
// Assuming you already have a database connection
include '../test 3/db_connection.php'; // Ensure this connection uses mysqli

// Function to log debug information to a file
function logDebug($message) {
    $logfile = 'debug.txt';
    $currentTime = date('Y-m-d H:i:s');
    $formattedMessage = $currentTime . " - " . $message . PHP_EOL;
    file_put_contents($logfile, $formattedMessage, FILE_APPEND);
}

// Function to get the total current amount for a user
function getTotalCurrentAmount($conn, $idNumber) {
    // Query to get the latest total current amount for the user
    $totalQuery = $conn->prepare("SELECT total_current_amount FROM savings WHERE id_number = ? ORDER BY date DESC LIMIT 1");
    $totalQuery->bind_param("s", $idNumber);
    $totalQuery->execute();
    $result = $totalQuery->get_result()->fetch_assoc();
    
    // Check if the total_current_amount is either null or zero
    if ($result && $result['total_current_amount'] !== null && $result['total_current_amount'] != 0) {
        return $result['total_current_amount'];
    } else {
        // If no valid total current amount found or it's zero, calculate the sum of all entries
        $sumQuery = $conn->prepare("SELECT SUM(amount) AS total_savings FROM savings WHERE id_number = ?");
        $sumQuery->bind_param("s", $idNumber);
        $sumQuery->execute();
        $sumResult = $sumQuery->get_result()->fetch_assoc();
        
        // Return the total savings or 0 if no entries are found
        return $sumResult ? $sumResult['total_savings'] : 0;
    }
}

// Function to update the status of the lead
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $leadId = $_POST['lead_id'] ?? '';
    $response = ['success' => false, 'message' => '', 'status' => '', 'due_date' => '', 'lead' => [], 'debug' => []];

    // Validate input
    if (empty($action) || empty($leadId)) {
        $response['message'] = 'Invalid request parameters.';
        logDebug("Invalid request parameters: Action - $action, Lead ID - $leadId");
        echo json_encode($response);
        exit();
    }

    // Collect debug information
    logDebug("Action: $action, Lead ID: $leadId");

    // Prepare the lead query
    $leadQuery = $conn->prepare("SELECT * FROM leads WHERE id = ?");
    $leadQuery->bind_param("i", $leadId);
    $leadQuery->execute();
    $lead = $leadQuery->get_result()->fetch_assoc();

    logDebug("Lead Query Executed: " . json_encode($lead));

    if ($lead) {
        // Populate the response with the lead data
        $response['lead'] = $lead;

        if ($action === 'accept') {
            $user_id = $lead['user_id'];
            $loan_amount = $lead['loan_amount'];

            // Retrieve user details
            $userQuery = $conn->prepare("SELECT national_id_number, name, phone, email FROM users WHERE user_id = ?");
            $userQuery->bind_param("i", $user_id);
            $userQuery->execute();
            $user = $userQuery->get_result()->fetch_assoc();

            if (!$user) {
                $response['message'] = 'User not found.';
                logDebug("User not found for user_id: $user_id");
                echo json_encode($response);
                exit();
            }

            $nationalIdNumber = $user['national_id_number'];
            $name = $user['name'];
            $phone = $user['phone'];
            $email = $user['email'];

            logDebug("User ID and National ID Number Retrieved: " . json_encode($user));

            // Retrieve lender's total savings
            $lenderTotalSavings = getTotalCurrentAmount($conn, $nationalIdNumber);
            logDebug("Lender Total Savings: $lenderTotalSavings");

            if ($lenderTotalSavings >= $loan_amount) {
                // Deduct the loan amount from lender's savings
                $newlenderAmount = $lenderTotalSavings - $loan_amount;

                logDebug("Lender Savings Updated: " . json_encode(['new_amount' => $newlenderAmount]));

                // Retrieve borrower's total savings
                $BorrowerNationalId = $lead['lead_national_id'];
                $BorrowerTotalSavings = getTotalCurrentAmount($conn, $BorrowerNationalId);
                logDebug("Borrower Total Savings: $BorrowerTotalSavings");

                // Add the loan amount to borrower's savings
                $newBorrowerAmount = $BorrowerTotalSavings + $loan_amount;

                logDebug("Borrower Savings Updated: " . json_encode(['new_amount' => $newBorrowerAmount]));

                // Calculate due date and due amount
                $dueDate = date('Y-m-d', strtotime('+30 days'));
                $dueAmount = $loan_amount * 1.10;

                logDebug("Due Date: $dueDate, Due Amount: $dueAmount");

                // Update the lead status to approved and set due date and due amount
                $updateLead = $conn->prepare("UPDATE leads SET status = 'approved', due_date = ?, due_amount = ? WHERE id = ?");
                $updateLead->bind_param("sdi", $dueDate, $dueAmount, $leadId);

                if (!$updateLead->execute()) {
                    $response['message'] = 'Failed to update lead status.';
                    logDebug("Failed to update lead status: " . $conn->error);
                    echo json_encode($response);
                    exit();
                }

                logDebug("Lead Updated: " . json_encode(['status' => 'approved', 'due_date' => $dueDate, 'due_amount' => $dueAmount]));

                // Insert new savings entry for lender (negative amount)
                $insertlenderSavings = $conn->prepare("INSERT INTO savings (name, id_number, phone, email, amount, date, total_current_amount) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                $amountlender = -$loan_amount; // Negative amount for lender
                $insertlenderSavings->bind_param("ssssds", $name, $nationalIdNumber, $phone, $email, $amountlender, $newlenderAmount);

                if (!$insertlenderSavings->execute()) {
                    $response['message'] = 'Failed to insert lender savings.';
                    logDebug("Failed to insert lender savings: " . $conn->error);
                    echo json_encode($response);
                    exit();
                }

                logDebug("Lender Savings Inserted: " . json_encode([
                    'name' => $name, 
                    'id_number' => $nationalIdNumber, 
                    'phone' => $phone, 
                    'email' => $email, 
                    'amount' => $amountlender, 
                    'total_current_amount' => $newlenderAmount
                ]));

                // Insert new savings entry for borrower (positive amount)
                $insertBorrowerSavings = $conn->prepare("INSERT INTO savings (name, id_number, phone, email, amount, date, total_current_amount) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                $amountBorrower = $loan_amount; // Positive amount for borrower
                $insertBorrowerSavings->bind_param("ssssds", $name, $BorrowerNationalId, $phone, $email, $amountBorrower, $newBorrowerAmount);

                if (!$insertBorrowerSavings->execute()) {
                    $response['message'] = 'Failed to insert borrower savings.';
                    logDebug("Failed to insert borrower savings: " . $conn->error);
                    echo json_encode($response);
                    exit();
                }

                logDebug("Borrower Savings Inserted: " . json_encode([
                    'name' => $name, 
                    'id_number' => $BorrowerNationalId, 
                    'phone' => $phone, 
                    'email' => $email, 
                    'amount' => $amountBorrower, 
                    'total_current_amount' => $newBorrowerAmount
                ]));

                // Update the loan_application table
                $updateLoanApplication = $conn->prepare("UPDATE loans_application SET status = 'approved', reason = 'Your loan has been approved and is due in 30 days with 10% interest' WHERE id = ?");
                $updateLoanApplication->bind_param("i", $lead['loan_id']);

                if (!$updateLoanApplication->execute()) {
                    $response['message'] = 'Failed to update loan application status.';
                    logDebug("Failed to update loan application status: " . $conn->error);
                    echo json_encode($response);
                    exit();
                }

                logDebug("Loan Application Updated: " . json_encode([
                    'status' => 'approved', 
                    'reason' => 'Loan approved',
                    'due_date' => $dueDate, 
                    'due_amount' => $dueAmount
                ]));

                // Update the response
                $response['success'] = true;
                $response['message'] = 'Loan has been processed successfully.';
            } else {
                $response['message'] = 'Insufficient savings or lender savings record not found.';
                logDebug("Insufficient savings or lender savings not found for id_number: $nationalIdNumber");
            }
        } elseif ($action === 'reject') {
            // Update the lead status to rejected
            $updateLead = $conn->prepare("UPDATE leads SET status = 'rejected' WHERE id = ?");
            $updateLead->bind_param("i", $leadId);

            if (!$updateLead->execute()) {
                $response['message'] = 'Failed to update lead status.';
                logDebug("Failed to update lead status: " . $conn->error);
                echo json_encode($response);
                exit();
            }

            logDebug("Lead Updated: " . json_encode(['status' => 'rejected']));

            // Update the loan_application table
            $updateLoanApplication = $conn->prepare("UPDATE loans_application SET status = 'denied', reason = 'Your loan application has been rejected.' WHERE id = ?");
            $updateLoanApplication->bind_param("i", $lead['loan_id']);

            if (!$updateLoanApplication->execute()) {
                $response['message'] = 'Failed to update loan application status.';
                logDebug("Failed to update loan application status: " . $conn->error);
                echo json_encode($response);
                exit();
            }

            logDebug("Loan Application Updated: " . json_encode([
                'status' => 'rejected', 
                'reason' => 'Loan application rejected'
            ]));

            // Update the response
            $response['success'] = true;
            $response['message'] = 'Loan application has been rejected.';
        } else {
            $response['message'] = 'Invalid action.';
        }

        // Close queries and connection
        $leadQuery->close();
        if (isset($userQuery)) $userQuery->close();
        if (isset($insertlenderSavings)) $insertlenderSavings->close();
        if (isset($insertBorrowerSavings)) $insertBorrowerSavings->close();
    } else {
        $response['message'] = 'Lead not found.';
        logDebug("Lead not found for ID: $leadId");
    }

    // Close database connection
    $conn->close();

    // Send response
    echo json_encode($response);
}
?>
