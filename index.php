<html>
<head><title>Option Calculator</title>
<style>
div.row {
  clear: both;
  padding-top: 5px;
  }

div.row span.label {
  float: left;
  width: 60%;
  text-align: right;
  }

div.row span.formw {
  float: right;
  width: 35%;
  text-align: left;
  } 
</style>
</head>
<body>

<?php

$price = 100;
$duration = 100;
$interest = 5;
$vol = 20;

if(isset($_POST['retrieve_submit']) || isset($_POST['submitted'])){
	if($_POST['symbol']!='' && isset($_POST['symbol'])){
		include("retrieve_stock.php");
		$symbol = $_POST['symbol'];
		$price = $quote[2];	
	}else{
		$price = 100;
		$symbol = '';
	}
}else{
	$symbol = '';
}

echo <<<_END

<form action="./index.php" method="post">
	Stock Symbol: </br>
	<input type="text" size="5" name="symbol" value=$symbol>
	<input type="hidden" name="retrieve_submit">
	<input type="submit" value="retrieve current price">
</form>
</br>

<div style="width: 250px; background-color: #ccc;
border: 1px dotted #333; padding: 5px;">
<form action="./index.php" method="post">

Option Type: </br>
<select name="option_type">
<option>European</option>
<option>American</option>
</select>
</br></br>

<div class="row">
  <span class="label">Current Stock Price ($):</span><span class="formw"><input type="text" size="5" name="stock" value=$price /></span>
</div>
<div class="row">
  <span class="label">Strike Price ($):</span><span class="formw"><input type="text" size="5" name="strike" value=$price /></span>
</div>
<div class="row">
  <span class="label">Duration (days):</span><span class="formw"><input type="text" size="5" name="duration" value=$duration /></span>
</div>
<div class="row">
  <span class="label">Interest rate (%):</span><span class="formw"><input type="text" size="5" name="interest" value=$interest /></span>
</div>
<div class="row">
  <span class="label">Volatility (%):</span><span class="formw"><input type="text" size="5" name="vol" value=$vol /></span>
</div>
<input type="hidden" name="submitted">
<input type="hidden" name="symbol" value=$symbol>
<input type="submit" value="calculate option prices">

</form>

</div>

_END;
echo "<br>";
if (isset($_POST['submitted'])){
	include("calculate.php"); 
}

?>
	

</body>
</html>