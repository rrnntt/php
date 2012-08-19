<?php
require('matrix.php');
/**
 * Define comparisson function for sorting hspline points
 */
function point_cmp($p1,$p2)
{
	if ($p2->x == $p1->x) return 0;
	return  ($p1->x < $p2->x) ? -1 : 1;
}

class Point
{
	function Point($x,$y,$d)
	{
		$this->x = $x;
		$this->y = $y;
		$this->d = $d;
	}
}

/**
 * A cubic spline object definition.
 */
class Spline
{
	function Spline()
	{
		$this->points = array();
		$this->coeffs = array();
	}

	/**
	 * Calculate the cubic polynomial coefficients.
	 */
	function fit()
	{
		$n = sizeof($this->points) - 1;
		$this->coeffs  = array();
		for($i = 0; $i < $n; ++$i)
		{
			$p = $this->points[$i];
			$c = array($p->y,$p->d,0,0);
			$p1 = $this->points[$i+1];
			$dx = $p1->x - $p->x;
			$dx2 = $dx * $dx;
			$dx3 = $dx2 * $dx;
			$c[2] = (3 * ($p1->y - $p->y) - ($p1->d + 2 * $p->d) * $dx) / $dx2;
			$c[3] = ($dx * ($p1->d + $p->d) - 2 * ($p1->y - $p->y)) / $dx3;
			array_push( $this->coeffs, $c );
		}
		// add the last cubic
		$p = $this->points[$n];
		array_push($this->coeffs, array($p->y,$p->d,0,0) );
	}
	
	/**
	 * Add a new point to the spline. points are sorted by x.
	 * @param x0 :: x value
	 * @param y0 :: y value at x
	 * @param d0 :: derivative at x
	 */
	function add_point($x0,$y0,$d0)
	{
		array_push( $this->points, new Point($x0, $y0, $d0) );
		usort($this->points, 'point_cmp');
		return $this->indexOf($x0);
	}
	
	/**
	 * parray is array of the form [ [x1,y1,d1], [x2,y2,d2], ... ]
	 */
	function add_points($parray)
	{
		foreach($parray as $p)
		{
			array_push( $this->points, new Point($p[0], $p[1], $p[2]) );
		}
		usort($this->points, 'point_cmp');
	}
	
	/**
	 * Get index of the cubic for point $x
	 */
	function indexOf($x)
	{
		$n = sizeof($this->points);
		$i = $n - 1;
		for($j = 0; $j < $n; $j += 1)
		{
			if ($x < $this->points[$j]->x)
			{
				if ($j > 0)
				{
					$i = $j - 1;
				}
				else
				{
					$i = 0;
				}
				break;
			}
		}
		return $i;
	}
	
	function del($i)
	{
		if ($i >= 0 && $i < sizeof($this->points))
		{
			$n = sizeof($this->points);
			$res = array();
			for($j = 0; $j < $n; ++$j)
			{
				if ($i != $j)
				{
					array_push($res, $this->points[$j]);
				}
			}
			$this->points = $res;
		}
	}

	/**
	 * Modify x and y of i-th point. Doesn't refit.
	 */
	function move_point($i,$x,$y)
	{
		if ($i >= 0 && $i < sizeof($this->points))
		{
			$p = $this->points[$i];
			$p->x = $x;
			$p->y = $y;
			usort($this->points, 'point_cmp');
		}
	}
	
	/**
	 * Modify d of i-th point. Doesn't refit.
	 */
	function change_deriv($i,$d)
	{
		if ($i >= 0 && $i < sizeof($this->points))
		{
			$this->points[$i]->d = $d;
		}
	}
	
	/**
	 * Evaluate the spline at point x
	 */
	function calc($x)
	{
		$n = sizeof($this->points);
		$i = $n - 1;
		for($j = 0; $j < $n; ++$j)
		{
			if ($x < $this->points[$j]->x)
			{
				if ($j > 0)
				{
					$i = $j - 1;
				}
				else
				{
					$i = 0;
				}
				break;
			}
		}
		$dx = $x - $this->points[$i]->x;
		$c = $this->coeffs[$i];
		return $c[0] + $dx * ($c[1] + $dx * ($c[2] + $dx * $c[3]));
	}

	/**
	 * Evaluate the value if the i-th part of spline at point x
	 */
	function calci($i,$x)
	{
		$n = sizeof($this->points);
		if ($i >= $n || $i < 0)
		{
			throw new Exception('Spline part '.$i.' does not exist');
		}
		$dx = $x - $this->points[$i]->x;
		$c = $this->coeffs[$i];
		return $c[0] + $dx * ($c[1] + $dx * ($c[2] + $dx * $c[3]));
	}

	/**
	 * First derivative at point x
	 */
	function deriv($x)
	{
		$i = $this->indexOf($x);
		if ($i >= 0 && $i < sizeof($this->points))
		{
			$dx = $x - $this->points[$i]->x;
			$c = $this->coeffs[$i];
			return $c[1] + $dx * (2*$c[2] + $dx * 3*$c[3]);
		}
		else
		{
			return 0.0;
		}
	}

	/**
	 * First derivative of the i-th part of spline at point x
	 */
	function derivi($i,$x)
	{
		$n = sizeof($this->points);
		if ($i >= $n || $i < 0)
		{
			throw new Exception('Spline part '.$i.' does not exist');
		}
		$dx = $x - $this->points[$i]->x;
		$c = $this->coeffs[$i];
		return $c[1] + $dx * (2*$c[2] + $dx * 3*$c[3]);
	}

