<div class="preloader" id="preloader">
  <div class="preloader-container">
    <span class="animated-preloader"></span>
  </div>
</div>

<style>
  .preloader {
    background-color: black; /* Sets black background */
    position: fixed; /* Ensures it covers the screen */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999; /* Keeps preloader on top */
  }
</style>

<script>
  window.addEventListener('load', () => {
    setTimeout(() => {
      document.getElementById('preloader').style.display = 'none';
    }, 1000); // Hides preloader 1 second after page load
  });
</script>
