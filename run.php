<?php
include "config.php";

$query = $db->query("SELECT count(*) as count FROM oc_product")->fetch(PDO::FETCH_ASSOC);
if ( $query ){
    $count = $query["count"];
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<title>MSSQL</title>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			function get(kontrol){
				$.ajax({
					url:'product.php?count=<?php echo $count; ?>',
					success	:function(data){
						if(data == 'die'){
							$("#return").prepend('Migration Completed');
						} else {
							$("#return").prepend(data);
							get();
						}
					}
				});
			}

			$('#start').click(function(){
				get();
			});	
		});
	</script>

	

	

</head>
<body>
	<input type="button" value="Migration Start" id="start"/>
	<div id="return"></div>
</body>
</html>