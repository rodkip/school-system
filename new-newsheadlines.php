<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else
  
  {
    $eid=$_GET['editid']; 
    
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Kipmetz-SMS|News Headlines</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
      .blue-text {
        color: blue;
        background: lightskyblue;
      }
      #ageMessage {
      color: red;
    }

    #ageMessage.valid {
      color: green;      
    }
    body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            
            color: #444;
        }
        .news-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
          
            margin: 0 auto;
        }
        .headline {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 5px;
            transition: transform 0.2s;
        }
        .headline:hover {
            transform: translateY(-5px);
        }
        .headline a {
            text-decoration: none;
            color: #0073e6;
            font-size: 18px;
            font-weight: bold;
        }
        .headline a:hover {
            text-decoration: underline;
        }
        .source {
            font-size: 14px;
            color: #777;
            margin-top: 8px;
        }
    </style>
  </head>
  <body>
    <!--  wrapper -->
    <div id="wrapper">
      <!-- navbar top --> <?php include_once('includes/header.php');?>
      <!-- end navbar top -->
      <!-- navbar side --> <?php include_once('includes/sidebar.php');?>
      <!-- end navbar side -->
      <!--  page-wrapper -->
      <div id="page-wrapper">
        <div class="row">
          <!-- page header -->
          <div class="col-lg-12">
            <br>
            <h1 class="page-header">News Headline <i class="fa fa-user" aria-hidden="true"></i></h1>
          </div>
          <!--end page header -->
        </div>
        <div class="row">
          <div class="col-lg-12">
            <!-- Form Elements -->
            <div class="panel panel-default">
              <div class="panel-body">
                <div class="row">
                  <div class="col-lg-12">
                    <div background-color="green"> <?php echo $_SESSION['message']; 
       unset($_SESSION['message'])
    ?> 
     <h1>Latest News Headlines in Kenya</h1>
   
    <div id="news-container" class="news-container"></div>

    <script>
        const apiKey = '8507bb8d5a60c942c6b44202cb4ad8e7'; // Replace with your Mediastack API key
        const newsContainer = document.getElementById('news-container');

        async function fetchNews() {
            try {
                const response = await fetch(`http://api.mediastack.com/v1/news?access_key=${apiKey}&countries=ke&languages=en&limit=20`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                if (data.data && data.data.length > 0) {
                    displayNews(data.data);
                } else {
                    newsContainer.innerHTML = "<p>No news available at the moment.</p>";
                }
            } catch (error) {
                console.error("Error fetching news:", error);
                newsContainer.innerHTML = "<p>There was an error fetching the news. Please try again later.</p>";
            }
        }

        function displayNews(articles) {
            newsContainer.innerHTML = ''; // Clear previous headlines
            articles.forEach(article => {
                const newsDiv = document.createElement('div');
                newsDiv.classList.add('headline');
                newsDiv.innerHTML = `
                    <a href="${article.url}" target="_blank">${article.title}</a>
                    <div class="source">Source: ${article.source}</div>
                `;
                newsContainer.appendChild(newsDiv);
            });
        }

        fetchNews();
        setInterval(fetchNews, 60000); // Refresh every 60 seconds
    </script>
       </div>
                  </div>
                  
                </div>
              </div>
              <!-- End Form Elements -->
            </div>
          </div>
        </div>
        <!-- end page-wrapper -->
      </div>
      <!-- end wrapper -->
      <!-- Core Scripts - Include with every page -->
      <script src="assets/plugins/jquery-1.10.2.js"></script>
      <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
      <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
      <script src="assets/plugins/pace/pace.js"></script>
      <script src="assets/scripts/siminta.js"></script>
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          var disabilityYes = document.getElementById("disability_yes");
          var disabilityTypeSelect = document.getElementById("disabilitytype");
          // Initial check on page load
          toggleDisabilityType(disabilityYes.checked);
          // Add event listener for radio button change
          disabilityYes.addEventListener("change", function() {
            toggleDisabilityType(disabilityYes.checked);
          });

          function toggleDisabilityType(isYesChecked) {
            disabilityTypeSelect.disabled = !isYesChecked;
          }
        });
      </script>
          <script>
    function checkAge() {
      // Get the input value
      var dobInput = document.getElementById('dob').value;

      // Convert the input value to a Date object
      var dob = new Date(dobInput);

      // Get the current date
      var currentDate = new Date();

      // Calculate the age
      var age = currentDate.getFullYear() - dob.getFullYear();

      // Get the message element
      var ageMessage = document.getElementById('ageMessage');

      // Check if the age is below 18
      if (age < 18) {
        ageMessage.innerHTML = 'The staff must be at least 18 years old. Check the DOB again';
        ageMessage.classList.remove('valid'); // Remove the 'valid' class
      } else {
        ageMessage.innerHTML = 'Valid age!';
        ageMessage.classList.add('valid'); // Add the 'valid' class
        // You can add additional logic here for further processing
      }
    }
  </script>
   <script>
          $(document).ready(function() {
            $('#dataTables-example').dataTable();
          });
        </script>
        <script>
          if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
          }
        </script>
        <script>
          function idnoregavailability() {
            $("#loaderIcon").show();
            // Show a loading icon or animation
            jQuery.ajax({
              url: "checkstaffidno.php", // URL of the PHP script to be called
              data: 'idno=' + $("#idno").val(), // Send the value of an element with the ID "idNo" as the 'idNo' parameter
              type: "POST", // Use the HTTP POST method
              success: function(data) {
                // Function to handle the response on success
                $("#idnoavailabilitystatus").html(data);
                // Update the HTML content of an element with the ID "idnoavailabilitystatus" with the response data
                $("#loaderIcon").hide();
                // Hide the loading icon
              },
              error: function() {
                // Function to handle errors (currently empty)
                // You can add error handling code here if needed
              }
            });
          }
        </script>
        <script>
          // Add autofocus to the cancel button and trigger Cancel on Enter key press
          document.addEventListener('DOMContentLoaded', function() {
            var cancelButton = document.getElementsByName('delete')[0];
            cancelButton.focus();
            document.addEventListener('keydown', function(event) {
              if (event.key === 'Enter') {
                event.preventDefault();
                cancelButton.click();
              }
            });
          });
        </script>
        <script>
          // Add matches Idno with MpesaIdno
  function checkIdnoMpesaidnoMatch() {
    var idnoValue = document.getElementById('idno').value;
    var mpesaidnoValue = document.getElementById('mpesaidno').value;
    var matchStatusElement = document.getElementById('mpesaidnoMatchStatus');

    if (idnoValue === mpesaidnoValue) {
      matchStatusElement.innerHTML = 'This MpesaIdNo is matching the staff IdNo!';
      matchStatusElement.style.color = 'green';
    } else {
      matchStatusElement.innerHTML = 'This MpesaIdNo is not matching the staff IdNo!';
      matchStatusElement.style.color = 'red';
    }
  }
