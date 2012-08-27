<?php
function stndev($set){
	$amount = count($set);
	$mean = array_sum($set) / $amount;
        $difference = array();
	foreach($set as $value)
	{
		$difference[] = pow($value - $mean, 2);
	}

	return pow(array_sum($difference) / $amount, 0.5);
}
?>
