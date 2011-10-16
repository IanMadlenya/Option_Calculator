<form action="./integration.php" method="post">
<?php 
if (isset($_POST['submitted'])){
	if($_POST['n']=='') $n=100;
	else $n=$_POST['n'];
	if($_POST['a']=='') $a=0;
	else $a=$_POST['a'];
	if($_POST['b']=='') $b=1;
	else $b=$_POST['b'];
	if($_POST['user_func']=='') $user_func='x';
	else $user_func=$_POST['user_func'];
}else{
	$n=100; $a=0; $b=1; $user_func='x';
}
?>
function with independent variable x: </br> <input type="text" size="10" name="user_func"  <?php if(isset($user_func)) echo "value=$user_func"; ?>></br>
n:</br> <input type="text" size="10" name="n"  <?php if(isset($n)) echo "value=$n"; ?>></br>
upper limit:</br> <input type="text" size="10" name="a" <?php if(isset($a)) echo "value=$a"; ?>></br>
lower limit:</br> <input type="text" size="10" name="b" <?php if(isset($b)) echo "value=$b"; ?>></br>
<input type="hidden" name="submitted" value="yes">
<input type="submit" value="integrate"></br>

</form>

<?php 
if (isset($_POST['submitted'])){
	include("simpson_integrater.php"); 
	echo "$user_func integrated from $a to $b equals $out";
}
?>