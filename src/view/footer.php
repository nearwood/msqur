<div class="footer">
	<?php if (@readfile("VERSION") === FALSE) echo "DEV"; ?> <a href="http://httpd.apache.org/">Apache</a> <a href="http://php.net/">PHP</a> <a href="https://angularjs.org/">AngularJS</a> <a href="http://jquery.com/">jQuery</a>
</div>
<?php
if (!LOCAL)
{
	?>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new	Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('create', 'UA-5743113-10', 'auto');
		ga('send', 'pageview');
	</script>
	<?php
}
?>
</body>
</html>
