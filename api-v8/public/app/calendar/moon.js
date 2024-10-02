/*   
  Moon-phase calculation
  Roger W. Sinnott, Sky & Telescope, June 16, 2006.
*/

/*
 Declare constant variables
 */
var jd = 0;

/*
 Set date and time to current date and time
 */
function setup() {
    var nowdate = new Date();
    var day = nowdate.getDate();
    var month = nowdate.getMonth();
    var year = nowdate.getFullYear();
    document.moonfaseBox.yearf.value = year;
    document.moonfaseBox.monthf.selectedIndex = month;
    document.moonfaseBox.dayf.selectedIndex = day - 1;
}


/*
 todo: Do not know what function does
 */
function proper_ang(big) {
    with (Math) {
        var tmp = 0;
        if (big > 0) {
            tmp = big / 360.0;
            tmp = (tmp - floor(tmp)) * 360.0;
        }
        else {
            tmp = ceil(abs(big / 360.0));
            tmp = big + tmp * 360.0;
        }
    }
    return tmp;
}


/*
 Calculate dates in February
 */
function daysInFebruary(year) {
    if (year > 1582) {
        return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
    }
    else {
        return ((year % 4 == 0) ? 29 : 28 );
    }
}


/*
 Calculate days in every month
 */
function DaysArray(n) {
    for (var i = 1; i <= n; i++) {
        this[i] = 31;
        if (i == 4 || i == 6 || i == 9 || i == 11) {
            this[i] = 30
        }
        if (i == 2) {
            this[i] = 29
        }
    }
    return this;
}


/*
 Validate date and display errors
 */
function isDate() {
    var daysInMonth = new DaysArray(12);
    var monthIdx = document.moonfaseBox.monthf.selectedIndex;
    var month = eval(document.moonfaseBox.monthf.options[monthIdx].value);
    var dayIdx = document.moonfaseBox.dayf.selectedIndex;
    var day = eval(document.moonfaseBox.dayf.options[dayIdx].value);
    var yearff = document.moonfaseBox.yearf.value;

    if (yearff == "") {
        alert("Please enter the year.");
        document.moonfaseBox.yearf.focus();
        return false;
    }

    if (isNaN(yearff)) {
        alert("Please reenter the year.  Only numbers are allowed.");
        document.moonfaseBox.yearf.focus();
        return false;
    }

    var year = eval(yearff);

    if (year == 0) {
        alert("Sorry!  There was no year 0 in common reckoning.  Please enter 1 and click 'BC' if that is what you meant.");
        document.moonfaseBox.yearf.focus();
        return false;
    }

    if (year < 0) {
        alert("Sorry!  Don't use a minus sign with the year.  Instead, click 'BC' and increase the absolute value of the year by one.");
        document.moonfaseBox.yearf.focus();
        return false;
    }

    var eraObj = document.getElementsByName("adbc");
    for (var i = 0; i < eraObj.length; i++) {
        if (eraObj[i].checked) {
            era = eraObj[i].value;
        }
    }
    if (era == 0) year = 1 - year;

// Note:  From here on, in this function only, 'year' is the astronomical year with sign!!
    if (month == 2 && day == 29 && day > daysInFebruary(year)) {
        alert("Sorry!  Not a leap year, so there is no February 29th.");
        return false;
    }

    if (day > daysInMonth[month]) {
        alert("Oops!  Please enter a valid number for the day.");
        return false;
    }

    if (year == 1582 && month == 10 && day > 4 && day < 15) {
        alert("Invalid date.  In the Gregorian calendar reform, October 4, 1582, was immediately followed by October 15, 1582.");
        return false;
    }

    if (year < -3999) {
        alert("The year you've entered is too early.  Please enter a year after 4001 BC.");
        return false;
    }

    if (year > 8000) {
        alert("The year you've entered is too far in the future.  Please enter a year prior to AD 8001.");
        return false;
    }

    if ((year < 100) && (year > 0)) {
        alert("Caution!  You've entered a year prior to AD 100.  If you intended a modern date, be sure 'AD' is clicked and enter all four digits of the year.  If you really did mean an early historical date in the Julian calendar, the Moon's phase will be correctly shown.")
    }

    return true;
}


/*
 Validate form
 */
function ValidateForm() {
    if (isDate() == false) {
        return false;
    }
    jdn();
    moonElong();
    return true;
}


