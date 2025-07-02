<div class="preloader">
  <div class="preloader-container">
    <div class="dot-spinner">
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
      <div class="dot"></div>
    </div>
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

  .dot-spinner {
    position: relative;
    width: 50px;
    height: 50px;
  }

  .dot {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #3498db; /* Blue dots, adjust color as needed */
    border-radius: 50%;
    animation: dot-spin 1.5s infinite ease-in-out;
    transform-origin: center;
  }

  .dot:nth-child(1) { transform: rotate(0deg) translate(20px); animation-delay: -0.9s; }
  .dot:nth-child(2) { transform: rotate(72deg) translate(20px); animation-delay: -0.75s; }
  .dot:nth-child(3) { transform: rotate(144deg) translate(20px); animation-delay: -0.6s; }
  .dot:nth-child(4) { transform: rotate(216deg) translate(20px); animation-delay: -0.45s; }
  .dot:nth-child(5) { transform: rotate(288deg) translate(20px); animation-delay: -0.3s; }

  @keyframes dot-spin {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(0.5); opacity: 0.5; }
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
