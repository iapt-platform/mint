
SunCalc
=======

[![Build Status](https://travis-ci.org/mourner/suncalc.svg?branch=master)](https://travis-ci.org/mourner/suncalc)

SunCalc is a tiny BSD-licensed JavaScript library for calculating sun position,
sunlight phases (times for sunrise, sunset, dusk, etc.),
moon position and lunar phase for the given location and time,
created by [Vladimir Agafonkin](http://agafonkin.com/en) ([@mourner](https://github.com/mourner))
as a part of the [SunCalc.net project](http://suncalc.net).

Most calculations are based on the formulas given in the excellent Astronomy Answers articles
about [position of the sun](http://aa.quae.nl/en/reken/zonpositie.html)
and [the planets](http://aa.quae.nl/en/reken/hemelpositie.html).
You can read about different twilight phases calculated by SunCalc
in the [Twilight article on Wikipedia](http://en.wikipedia.org/wiki/Twilight).


## Usage example

```javascript
// get today's sunlight times for London
// 获取当天伦敦的太阳时间参数
var times = SunCalc.getTimes(new Date(), 51.5, -0.1);// 经度，纬度 

// format sunrise time from the Date object
// 从Date对象格式化日出时间 hh:mm
var sunriseStr = times.sunrise.getHours() + ':' + times.sunrise.getMinutes();

// get position of the sun (azimuth and altitude) at today's sunrise
// 获取今天日出时太阳的位置（当地经度，当地纬度）
var sunrisePos = SunCalc.getPosition(times.sunrise, 51.5, -0.1);

// get sunrise azimuth in degrees
// 得到太阳方位角
var sunriseAzimuth = sunrisePos.azimuth * 180 / Math.PI;
```

SunCalc is also available as an NPM package:
SunCalc也可以作为NPM软件包提供：

```bash
$ npm install suncalc
```

```js
var SunCalc = require('suncalc');
```


## Reference调用

### Sunlight times 太阳时间

```javascript
SunCalc.getTimes(/*Date*/ date, /*Number*/ latitude, /*Number*/ longitude, /*Number (default=0)*/ height)
SunCalc.getTimes(/*日期*/ date, /*纬度*/ latitude, /*经度*/ longitude, /*海拔高度（千米） (default=0)*/ height)
```

Returns an object with the following properties (each is a `Date` object):

返回一个对象具有一下属性（每一个都是一个`Date`对象）

| Property属性    | Description描述                                                                                            |
| --------------- | ---------------------------------------------------------------------------------------------------------- |
| `sunrise`       | sunrise 日出(top edge of the sun appears on the horizon  日轮上缘出现在地平线)                             |
| `sunriseEnd`    | sunrise ends 日出结束(bottom edge of the sun touches the horizon    日轮下缘触及地平线)                    |
| `goldenHourEnd` | 朝霞结束morning golden hour (soft light, best time for photography) ends                                   |
| `solarNoon`     | 正午solar noon (sun is in the highest position 太阳位于高度角达到最高)                                     |
| `goldenHour`    | evening golden hour starts  晚霞开始                                                                       |
| `sunsetStart`   | sunset starts 日落开始 (bottom edge of the sun touches the horizon 日轮下缘触及地平线)                     |
| `sunset`        | sunset 日落(sun disappears below the horizon, evening civil twilight starts日轮上缘消失在地平线，夜幕降临) |
| `dusk`          | dusk 暮色 (evening nautical twilight starts 航海夜幕开始)                                                  |
| `nauticalDusk`  | nautical dusk 航海暮色 (evening astronomical twilight starts 天文夜幕开始)                                 |
| `night`         | night starts 入夜 (dark enough for astronomical observations夜色足够黑暗可以进行天文学观察)                |
| `nadir`         | nadir天底 (darkest moment of the night, sun is in the lowest position 夜晚的至暗之时，太阳位于最低点)      |
| `nightEnd`      | night ends夜尽 (morning astronomical twilight starts 天文曙光开始)                                         |
| `nauticalDawn`  | nautical dawn 航海曙光 (morning nautical twilight starts 航海曙光开始)                                     |
| `dawn`          | dawn 破晓(morning nautical twilight ends, morning civil twilight starts航海曙光结束，民用曙光开始)         |

```javascript
SunCalc.addTime(/*Number*/ angleInDegrees, /*String*/ morningName, /*String*/ eveningName)
```

Adds a custom time when the sun reaches the given angle to results returned by `SunCalc.getTimes`.

当太阳到达给定角度时，通过`SunCalc.getTimes`将自定义时间添加到返回的结果中。

`SunCalc.times` property contains all currently defined times.
`SunCalc.times`属性包含了所有当前已定义的时间

### Sun position太阳位置

```javascript
SunCalc.getPosition(/*Date*/ timeAndDate, /*Number*/ latitude, /*Number*/ longitude)
```

Returns an object with the following properties:

 * `altitude`: sun altitude above the horizon in radians,太阳高度（以弧度为单位）
 e.g. `0` at the horizon and `PI/2` at the zenith (straight over your head)
 `0`为地平线 `PI/2`为天顶
 * `azimuth`: sun azimuth in radians (direction along the horizon, measured from south to west),太阳弧度（以弧度为单位）（地平线的投影方向，从南到西测量），
 e.g. `0` is south and `Math.PI * 3/4` is northwest
- `0`为正南
- `Math.PI * 1/4`为西南
- `Math.PI * 1/2`为正西
- `Math.PI * 3/4` 西北
- `Math.PI` 为正北


### Moon position

```javascript
SunCalc.getMoonPosition(/*Date*/ timeAndDate, /*Number*/ latitude, /*Number*/ longitude)
```

Returns an object with the following properties:

 * `altitude`: moon altitude above the horizon in radians  月亮高度（弧度）
 * `azimuth`: moon azimuth in radians 月亮水平偏移（弧度）
 * `distance`: distance to moon in kilometers 地月距离（千米）
 * `parallacticAngle`: parallactic angle of the moon in radians 月亮的视差角（弧度）


### Moon illumination月轮亮度

```javascript
SunCalc.getMoonIllumination(/*Date*/ timeAndDate)
```

Returns an object with the following properties:
返回一个具有以下属性的对象
 * `fraction`: illuminated fraction of the moon; varies from `0.0` (new moon) to `1.0` (full moon)
 * 月亮照亮的部分，从`0.0`到`1.0`变化
 * `phase`: moon phase; varies from `0.0` to `1.0`, described below
 * 月相；从`0.0`到`1.0`变化，描述如下
 * `angle`: midpoint angle in radians of the illuminated limb of the moon reckoned eastward from the north point of the disk;
 the moon is waxing if the angle is negative, and waning if positive
* 通过月亮照亮的部分从圆盘的正北向东偏移的角度（以弧度计）的中点；如果角度为负，则月亮在变亮；如果角度为正，则月亮在变暗

Moon phase value should be interpreted like this:
月相值的解释如下：

| Phase相 | Name                     |
| ------: | ------------------------ |
|       0 | New Moon 新月            |
|         | Waxing Crescent 凹月渐亮 |
|    0.25 | First Quarter 上弦月     |
|         | Waxing Gibbous 凸月渐满  |
|     0.5 | Full Moon 满月           |
|         | Waning Gibbous 满月渐小  |
|    0.75 | Last Quarter 下弦月      |
|         | Waning Crescent 凹月渐暗 |

By subtracting the `parallacticAngle` from the `angle` one can get the zenith angle of the moons bright limb (anticlockwise).
The zenith angle can be used do draw the moon shape from the observers perspective (e.g. moon lying on its back).

### Moon rise and set times

```js
SunCalc.getMoonTimes(/*Date*/ date, /*Number*/ latitude, /*Number*/ longitude[, inUTC])
```

Returns an object with the following properties:

 * `rise`: moonrise time as `Date`
 * `set`: moonset time as `Date`
 * `alwaysUp`: `true` if the moon never rises/sets and is always _above_ the horizon during the day
 * `alwaysDown`: `true` if the moon is always _below_ the horizon

By default, it will search for moon rise and set during local user's day (frou 0 to 24 hours).
If `inUTC` is set to true, it will instead search the specified date from 0 to 24 UTC hours.

## Changelog

#### 1.8.0 &mdash; Dec 22, 2016

- Improved precision of moonrise/moonset calculations.
- Added `parallacticAngle` calculation to `getMoonPosition`.
- Default to today's date in `getMoonIllumination`.
- Fixed incompatibility when using Browserify/Webpack together with a global AMD loader.

#### 1.7.0 &mdash; Nov 11, 2015

- Added `inUTC` argument to `getMoonTimes`.

#### 1.6.0 &mdash; Oct 27, 2014

- Added `SunCalc.getMoonTimes` for calculating moon rise and set times.

#### 1.5.1 &mdash; May 16, 2014

- Exposed `SunCalc.times` property with defined daylight times.
- Slightly improved `SunCalc.getTimes` performance.

#### 1.4.0 &mdash; Apr 10, 2014

- Added `phase` to `SunCalc.getMoonIllumination` results (moon phase).
- Switched from mocha to tape for tests.

#### 1.3.0 &mdash; Feb 21, 2014

- Added `SunCalc.getMoonIllumination` (in place of `getMoonFraction`) that returns an object with `fraction` and `angle`
(angle of illuminated limb of the moon).

#### 1.2.0 &mdash; Mar 07, 2013

- Added `SunCalc.getMoonFraction` function that returns illuminated fraction of the moon.

#### 1.1.0 &mdash; Mar 06, 2013

- Added `SunCalc.getMoonPosition` function.
- Added nadir (darkest time of the day, middle of the night).
- Added tests.

#### 1.0.0 &mdash; Dec 07, 2011

- Published to NPM.
- Added `SunCalc.addTime` function.

#### 0.0.0 &mdash; Aug 25, 2011

- First commit.