	/**
	 * Second derivative at point x
	 */
	function deriv2($x)
	{
		$i = $this->indexOf($x);
		if ($i >= 0 && $i < sizeof($this->points))
		{
			$dx = $x - $this->points[$i]->x;
			$c = $this->coeffs[$i];
			return 2*$c[2] + $dx * 6*$c[3];
		}
		else
		{
			return 0.0;
		}
	}

	/**
	 * Second derivative of the i-th part of spline at point x
	 */
	function deriv2i($i,$x)
	{
		if ($i >= $n || $i < 0)
		{
			throw new Exception('Spline part '.$i.' does not exist');
		}
		$dx = $x - $this->points[$i]->x;
		$c = $this->coeffs[$i];
		return 2*$c[2] + $dx * 6*$c[3];
	}

	/**
	 * Evaluate the spline at all points in array x = [x1,x2,...].
	 * Return array in the form: [ [x1,y1], [x2,y2], ... ]
	 * sutable for using in Flotr
	 */
	function calc_array($x)
	{
		$n = sizeof($this->points);
		$i = $n - 1;
		for($j = 0; $j < $n; ++$j)
		{
			if ($x[$j] < $this->points[$j]->x)
			{
				if ($j > 0)
				{
					$i = $j - 1;
				}
				else
				{
					$i = 0;
				}
				break;
			}
		}
		$nx = sizeof($x);
		$y = array();
		for($j = 0; $j < $nx; ++$j)
		{
			if ($i < $n - 1 && $x[$j] > $this->points[$i + 1]->x )
			{
				++$i;
			}
			$dx = $x[$j] - $this->points[$i]->x;
			$c = $this->coeffs[$i];
			array_push($y, $c[0] + $dx * ($c[1] + $dx * ($c[2] + $dx * $c[3])) );
		}
		return $y;
	}
	
	/**
	 * Returns the size of the spline which is the number of points in it.
	 */
	function size()
	{
		return sizeof($this->points);
	}

	/**
	 * Clear the spline.
	 */
	function clear()
	{
		$this->points = array();
		$this->coeffs = array();
	}
	
	/**
	 * Fit first derivatives to minimise second derivatives.
	 */
	function fit_derivs_badly()
	{
		$n = $this->size() - 1;
		// there are 3*$n parameters to fit: polynomial coeffs of orders from 1 to 3 for each spline piece
		$M = new Matrix(3*$n,3*$n);
		$b = array_fill(0,3*$n,0);
		for($i = 0; $i < $n; ++$i)
		{
			$dx = $this->points[$i+1]->x - $this->points[$i]->x;
			$dx2 = $dx * $dx;
			$dx3 = $dx2* $dx;
			$j = 3 * $i;

			$b[$j] = $this->points[$i+1]->y - $this->points[$i]->y;
			$M->add($j,$j,$dx); // c_i^1
			$M->add($j,$j+1,$dx2); // c_i^2
			$M->add($j,$j+2,$dx3); // c_i^3
			
			$M->add($j+1,$j,1); // c_i^1
			$M->add($j+1,$j+1,2*$dx); // c_i^2
			$M->add($j+1,$j+2,3*$dx2); // c_i^3
			if ($i != $n - 1)
			$M->add($j+1,$j+3,-1); // c_{i+1}^1
			
			$M->add($j+2,$j+1,1); // c_i^2
			$M->add($j+2,$j+2,3*$dx); // c_i^3
			if ($i != $n - 1)
			$M->add($j+2,$j+4,-1); // c_{i+1}^2
		}
		//$M->log();
		//$M0 = $M->copy();
		//echo 'b='.json_encode($b).'<br>';
		$x = $M->solve($b);
		//echo 'b='.json_encode($M0->mulVect($x)).'<br>';
		for($i = 0; $i < $n; ++$i)
		{
			$j = 3 * $i;
			$p = $this->points[$i];
			array_push($this->coeffs, array($p->y,$x[$j],$x[$j+1],$x[$j+2]) );
		}
		$p = $this->points[$n];
		array_push($this->coeffs, array($p->y,0,0,0) );
	}

	/**
	 * Fit first derivatives to minimise second derivatives.
	 */
	function fit_derivs()
	{
		$n = $this->size();
		for($i = 1; $i < $n - 1; ++$i)
		{
			$d = 0;
			$d += ($this->points[$i]->y - $this->points[$i-1]->y) / ($this->points[$i]->x - $this->points[$i-1]->x);
			$d += ($this->points[$i+1]->y - $this->points[$i]->y) / ($this->points[$i+1]->x - $this->points[$i]->x);
			$this->points[$i]->d = 0.5 * $d;
		}
		if ($n > 2)
		{
			$dx = $this->points[1]->x - $this->points[0]->x;
			$c2 = ($this->points[0]->y - $this->points[1]->y + $this->points[1]->d * $dx) / ($dx * $dx);
			$this->points[0]->d = -2 * $c2 * $dx + $this->points[1]->d;
			
			$dx = $this->points[$n-1]->x - $this->points[$n-2]->x;
			$c2 = ($this->points[$n-1]->y - $this->points[$n-2]->y - $this->points[$n-2]->d * $dx) / ($dx * $dx);
			$this->points[$n-1]->d = 2 * $c2 * $dx + $this->points[$n-2]->d;
		}
		$this->fit();
	}
}

?>
