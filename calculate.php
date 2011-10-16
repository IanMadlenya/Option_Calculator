<?php
$trade_days=365;
//number of decimal places in outputs
$num_dec = 4;
$type = ($_POST['option_type']);
$S0 = ($_POST['stock']);
$K = ($_POST['strike']);
$T = ($_POST['duration'])/$trade_days;
$r = ($_POST['interest'])/100;
$sigma = ($_POST['vol'])/100;

$d1 = (log($S0/$K)+($r+pow($sigma,2)/2)*$T)/($sigma*sqrt($T));
$d2 = (log($S0/$K)+($r-pow($sigma,2)/2)*$T)/($sigma*sqrt($T));
	
$C = $S0*N($d1)-$K*exp(-$r*$T)*N($d2);
$C_delta = N($d1);
$C_theta = (-$S0*Ndist($d1)*$sigma/(2*sqrt($T))-$r*$K*exp(-$r*$T)*N($d2))/$trade_days;
$C_gamma = Ndist($d1)/($S0*$sigma*sqrt($T));
$C_vega = $S0*sqrt($T)*Ndist($d1)/100;
$C_rho = $K*$T*exp(-$r*$T)*N($d2)/100;

if($type=="European"){
	$P = $K*exp(-$r*$T)*N(-$d2)-$S0*N(-$d1);
	$P_delta = N($d1)-1;
	$P_theta = (-$S0*Ndist($d1)*$sigma/(2*sqrt($T))+$r*$K*exp(-$r*$T)*N(-$d2))/$trade_days;
	$P_gamma = Ndist($d1)/($S0*$sigma*sqrt($T));
	$P_vega = $S0*sqrt($T)*Ndist($d1)/100;
	$P_rho = -$K*$T*exp(-$r*$T)*N(-$d2)/100;

}else{
	$n = 500;
	$f = pricer($S0,$K,$T,$r,$sigma,$n);

	$f0 = $f[0]; $f01=$f[1]; $f11=$f[2]; $f02=$f[3]; $f12=$f[4]; $f22=$f[5]; 
    
    //at each node the stock price can increase by a factor of u or decrease by a factor of d
	$delta = $T/$n;
    $u = exp($sigma*sqrt($delta));
    $d = exp(-$sigma*sqrt($delta));

	$P = $f0;
    $P_delta = ($f01-$f11)/($S0*$u-$S0*$d);
    $P_theta = ($f12-$f0)/(2*$delta);
    $P_gamma = (($f02-$f12)/($S0*pow($u,2)-$S0)-($f12-$f22)/($S0-$S0*pow($d,2)))/(0.5*($S0*pow($u,2)-$S0*pow($d,2)));
	
	$eps = 0.01;
	//Vega
	$f0_vstar = pricer($S0,$K,$T,$r,$sigma*(1+$eps),$n);
	$P_vega = $eps*($f0_vstar[0]-$f0)/($sigma*$eps);
 
	//Rho
	$f0_rstar = pricer($S0,$K,$T,$r*(1+$eps),$sigma,$n);
	$P_rho = $eps*($f0_rstar[0]-$f0)/($r*$eps);
}

echo "
<table border='1'>
<tr>
<th></th>
<th>Call</th>
<th>Put</th>
</tr><tr>
<td>Price</td>
<td>$".round($C, 2)."</td>
<td>$".round($P, 2)."</td>
</tr><tr>
<td>Delta</td>
<td>".round($C_delta,$num_dec)."</td>
<td>".round($P_delta,$num_dec)."</td>
</tr><tr>
<td>Gamma</td>
<td>".round($C_theta,$num_dec)."</td>
<td>".round($P_theta,$num_dec)."</td>
</tr><tr>
<td>Vega</td>
<td>".round($C_gamma,$num_dec)."</td>
<td>".round($P_gamma,$num_dec)."</td>
</tr><tr>
<td>Theta</td>
<td>".round($C_vega,$num_dec)."</td>
<td>".round($P_vega,$num_dec)."</td>
</tr><tr>
<td>Rho</td>
<td>".round($C_rho,$num_dec)."</td>
<td>".round($P_rho,$num_dec)."</td>
</tr>
</table> ";


function Ndist($x){
//normal distribution function with mean=0 and var=1
	return exp(-$x*$x/2)/sqrt(2*pi());
}

function N($x){
//normal cumulative distribution function
	$func = 'exp(-x*x/2)/sqrt(2*pi())';
	return integrate(1000, -10, $x, $func);
}

function integrate($n, $a, $b, $user_func){
//integrate $user_func from $a to $b using simpson's integration with $n iterations
	$h = ($b-$a)/$n;
	$sum1 = func($user_func, ($a+$h/2));
	$sum2 = 0;

	for ($i=1; $i<$n; $i++)
	{
		$sum1 = $sum1 + func($user_func, ($a+($h*$i)+($h/2)));
		$sum2 = $sum2 + func($user_func, ($a+($h*$i)));
	}

	$out = ($h/6)*(func($user_func, $a)+func($user_func, $b)+(4*$sum1)+(2*$sum2));
	return $out;
}

function func($user_func, $x){
//returns the value of $user_func with argument $x
	$fun_pos=strlen($user_func)-1;
	while($fun_pos>=0){
		if($user_func[$fun_pos]=='x'){
			if($fun_pos>0 && $fun_pos<(strlen($user_func)-1)){
			if($user_func[$fun_pos+1]!='p' && $user_func[$fun_pos-1]!='a'){
				$user_func = substr_replace($user_func,"$",$fun_pos,0);
			}
			}else{$user_func = substr_replace($user_func,"$",$fun_pos,0);}
		}
		$fun_pos--;
	}
	eval("\$out=$user_func;");
	return $out;
}

function pricer($S0,$K,$T,$r,$sigma,$n){
	//time step
	$delta = $T/$n;
    
    //at each node the stock price can increase by a factor of u or decrease by a factor of d
    $u = exp($sigma*sqrt($delta));
    $d = exp(-$sigma*sqrt($delta));
	
    
    //growth factor
    $a = exp($r*$delta);
    
    //probability of stock price increase by factor u
    $p = ($a-$d)/($u-$d);
    
    for($k=$n; $k>=0; $k--){
        for($i=0; $i<=$k; $i++){
            $S_temp = $S0*pow($d,$i)*pow($u,($k-$i));
            if($k==$n){
                $f[$i]=max($S0-$S_temp,0);
            }else{
                $f[$i]=max($K-$S_temp,($p*$f[$i]+(1-$p)*$f[$i+1])*exp(-$r*$delta));
            }            
        }
		if($k==2){
			$f22 = $f[2];
			$f12 = $f[1];
			$f02 = $f[0];
		}
		if($k==1){
			$f11 = $f[1];
			$f01 = $f[0];
		}
    }
	
	$f[1]=$f01; $f[2]=$f11; $f[3]=$f02; $f[4]=$f12; $f[5]=$f22; 
	return $f;
}

?>