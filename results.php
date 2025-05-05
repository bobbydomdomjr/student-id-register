<?php
include 'db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lastname = strtoupper(trim($_POST['lastname']));
    $dob = date('Y-m-d', strtotime($_POST['dob']));

    // Check if record exists
    $sql = "SELECT a.applicant_id, a.firstname, a.lastname, a.extensionname, a.middleinitial, e.dateofexam, e.typeofexam, e.regioncode
            FROM applicants a
            LEFT JOIN exam_details e ON a.applicant_id = e.applicant_id
            WHERE a.lastname = '$lastname' AND a.dob = '$dob'";

    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCSERGS Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
        }
        .success {
            background-color: #e6ffed;
            color: #2e7d32;
        }
        .error {
            background-color: #ffebee;
            color: #d32f2f;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
        }
        .back-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Examination Result</h2>

    <?php
    if (isset($result) && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        ?>
        <form action="generate_pdf.php" method="post" target="_blank">
        <div class="result success">
            🎉 <strong>Record Found!</strong>
            <p>Here are the exam details:</p>

            <table>
                <tr>
                    <th>Full Name</th>
                    <td><?php echo $row['firstname'] . " " . $row['middleinitial'] . ". " . $row['lastname'] . " " . $row['extensionname']; ?></td>
                </tr>
                <tr>
                    <th>Date of Exam</th>
                    <td><?php echo date('F d, Y', strtotime($row['dateofexam'])); ?></td>
                </tr>
                <tr>
                    <th>Type of Exam</th>
                    <td><?php echo $row['typeofexam']; ?></td>
                </tr>
                <tr>
                    <th>Region Code</th>
                    <td><?php echo $row['regioncode']; ?></td>
                </tr>
            </table>
        </div>
        <div class="text-center">
        <button type="submit" class="btn btn-primary mt-3">Download PDF Result</button>
</div>

        <?php
    } else {
        ?>
        <div class="result error">
            ❌ <strong>No Record Found!</strong>
            <p>Please double-check the information you entered or contact the administrator.</p>
        </div>
        <?php
    }
    ?>

    <a href="index.html" class="back-btn">⬅️ Back to Home</a>
</div>

</body>
</html>
