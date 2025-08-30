<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Fetch ID number from GET request
$idno = isset($_GET['idno']) ? $_GET['idno'] : null;

// Handle form submission for eTims invoice upload
if (isset($_POST['submitetimsinvoice'])) {
    $idno = $_POST['idno'];
    $projectfullname = $_POST['projectfullname'];

    // Handle file upload
    $pdfFile = $_FILES['pdfFile'];
    $uploadDirectory = 'etimsinvoices/';
    $pdfFileName = basename($pdfFile['name']);
    $uploadedFilePath = $uploadDirectory . $pdfFileName;

    // Validate file upload
    if ($pdfFile['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($pdfFile['tmp_name'], $uploadedFilePath)) {
            // Update database with the uploaded file info
            $sql = "UPDATE projectlistentries 
                    SET etimsinvoice = :pdfFileName 
                    WHERE idno = :idno AND projectfullname = :projectfullname";
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':pdfFileName', $pdfFileName, PDO::PARAM_STR);
            $stmt->bindParam(':idno', $idno, PDO::PARAM_STR);
            $stmt->bindParam(':projectfullname', $projectfullname, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Invoice uploaded successfully.";
            } else {
                $_SESSION['message'] = "Failed to update database.";
            }
        } else {
            $_SESSION['message'] = "Failed to upload the file.";
        }
    } else {
        $_SESSION['message'] = "File upload error: " . $pdfFile['error'];
    }
}
// Define the absolute path for PDF files
$baseUploadPath = "C:/xampp/htdocs/admin3/etimsinvoices/"; // Absolute path
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kipmetz-SMS | Field Staff Details</title>
    <!-- Core CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
        /* Header and Footer Styles */
        .header {
            background: linear-gradient(to right, #0166CC, #000000, #0166CC);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .footer {
            background: linear-gradient(to right, #0166CC, #000000, #0166CC);
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.2);
        }

        .footer a {
            color: #FFBF00;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* Table and Form Styles */
        .panel-primary {
            margin: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }

        .table-striped tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 8px 16px;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
    <!-- Optional: Add some CSS for better styling -->
<style>
    .staff-details {
        width: 100%;
        margin-bottom: 20px;
        border-collapse: collapse;
    }

    .staff-details td {
        padding: 10px;
        border: 1px solid #ddd;
    }

    .staff-id {
        color: #007bff; /* Blue color for ID */
        margin-bottom: 10px;
    }

    .staff-name {
        color: #28a745; /* Green color for staff name */
        margin-bottom: 20px;
    }

    .instructions {
        background-color: #f8f9fa; /* Light gray background */
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .instructions h5 {
        color: #333; /* Dark gray for heading */
        margin-bottom: 10px;
    }

    .instructions p {
        color: #555; /* Gray for text */
        margin: 0;
    }
</style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        Research Plus Africa - Field Staff Details
    </div>

    <div>
        <?php if (!empty($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php 
                    echo htmlentities($_SESSION['message']); 
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>
    </div>

    <table class="staff-details">
    <tr>
        <td colspan="4">
            <!-- Staff ID Number -->
            <h4 class="staff-id">IDNo: <?php echo htmlentities($idno); ?></h4>

            <!-- Staff Name -->
            <h4 class="staff-name">
                <?php
                // Fetch staff name based on ID number
                $sql = "SELECT staffname FROM staffdetails WHERE idno = :idno";
                $stmt = $dbh->prepare($sql);
                $stmt->bindParam(':idno', $idno, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo $result ? "Staff Name: " . htmlentities($result['staffname']) : "Staff Name not found.";
                    $staffname= htmlentities($result['staffname']);
                } else {
                    echo "Error fetching staff name.";
                }
                ?>
            </h4>

            <!-- Instructions -->
            <div class="instructions">
                <h5>Instructions:</h5>
                <p>
                    From the list of projects you have engaged, click on the <strong>Attach/Update</strong> button on the right side of the project to attach an eTIMs invoice. 
                    You can preview the invoice by clicking on it.
                </p>
            </div>
        </td>
    </tr>
</table>



    <div class="panel panel-primary">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Project Full Name</th>
                        <th>Title</th>
                        <th>Completion</th>
                        <th>eTims Invoice</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($idno) {
                        $sql = "SELECT * FROM projectlistentries 
                                JOIN projectdetails ON projectlistentries.projectfullname = projectdetails.projectfullname 
                                WHERE projectlistentries.idno = :idno 
                                ORDER BY projectlistentries.id DESC";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':idno', $idno, PDO::PARAM_STR);

                        if ($query->execute()) {
                            $results = $query->fetchAll(PDO::FETCH_ASSOC);

                            if ($results) {
                                $count = 1;

                                foreach ($results as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlentities($count++) . "</td>";
                                    echo "<td>" . htmlentities($row['projectfullname']) . "</td>";
                                    echo "<td>" . htmlentities($row['projectdesignation']) . "</td>";
                                    echo "<td>" . htmlentities($row['projectcompletion']) . "</td>";

                                    // PDF File Handling
                                    echo "<td>";
                                    if (!empty($row['etimsinvoice'])) {
                                        $pdfUrl = "http://localhost/admin3/etimsinvoices/" . rawurlencode($row['etimsinvoice']);
                                        $pdfPath = $baseUploadPath . htmlentities($row['etimsinvoice']);

                                        if (file_exists($pdfPath)) {
                                            echo '<a href="#" class="view-pdf" data-pdf="' . $pdfUrl . '">' . htmlentities($row['etimsinvoice']) . '</a>';
                                        } else {
                                            echo 'PDF not found';
                                        }
                                    } else {
                                        echo 'No File uploaded';
                                    }
                                    echo "</td>";

                                    // Invoice Upload Form
                                    echo "<td>";
                                    echo '<form method="post" action="etimsinvoicefieldteaminvoiceupload.php" enctype="multipart/form-data">';
                                    echo '<input type="hidden" name="idno" value="' . htmlentities($idno) . '">';
                                    echo '<input type="hidden" name="staffname" value="' . htmlentities($staffname) . '">';
                                    echo '<input type="hidden" name="projectfullname" value="' . htmlentities($row['projectfullname']) . '">';
                                    echo '<button type="submit" class="btn btn-primary">Attach/Update Invoice</button>';
                                    echo '</form>';
                                    echo "</td>";

                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No records found.</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Error executing query.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Invalid ID number.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        &copy; <?php echo date("Y"); ?> Research Plus Africa. All rights reserved. | 
       
    </div>

    <!-- PDF Modal -->
    <div id="pdfModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <iframe id="pdfViewer" width="100%" height="500px"></iframe>
        </div>
    </div>

    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            $('#dataTables-example').dataTable();
        });

        // PDF Modal functionality
        const modal = document.getElementById("pdfModal");
        const pdfViewer = document.getElementById("pdfViewer");
        const closeBtn = document.querySelector(".close");

        // Open modal when a PDF link is clicked
        document.querySelectorAll(".view-pdf").forEach(link => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                const pdfUrl = this.getAttribute("data-pdf");
                console.log("PDF URL:", pdfUrl); // Debugging: Log the PDF URL
                if (pdfUrl) {
                    pdfViewer.setAttribute("src", pdfUrl);
                    modal.style.display = "block";
                } else {
                    alert("PDF not found!");
                }
            });
        });

        // Close modal when the close button is clicked
        closeBtn.addEventListener("click", function () {
            modal.style.display = "none";
            pdfViewer.setAttribute("src", ""); // Clear the iframe
        });

        // Close modal when clicking outside the modal
        window.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.style.display = "none";
                pdfViewer.setAttribute("src", ""); // Clear the iframe
            }
        });
    </script>
</body>
</html>