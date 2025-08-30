<?php
// Function to generate a unique random ID number
function generateUniqueRandomIdNo()
{
    global $dbh, $prefix;

    // Generate a random ID number with prefix
    $idno = generateRandomIdNo($prefix);

    // Check if the generated ID number already exists in the database
    while (isIdNoDuplicate($idno)) {
        // If the generated ID number is a duplicate, generate a new random number with prefix
        $idno = generateRandomIdNo($prefix);
    }

    return $idno;
}

// Function to generate a random ID number with prefix
function generateRandomIdNo($prefix)
{
    // Generate a random number within the desired range
    $min = 10000000;
    $max = 99999999;
    $randomNumber = mt_rand($min, $max);

    // Concatenate the prefix and the random number
    $idno = $prefix . $randomNumber;

    return $idno;
}


// Function to check if idno already exists in staffdetails table
function isIdNoDuplicate($idno)
{
    global $dbh;

    $sql = "SELECT * FROM staffdetails WHERE idno = :idno";
    $query = $dbh->prepare($sql);
    $query->bindParam(':idno', $idno, PDO::PARAM_STR);
    $query->execute();

    return $query->rowCount() > 0;
}
?>