/*
 todo: Do not know what function does
 */
function jdn() {
    var now_date = new Date();
    var zone = now_date.getTimezoneOffset() / 1440;

    var monthIdx = document.moonfaseBox.monthf.selectedIndex;
    var mm = eval(document.moonfaseBox.monthf.options[monthIdx].value);

    var dayIdx = document.moonfaseBox.dayf.selectedIndex;
    var dd = eval(document.moonfaseBox.dayf.options[dayIdx].value);

    var yy = eval(document.moonfaseBox.yearf.value);
    with (Math) {
        var yyy = yy;
        var eraObj = document.getElementsByName("adbc");
        for (var i = 0; i < eraObj.length; i++) {
            if (eraObj[i].checked) {
                var era = eraObj[i].value;
            }
        }
        if (era == 0) yyy = 1 - yy;
        var mmm = mm;
        if (mm < 3) {
            yyy = yyy - 1;
            mmm = mm + 12;
        }
        var day = dd + zone + 0.5;
        var a = floor(yyy / 100);
        var b = 2 - a + floor(a / 4);
        jd = floor(365.25 * yyy) + floor(30.6001 * (mmm + 1)) + day + 1720994.5;
        if (jd > 2299160.4999999) jd = jd + b;
    }
    // document.moonfaseBox.jdnum.value = jd;
}


/*
 todo: Do not know what function does
 */
function moonElong() {
    with (Math) {
        var dr = PI / 180;
        var rd = 1 / dr;
        var meeDT = pow((jd - 2382148), 2) / (41048480 * 86400);
        var meeT = (jd + meeDT - 2451545.0) / 36525;
        var meeT2 = pow(meeT, 2);
        var meeT3 = pow(meeT, 3);
        var meeD = 297.85 + (445267.1115 * meeT) - (0.0016300 * meeT2) + (meeT3 / 545868);
        meeD = proper_ang(meeD) * dr;
        var meeM1 = 134.96 + (477198.8676 * meeT) + (0.0089970 * meeT2) + (meeT3 / 69699);
        meeM1 = proper_ang(meeM1) * dr;
        var meeM = 357.53 + (35999.0503 * meeT);
        meeM = proper_ang(meeM) * dr;
        var elong = meeD * rd + 6.29 * sin(meeM1);
        elong = elong - 2.10 * sin(meeM);
        elong = elong + 1.27 * sin(2 * meeD - meeM1);
        elong = elong + 0.66 * sin(2 * meeD);
        elong = proper_ang(elong);
        elong = round(elong);
        var moonNum = ((elong + 6.43) / 360) * 28;
        moonNum = floor(moonNum);
        if (moonNum == 28) moonNum = 0;
        if (moonNum < 10) moonNum = "0" + moonNum;
        var moonImage = "moon_files/moon" + moonNum.toString() + ".gif"
    }
    var moonPhase = " new Moon";
    if ((moonNum > 03) && (moonNum < 11)) moonPhase = " First Quarter";
    if ((moonNum > 10) && (moonNum < 18)) moonPhase = " Full Moon";
    if ((moonNum > 17) && (moonNum < 25)) moonPhase = " Last Quarter";

    if ((moonNum == 01) || (moonNum == 8) || (moonNum == 15) || (moonNum == 22)) {
        moonPhase = " 1 day past" + moonPhase
    }
    if ((moonNum == 02) || (moonNum == 9) || (moonNum == 16) || (moonNum == 23)) {
        moonPhase = " 2 days past" + moonPhase
    }
    if ((moonNum == 03) || (moonNum == 10) || (moonNum == 17) || (moonNum == 24)) {
        moonPhase = " 3 days past" + moonPhase
    }
    if ((moonNum == 04) || (moonNum == 11) || (moonNum == 18) || (moonNum == 25)) {
        moonPhase = " 3 days before" + moonPhase
    }
    if ((moonNum == 05) || (moonNum == 12) || (moonNum == 19) || (moonNum == 26)) {
        moonPhase = " 2 days before" + moonPhase
    }
    if ((moonNum == 06) || (moonNum == 13) || (moonNum == 20) || (moonNum == 27)) {
        moonPhase = " 1 day before" + moonPhase
    }

    document.moonfaseBox.moonphasetext.value = moonPhase;
    document.slideshow.src = moonImage;
    // document.moonfaseBox.elongation.value = elong
}

