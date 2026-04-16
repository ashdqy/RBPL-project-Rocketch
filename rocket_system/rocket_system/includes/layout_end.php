
  </div><!-- .content -->
</div><!-- #main -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-close alerts
setTimeout(()=>{ document.querySelectorAll('.alert-auto').forEach(el=>{ el.style.transition='opacity .5s'; el.style.opacity='0'; setTimeout(()=>el.remove(),500); }); }, 3000);
</script>
</body>
</html>
