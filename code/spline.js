/**
 * Define comparisson function for sorting hspline points
 */
function point_cmp(p1,p2)
{
	return p1.x - p2.x;
}

/**
 * A cubic spline object definition.
 */
function spline()
{
	this.points = [];
	this.coeffs = [];

	/**
	 * Calculate the cubic polynomial coefficients.
	 */
	this.fit = function ()
	{
		var n = this.points.length - 1;
		this.coeffs  = [];
		for(var i = 0; i < n; ++i)
		{
			var p = this.points[i];
			var c = [p.y,p.d,0,0];
			var p1 = this.points[i+1];
			var dx = p1.x - p.x;
			var dx2 = dx * dx;
			var dx3 = dx2 * dx;
			c[2] = (3 * (p1.y - p.y) - (p1.d + 2 * p.d) * dx) / dx2;
			c[3] = (dx * (p1.d + p.d) - 2 * (p1.y - p.y)) / dx3;
			this.coeffs.push( c );
		}
		// add the last cubic
		var p = this.points[n];
		this.coeffs.push( [p.y,p.d,0,0] );
	}
	
	/**
	 * Add a new point to the spline. points are sorted by x.
	 * @param x0 :: x value
	 * @param y0 :: y value at x
	 * @param d0 :: derivative at x
	 */
	this.add_point = function (x0,y0,d0)
	{
		this.points.push( {x:x0, y:y0, d:d0 } );
		this.points.sort(point_cmp);
		return this.indexOf(x0);
	}
	
	this.indexOf = function(x)
	{
		var i = this.points.length - 1;
		for(var j = 0; j < this.points.length; ++j)
		{
			if (x < this.points[j].x)
			{
				if (j > 0)
				{
					i = j - 1;
				}
				else
				{
					i = 0;
				}
				break;
			}
		}
		return i;
	}
	
	this.del = function(i)
	{
		if (i>=0 && i < this.points.length)
		{
			this.points.splice(i,1);
		}
	}
	
	this.move_point = function(i,x,y)
	{
		if (i>=0 && i < this.points.length)
		{
			var p = this.points[i];
			p.x = x;
			p.y = y;
			this.points.sort(point_cmp);
		}
	}
	
	this.change_deriv = function(i,d)
	{
		if (i>=0 && i < this.points.length)
		{
			var p = this.points[i];
			p.d = d;
		}
	}
	
	/**
	 * Evaluate the spline at point x
	 */
	this.eval = function(x)
	{
		var i = this.points.length - 1;
		for(var j = 0; j < this.points.length; ++j)
		{
			if (x < this.points[j].x)
			{
				if (j > 0)
				{
					i = j - 1;
				}
				else
				{
					i = 0;
				}
				break;
			}
		}
		var dx = x - this.points[i].x;
		var c = this.coeffs[i];
		return c[0] + dx * (c[1] + dx * (c[2] + dx * c[3]));
	}

	/**
	 * First derivative at point x
	 */
	this.deriv = function(x)
	{
		var i = this.indexOf(x);
		if (i >= 0 && i < this.points.length)
		{
			var dx = x - this.points[i].x;
			var c = this.coeffs[i];
			return c[1] + dx * (2*c[2] + dx * 3*c[3]);
		}
		else
		{
			return 0.0;
		}
	}

	/**
	 * Evaluate the spline at all points in array x = [x1,x2,...].
	 * Return array in the form: [ [x1,y1], [x2,y2], ... ]
	 * sutable for using in Flotr
	 */
	this.eval_array = function(x)
	{
		var i = this.points.length - 1;
		for(var j = 0; j < this.points.length; ++j)
		{
			if (x[j] < this.points[j].x)
			{
				if (j > 0)
				{
					i = j - 1;
				}
				else
				{
					i = 0;
				}
				break;
			}
		}
		var nx = x.length;
		var y = [];
		for(var j = 0; j < nx; ++j)
		{
			if (i < this.points.length - 1 && x[j] > this.points[i + 1].x )
			{
				++i;
			}
			var dx = x[j] - this.points[i].x;
			var c = this.coeffs[i];
			y.push( [x[j], c[0] + dx * (c[1] + dx * (c[2] + dx * c[3]))] );
		}
		return y;
	}
	
	/**
	 * Returns the size of the spline which is the number of points in it.
	 */
	this.size = function()
	{
		return this.points.length;
	}

	/**
	 * Clear the spline.
	 */
	this.clear = function()
	{
		this.points = [];
		this.coeffs = [];
	}

}

function sketch_spline()
{
	this.sp = new spline();
	this.ptype = [];
	
	/**
	 * Adds a point to the spline and adjusts derivatives of the slope points
	 * @param itype :: can be 0 for stationary point or 1 for a slope point
	 */
	this.add_point = function(x,y,itype)
	{
		var n = this.sp.size();
		var d = 0;
		if (n == 0)
		{
			d = 0;
		}
		else if (n == 1)
		{
			if (itype == 1)
			{
				var p0 = this.sp.points[0];
				d = (y - p0.y) / (x - p0.x);
			}
			if (this.ptype[0].type == 1)
			{
				p0.d = d;
			}
		}
		else if (itype == 1)
		{
			d = this.sp.deriv(x);
		}
		var ind = this.sp.add_point(x,y,d);
		this.ptype.push( {x:x, type:itype} );
		this.ptype.sort(point_cmp);
		return ind;
	}

	this.fit = function(){this.sp.fit();}
	this.size = function(){return this.sp.size();}
	this.clear = function(){this.sp.clear();}
	this.del = function(i){this.sp.del(i);}
	this.move_point = function(i,x,y){this.sp.move_point(i,x,y);}
	this.change_deriv = function(i,d){this.sp.change_deriv(i,d);}
	this.eval = function(x){return this.sp.eval(x);}
	this.indexOf = function(x){return this.sp.indexOf(x);}
	this.eval_array = function(x){return this.sp.eval_array(x);}
	this.get_points = function()
	{
		var d_slope = [];
		var d_stat = [];
		var n = this.sp.size();
		for(var i = 0; i < n; ++i)
		{
			var p = this.sp.points[i];
			if (this.ptype[i].type == 0)
			{
				d_stat.push( [p.x, p.y] );
			}
			else
			{
				d_slope.push( [p.x, p.y] );
			}
		}
		//alert(d_stat.length+' '+d_slope.length);
		return [ {data:d_slope, points:{show:true}, color:'green', mouse:{track:true}}, 
		         {data:d_stat, points:{show:true}, color:'blue', mouse:{track:true}} ];
		
	}
}

function test_spline()
{
	document.writeln('Hello!<br>');

	var hs = new spline();
	hs.add_point(2,3,2);
	hs.add_point(1,2,3);
	hs.add_point(0,2,3);
	hs.fit();
	for(var i = 0; i < hs.points.length; ++i)
	{
		document.write(hs.points[i].x + ' ' + hs.points[i].y + ' ' + hs.points[i].d + '<br>');
	}
}
