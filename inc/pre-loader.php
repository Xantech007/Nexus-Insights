<div class="preloader" id="preloader">
  <div class="preloader-container">
    <span class="animated-preloader"></span>
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    setTimeout(() => {
      document.getElementById('preloader').style.display = 'none';
    }, 1000); // Hides preloader 1 second after page load
  });
</script>
