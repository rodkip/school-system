<div id="spinnerOverlay"></div>
<div id="spinner">
  <div class="loading">
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
    <span></span>
  </div>
  <p>Be patient! The table is loading in a moment...</p>
</div>

<style>
  /* Overlay to block interactions */
  #spinnerOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    z-index: 9998;
    display: none;
  }

  /* Modern styling for the spinner container */
  #spinner {
  position: fixed; /* Make sure it's always in the same place */
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* Center the spinner */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  height: auto; /* No need for full viewport height */
  background: transparent; /* Set the background to transparent */
  z-index: 9999;
  opacity: 0;
  animation: fadeInUp 0.6s ease-in-out forwards;
}


  #spinner p {
    margin-top: 20px;
    font-size: 2rem; /* Increased font size */
    color: #4a4a4a; /* Darker text for better contrast */
    font-weight: 500;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Modern font */
  }

  /* Modern spinner styling */
  .loading {
    --speed-of-animation: 1.2s;
    --gap: 12px; /* Increased gap for a more spacious look */
    --first-color: #ff6b6b; /* Vibrant colors */
    --second-color: #4ecdc4;
    --third-color: #ffe66d;
    --fourth-color: #ff6b6b;
    --fifth-color: #4ecdc4;
    --sixth-color: #ffe66d;
    --seventh-color: #ff6b6b;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 180px; /* Increased width */
    height: 180px; /* Increased height */
    gap: var(--gap);
    margin: 0 auto;
  }

  .loading span {
    width: 20px; /* Increased width for larger bars */
    height: 90px; /* Increased height for a bigger effect */
    background: var(--first-color);
    border-radius: 6px; /* Rounded corners */
    animation: scale var(--speed-of-animation) ease-in-out infinite;
  }

  .loading span:nth-child(2) {
    background: var(--second-color);
    animation-delay: -0.8s;
  }

  .loading span:nth-child(3) {
    background: var(--third-color);
    animation-delay: -0.7s;
  }

  .loading span:nth-child(4) {
    background: var(--fourth-color);
    animation-delay: -0.6s;
  }

  .loading span:nth-child(5) {
    background: var(--fifth-color);
    animation-delay: -0.5s;
  }

  .loading span:nth-child(6) {
    background: var(--sixth-color);
    animation-delay: -0.4s;
  }

  .loading span:nth-child(7) {
    background: var(--seventh-color);
    animation-delay: -0.3s;
  }

  /* Smoother animation */
  @keyframes scale {
    0%, 40%, 100% {
      transform: scaleY(0.1);
    }
    20% {
      transform: scaleY(1);
    }
  }

  @keyframes fadeInUp {
    0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
    100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
  }
</style>

<script>
  // Function to show the spinner and block page interactions
  function showSpinner() {
    document.getElementById('spinnerOverlay').style.display = 'block'; // Show the overlay
    document.getElementById('spinner').style.opacity = 1; // Show the spinner
    document.body.style.pointerEvents = 'none'; // Disable body interaction (blocks user actions)
  }

  // Function to hide the spinner and restore page interactions
  function hideSpinner() {
    document.getElementById('spinnerOverlay').style.display = 'none'; // Hide the overlay
    document.getElementById('spinner').style.opacity = 0; // Hide the spinner
    document.body.style.pointerEvents = 'auto'; // Re-enable body interaction
  }

  // Simulate table loading and hide the spinner after completion
  // Replace with actual logic for when your table is loaded
  setTimeout(hideSpinner, 5000); // Simulate hiding the spinner after 5 seconds (use actual loading logic here)

  // Show spinner when the page starts loading the table
  showSpinner();
</script>

