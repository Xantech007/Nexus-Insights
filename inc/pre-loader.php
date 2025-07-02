<div class="preloader">
  <div class="preloader-container">
    <span class="animated-preloader"></span>
  </div>
</div>

<style>
  .preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: transparent; /* Transparent background */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.5s ease; /* Fade-out transition */
  }

  .preloader-container {
    text-align: center;
  }

  .animated-preloader {
    display: inline-block;
    font-size: 24px; /* Adjust as needed */
    animation: spin 1s linear infinite; /* Example animation */
  }

  /* Example animation for the preloader */
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  /* Class to hide preloader */
  .preloader.hidden {
    opacity: 0;
    pointer-events: none; /* Prevents interaction during fade-out */
  }
</style>

<script>
  window.addEventListener('load', () => {
    setTimeout(() => {
      const preloader = document.querySelector('.preloader');
      preloader.classList.add('hidden');
      // Remove preloader from DOM after fade-out completes
      setTimeout(() => {
        preloader.style.display = 'none';
      }, 500); // Matches transition duration
    }, 1000); // 1-second delay after page load
  });
                          </script>
