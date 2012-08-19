<?php 
require('expression.php');
require('spline.php');
require('utils.php');
/**
 * Post parameters: formula
 * Change text formula into latex
 * Return JavaScript JSON readable object {status: "success|error", data:[[x,y],...]}
 * where data is an array of n points for plotting by Flotr
 * If input has error return {status:"error",value:"Error message"}
 *
 * If expression=spline, there must be additional arguments:
 *    points=[[xi,y1,d1],[x2,y2,d2],...] containing spline control points
 *    fit=true | false saying whether or not fit the 1st derivatives to minimise curvature
 *    
 */
if(isset($_REQUEST["expression"]) and
   isset($_REQUEST["xrange"]) and
   isset($_REQUEST["n"])
   )
{
	$xrange = json_decode($_REQUEST["xrange"]);
	$range_n = sizeof($xrange);
	if ($range_n % 2 != 0)
	{
		exit('{status:"error",value:"xrange must have even number of entries: each pair define an x interval "}');
	}
	$xmin = $xrange[0];
	$xmax = $xrange[$range_n-1];
	$n_of_intv = $range_n / 2; // number of contiguous intervals in the x range
	$n = $_REQUEST["n"];
	if ($n <= 2)
	{
	exit('{status:"error",value:"n must be >= 2 "}');
	}
	$dX = ($xmax - $xmin) / ($n);

	try
	{
		$fun = '['; // output string with plottable data
		
		for($intv = 0; $intv < $n_of_intv; $intv++)
		{
			$xvalues = array();
			$yvalues = array();
			$data = array();
			
			$xmin = $xrange[$intv*2];
			$xmax = $xrange[$intv*2+1];
			$dx = $dX;
			if ($dx >= $xmax - $xmin)
			{
				$dx = $xmax - $xmin;
				$n = 2;
			}
			else
			{
				$n = ($xmax - $xmin) / $dx + 1;
			}
			
			for($i = 0; $i < $n; $i += 1)
			{
				array_push($xvalues,$xmin + $i * $dx);
			}
			//exit('{status:"error",value:'.json_encode($xvalues).',xmin:'.$xmin.',xmax:'.$xmax.',dx:'.$dx.',n:'.$n.'}');
			
			$expr_str = decode($_REQUEST["expression"]);
			if ($expr_str == 'spline')
			{
				if(!isset($_REQUEST["points"]))
				{
					exit('{status:"error",value:"Points not given for spline"}');
				}
				$points = json_decode($_REQUEST["points"]);
				$do_fit = false;
				if (isset($_REQUEST["fit"]))
				{
					$do_fit = $_REQUEST["fit"];
				}
				if (sizeof($points) == 0)
				{
					exit('{status:"error",value:"Empty points array given for spline"}');
				}
				$spline = new Spline();
				$spline->add_points($points);
				//exit('{status:"error",value:'.json_encode($points).'}');
				if ($do_fit)
				{
					$spline->fit_derivs();
				}
				else
				{
					$spline->fit();
				}
				$x_first = $spline->points[0]->x;
				$x_last = $spline->points[sizeof($spline->points)-1]->x;
				$x_arr = array();
				$m = sizeof($xvalues);
				$empty = true;
				for($i = 0; $i < $m; $i += 1)
				{
					$x =$xvalues[$i]; 
					if ($x >= $x_first && $x <= $x_last)
					{
						if ($empty && $x > $x_first)
						{
							array_push($x_arr,$x_first);
						}
						array_push($x_arr,$x);
						$empty = false;
					}
				}
				$xvalues = $x_arr;
				if (sizeof($xvalues) > 0)
				{
					$yvalues = $spline->calc_array($xvalues);
				}
				else
				{
					$yvalues = array();
				}
				//exit('{status:"stuff",data:[],x:'.json_encode($xvalues).',y:'.json_encode($yvalues).',pnt:'.json_encode($points).'}');
			}
			else // treat expression as a formula for a function
			{
				$expr = new Expression();
				$expr->parse($expr_str);
				$yvalues = $expr->eval_double_array(array(),'x',$xvalues);
			}
			$m = sizeof($xvalues);
			if ($m == 0)
			{
				exit('{status:"OK?",data:[]}');
			}
			if ($m != sizeof($yvalues))
			{
				exit('{status:"error",value:"X-Y mismatch"}');
			}
			for($i = 0; $i < $m; $i += 1)
			{
				array_push($data,array($xvalues[$i],$yvalues[$i]));
			}
			if ($intv > 0)
			{
				$fun .= ',';
			}
			$fun .= '{data:'.json_encode($data).'}';
		} // for intv
		$fun .= ']';
		echo '{status:"OK",value:'.$fun.'}';
	}
	catch(Exception $e)
	{
		exit('{status:"error",value:"Exception "}');
	}
}
else
{
  echo '{status:"error",value:"URL error: required expression, xrange, n"}';
}

?>
