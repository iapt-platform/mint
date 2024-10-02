'use strict';
var ns = require('ns');
var MoonRealOrbit = require('./MoonRealOrbit');

module.exports = [
	{
		name: 'sun',
		title : 'The Sun',
		mass : 1.9891e30,
		radius : 6.96342e5,
		k : 0.01720209895 //gravitational constant (μ)
	},
	{
	 	name: 'mercury',
		title : 'Mercury',
		mass : 3.3022e23,
		radius:2439,
		orbit : { 
			base : {a : 0.38709927 * ns.AU ,  e : 0.20563593, i: 7.00497902, l : 252.25032350, lp : 77.45779628, o : 48.33076593},
			cy : {a : 0.00000037 * ns.AU ,  e : 0.00001906, i: -0.00594749, l : 149472.67411175, lp : 0.16047689, o : -0.12534081}
		}
	},
	{
		name: 'venus',
		title : 'Venus',
		mass : 4.868e24,
		radius : 6051,
		orbit : {
			base : {a : 0.72333566 * ns.AU ,  e : 0.00677672, i: 3.39467605, l : 181.97909950, lp : 131.60246718, o : 76.67984255},
			cy : {a : 0.00000390 * ns.AU ,  e : -0.00004107, i: -0.00078890, l : 58517.81538729, lp : 0.00268329, o : -0.27769418}
		}
	},
	{
		name:'earth',
		title : 'The Earth',
		mass : 5.9736e24,
		radius : 3443.9307 * ns.NM_TO_KM,
		sideralDay : ns.SIDERAL_DAY,
		tilt : 23+(26/60)+(21/3600) ,
		orbit : {
			base : {a : 1.00000261 * ns.AU, e : 0.01671123, i : -0.00001531, l : 100.46457166, lp : 102.93768193, o : 0.0},
			cy : {a : 0.00000562 * ns.AU, e : -0.00004392, i : -0.01294668, l : 35999.37244981, lp : 0.32327364, o : 0.0}
		}
	},
	{
		name:'mars',
		title : 'Mars',
		mass : 6.4185e23,
		radius : 3376,
		sideralDay : 1.025957 * ns.DAY,
		orbit : {
			base : {a : 1.52371034 * ns.AU ,  e : 0.09339410, i: 1.84969142, l : -4.55343205, lp : -23.94362959, o : 49.55953891},
			cy : {a : 0.00001847 * ns.AU ,  e : 0.00007882, i: -0.00813131, l : 19140.30268499, lp : 0.44441088, o : -0.29257343}
		}
	},
	{
	 	name:'jupiter',
		title : 'Jupiter',
		mass : 1.8986e27,
		radius : 71492,
		orbit : {
			base : {a : 5.20288700 * ns.AU ,  e : 0.04838624, i: 1.30439695, l : 34.39644051, lp : 14.72847983, o : 100.47390909},
			cy : {a : -0.00011607 * ns.AU ,  e : -0.00013253, i: -0.00183714, l : 3034.74612775, lp : 0.21252668, o : 0.20469106}
		}
	},
	{
		name:'saturn',
		title : 'Saturn',
		mass : 5.6846e26,
		radius : 58232,
		tilt : 26.7,
		orbit : {
			base : {a : 9.53667594 * ns.AU ,  e : 0.05386179, i: 2.48599187, l : 49.95424423, lp : 92.59887831, o : 113.66242448},
			cy : {a : -0.00125060 * ns.AU ,  e : -0.00050991, i: 0.00193609, l : 1222.49362201, lp : -0.41897216, o : -0.28867794}
		}
	},
	{
		name: 'uranus',
		title : 'Uranus',
		mass : 8.6810e25,
		radius : 25559,
		orbit : {
			base : {a : 19.18916464 * ns.AU ,  e : 0.04725744, i: 0.77263783, l : 313.23810451, lp : 170.95427630, o : 74.01692503},
			cy : {a : -0.00196176 * ns.AU ,  e : -0.00004397, i: -0.00242939, l : 428.48202785, lp : 0.40805281, o : 0.04240589}
		}
	},
	{
		name:'neptune',
		title : 'Neptune',
		mass : 1.0243e26,
		radius : 24764,
		orbit : {
			base : {a : 30.06992276  * ns.AU,  e : 0.00859048, i: 1.77004347, l : -55.12002969, lp : 44.96476227, o : 131.78422574},
			cy : {a : 0.00026291  * ns.AU,  e : 0.00005105, i: 0.00035372, l : 218.45945325, lp : -0.32241464, o : -0.00508664}
		}
	},
	{
		name: 'pluto',
		title : 'Pluto',
		mass : 1.305e22+1.52e21,
		radius : 1153,
		orbit : {
			base : {a : 39.48211675 * ns.AU ,  e : 0.24882730, i: 17.14001206, l : 238.92903833, lp : 224.06891629, o : 110.30393684},
			cy : {a : -0.00031596 * ns.AU ,  e : 0.00005170, i: 0.00004818, l : 145.20780515, lp : -0.04062942, o : -0.01183482}
		}
	},
	{
		name: 'halley',
		title : 'Halley\'s Comet',
		mass : 2.2e14,
		radius : 50,
		orbit : {
			base : {a : 17.83414429 * ns.AU ,  e : 0.967142908, i: 162.262691, M : 360 * (438393600 / (75.1 * ns.YEAR * ns.DAY)), w : 111.332485, o : 58.420081},
			day : {a : 0 ,  e : 0, i: 0, M : (360 / (75.1 * 365.25) ), w : 0, o : 0}
		}
	},
	{
		name: 'moon',
		title : 'The Moon',
		mass : 7.3477e22,
		radius : 1738.1,
		sideralDay : (27.3215782 * ns.DAY) ,
		tilt : 1.5424,
		fov : 1,
		relativeTo : 'earth',
		orbitCalculator : MoonRealOrbit,
		orbit: {
			base : {
				a : 384400,
				e : 0.0554,
				w : 318.15,
				M : 135.27,
				i : 5.16,
				o : 125.08
			},
			day : {
				a : 0,
				e : 0,
				i : 0,
				M : 13.176358,//360 / 27.321582,
				w : (360 / 5.997) / 365.25,
				o : (360 / 18.600) / 365.25
			}	
		}
	}
];