</script>
        <script>
          // Get today's date
          var today = new Date().toISOString().split('T')[0];
          // Set the value of the input field to today's date
          document.getElementById('dob').value = today;
        </script>
        <script>
          $(document).ready(function() {
            $("input[name='disabilitytype']").change(function() {
              if ($(this).val() === "Yes") {
                $("select[name='disabilitytype']").prop("disabled", false);
              } else {
                $("select[name='disabilitytype']").prop("disabled", true);
              }
            });
          });
        </script>
        <script>
          document.addEventListener("DOMContentLoaded", function() {
            var disabilityYes = document.getElementById("disability_yes");
            var disabilityTypeSelect = document.getElementById("disabilitytype");
            // Initial check on page load
            toggleDisabilityType(disabilityYes.checked);
            // Add event listener for radio button change
            disabilityYes.addEventListener("change", function() {
              toggleDisabilityType(disabilityYes.checked);
            });

            function toggleDisabilityType(isYesChecked) {
              disabilityTypeSelect.disabled = !isYesChecked;
            }
          });
        </script>
    <script>
    function checkAge() {
      // Get the input value
      var dobInput = document.getElementById('dob').value;

      // Convert the input value to a Date object
      var dob = new Date(dobInput);

      // Get the current date
      var currentDate = new Date();

      // Calculate the age
      var age = currentDate.getFullYear() - dob.getFullYear();

      // Get the message element
      var ageMessage = document.getElementById('ageMessage');

      // Check if the age is below 18
      if (age < 18) {
        ageMessage.innerHTML = 'The staff must be at least 18 years old. Check the DOB again';
        ageMessage.classList.remove('valid'); // Remove the 'valid' class
      } else {
        ageMessage.innerHTML = 'Valid age!';
        ageMessage.classList.add('valid'); // Add the 'valid' class
        // You can add additional logic here for further processing
      }
    }
  </script>
  <script>
    // Function to handle file input change
    document.getElementById('pfpicname').addEventListener('change', function(event) {
        var file = event.target.files[0]; // Get the selected file

        if (file) {
            var reader = new FileReader();

            reader.onload = function(e) {
                // Set the background image of the target td
                document.getElementById('profileImageCell').style.backgroundImage = `url('${e.target.result}')`;
                document.getElementById('profileImageCell').style.backgroundRepeat = 'no-repeat';
                document.getElementById('profileImageCell').style.backgroundPosition = 'center center';
                document.getElementById('profileImageCell').style.backgroundSize = 'cover';
            };

            // Read the selected file as a data URL
            reader.readAsDataURL(file);
        }
    });
</script>

  </body>
</html>