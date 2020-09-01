/*
 Almanac v2.2

 Commented out 2009-specific event list Jan 27, 2010 -- DAF

 JavaScript programming and graphics,
 copyright: Adrian R. Ashford, December 29th, 2008.

 Used with permission by Sky Publishing Corporation.

 http://www.skyandtelescope.com/templates/almanac_v2.js

 Parts of sunrise, sunset, moonrise and moonset from
 moonup.bas and sunup.bas by Roger W. Sinnott,
 Sky and Telescope magazine.
 Free date & time validation scripts:
 Sandeep Tamhankar
 stamhankar@hotmail.com
 http://javascript.internet.com

 Free dynamic form event script:
 http://www.hscripts.com

 Free variable passing in URL script:
 http://javascript.internet.com

 Public domain cookie scripts:
 Dustin Diaz
 http://www.dustindiaz.com/top-ten-javascript/
 */


/*
 Cycle through moon phase images, to select correct Moonphase image on page load- write to page
 */
var myImage = ["almanac_files/mp0.gif","almanac_files/mp1.gif","almanac_files/mp2.gif","almanac_files/mp3.gif","almanac_files/mp4.gif","almanac_files/mp5.gif","almanac_files/mp6.gif","almanac_files/mp7.gif","almanac_files/mp8.gif","almanac_files/mp9.gif","almanac_files/mp10.gif","almanac_files/mp11.gif","almanac_files/mp12.gif","almanac_files/mp13.gif","almanac_files/mp14.gif","almanac_files/mp15.gif","almanac_files/mp16.gif","almanac_files/mp17.gif","almanac_files/mp18.gif","almanac_files/mp19.gif","almanac_files/mp20.gif","almanac_files/mp21.gif","almanac_files/mp22.gif","almanac_files/mp23.gif","almanac_files/mp24.gif","almanac_files/mp25.gif","almanac_files/mp26.gif","almanac_files/mp27.gif"];
thisImage = 0;
images = myImage.length - 1;


/*
 Get parameters for Latitude, Longitude, and time from URL
 */
function getParams()
{
    var idx = document.URL.indexOf('?');
    var params = [];
    if (idx != -1) {
        var pairs = document.URL.substring(idx+1, document.URL.length).split('&');
        for (var i=0; i<pairs.length; i++)
        {
            nameVal = pairs[i].split('=');
            params[nameVal[0]] = nameVal[1];
        }
    }
    return params;
}


/*
 Initialize variables from URL
 */
params = getParams();


/*
 Set variables from custom URL set in the params
 */
if(window.location.href.indexOf("latitude") != -1) {
    latitude = parseFloat(decodeURI(params["latitude"]));
    longitude = parseFloat(decodeURI(params["longitude"]));
    t_zone = parseFloat(decodeURI(params["tzone"]));
    UTdate = decodeURI(params["UTdate"]);
    UTtime = decodeURI(params["UTtime"]);
}

/*
 Initialize default variables
 */
else {
    latitude = 42.383;
    longitude = 71.133;
    t_zone = -5;
    UTdate = "now";
    UTtime = "now";
}

/*
 Set country option variables in array
 */
var country = [
    "Select",
    "USA",
    "Canada",
    "Afghanistan",
    "Albania",
    "Algeria",
    "Angola",
    "Argentina",
    "Armenia",
    "Australia",
    "Austria",
    "Azerbaijan",
    "Bahamas",
    "Bangladesh",
    "Belarus",
    "Belgium",
    "Belize",
    "Benin",
    "Bhutan",
    "Bolivia",
    "Bosnia_and_Herzegovina",
    "Botswana",
    "Brazil",
    "Brunei",
    "Bulgaria",
    "Burkina_Faso",
    "Burma",
    "Burundi",
    "Cambodia",
    "Cameroon",
    "Cape_Verde",
    "Central_African_Rep",
    "Chad",
    "Chile",
    "China",
    "Colombia",
    "Congo",
    "Congo_Dem_Rep",
    "Costa_Rica",
    "Cote_dIvoire",
    "Croatia",
    "Cuba",
    "Cyprus",
    "Czech_Republic",
    "Denmark",
    "Djibouti",
    "Dominican_Rep",
    "East_Timor",
    "Ecuador",
    "Egypt",
    "El_Salvador",
    "Eritrea",
    "Estonia",
    "Ethiopia",
    "Falkland_Islands",
    "Fiji",
    "Finland",
    "France",
    "French_Guiana",
    "French_Polynesia",
    "Gabon",
    "Gambia",
    "Georgia",
    "Germany",
    "Ghana",
    "Greece",
    "Greenland",
    "Guatemala",
    "Guinea",
    "Guinea_Bissau",
    "Guyana",
    "Haiti",
    "Honduras",
    "Hungary",
    "Iceland",
    "India",
    "Indonesia",
    "Iran",
    "Iraq",
    "Ireland",
    "Israel",
    "Italy",
    "Jamaica",
    "Japan",
    "Jordan",
    "Kazakhstan",
    "Kenya",
    "Korea_North",
    "Korea_South",
    "Kyrgyzstan",
    "Laos",
    "Latvia",
    "Lebanon",
    "Lesotho",
    "Liberia",
    "Libya",
    "Lithuania",
    "Macedonia",
    "Madagascar",
    "Malawi",
    "Malaysia",
    "Mali",
    "Mauritania",
    "Mexico",
    "Moldova",
    "Mongolia",
    "Morocco",
    "Mozambique",
    "Namibia",
    "Nepal",
    "Netherlands",
    "New_Zealand",
    "Nicaragua",
    "Niger",
    "Nigeria",
    "Northern_Ireland",
    "Norway",
    "Pakistan",
    "Panama",
    "Papua_New_Guinea",
    "Paraguay",
    "Peru",
    "Philippines",
    "Poland",
    "Portugal",
    "Puerto_Rico",
    "Qatar",
    "Romania",
    "Russia",
    "Rwanda",
    "Saudi_Arabia",
    "Senegal",
    "Sierra_Leone",
    "Singapore",
    "Slovakia",
    "Slovenia",
    "Somalia",
    "South_Africa",
    "Spain",
    "Sri_Lanka",
    "Sudan",
    "Suriname",
    "Swaziland",
    "Sweden",
    "Switzerland",
    "Syria",
    "Taiwan",
    "Tajikistan",
    "Tanzania",
    "Tasmania",
    "Thailand",
    "Togo",
    "Trinidad_and_Tobago",
    "Tunisia",
    "Turkey",
    "Turkmenistan",
    "Uganda",
    "Ukraine",
    "United_Arab_Emirates",
    "United_Kingdom",
    "Uruguay",
    "Uzbekistan",
    "Vatican_City",
    "Venezuela",
    "Vietnam",
    "Yemen",
    "Yugoslavia",
    "Zambia",
    "Zimbabwe"];

/*
 Create array that holds all Cities
 */
Select = new Array("City");


/*
 Set American Cities into new array
 */
var USA = ["---",
    "Albany, NY",
    "Albuquerque, NM",
    "Amarillo, TX",
    "Anchorage, AK",
    "Atlanta, GA",
    "Austin, TX",
    "Baker, OR",
    "Baltimore, MD",
    "Bangor, ME",
    "Birmingham, AL",
    "Bismarck, ND",
    "Boise, ID",
    "Boston, MA",
    "Buffalo, NY",
    "Carlsbad, NM",
    "Charleston, SC",
    "Charleston, WV",
    "Charlotte, NC",
    "Cheyenne, WY",
    "Chicago, IL",
    "Cincinnati, OH",
    "Cleveland, OH",
    "Columbia, SC",
    "Columbus, OH",
    "Dallas, TX",
    "Denver, CO",
    "Des Moines, IA",
    "Detroit, MI",
    "Dubuque, IA",
    "Duluth, MN",
    "Eastport, ME",
    "El Centro, CA",
    "El Paso, TX",
    "Eugene, OR",
    "Fargo, ND",
    "Flagstaff, AZ",
    "Fort Worth, TX",
    "Fresno, CA",
    "Grand Junction, CO",
    "Grand Rapids, MI",
    "Havre, MT",
    "Helena, MT",
    "Honolulu, HI",
    "Hot Springs, AR",
    "Houston, TX",
    "Idaho Falls, ID",
    "Indianapolis, IN",
    "Jackson, MS",
    "Jacksonville, FL",
    "Juneau, AK",
    "Kansas City, MO",
    "Key West, FL",
    "Klamath Falls, OR",
    "Knoxville, TN",
    "Las Vegas, NV",
    "Lewiston, ID",
    "Lincoln, NE",
    "Long Beach, CA",
    "Los Angeles, CA",
    "Louisville, KY",
    "Manchester, NH",
    "Memphis, TN",
    "Miami, FL",
    "Milwaukee, WI",
    "Minneapolis, MN",
    "Mobile, AL",
    "Montgomery, AL",
    "Montpelier, VT",
    "Nashville, TN",
    "Newark, NJ",
    "New Haven, CT",
    "New Orleans, LA",
    "New York, NY",
    "Nome, AK",
    "Oakland, CA",
    "Oklahoma City, OK",
    "Omaha, NE",
    "Philadelphia, PA",
    "Phoenix, AZ",
    "Pierre, SD",
    "Pittsburgh, PA",
    "Portland, ME",
    "Portland, OR",
    "Providence, RI",
    "Raleigh, NC",
    "Reno, NV",
    "Richfield, UT",
    "Richmond, VA",
    "Roanoke, VA",
    "Sacramento, CA",
    "St Louis, MO",
    "Salt Lake City, UT",
    "San Antonio, TX",
    "San Diego, CA",
    "San Francisco, CA",
    "San Jose, CA",
    "Santa Fe, NM",
    "Savannah, GA",
    "Seattle, WA",
    "Shreveport, LA",
    "Sioux Falls, SD",
    "Sitka, AK",
    "Spokane, WA",
    "Springfield, IL",
    "Springfield, MA",
    "Springfield, MO",
    "Syracuse, NY",
    "Tampa, FL",
    "Toledo, OH",
    "Tulsa, OK",
    "Virginia Beach, VA",
    "Washington, D.C.",
    "Wichita, KS",
    "Wilmington, NC"];


/*
 Set Canadian Cities into new array
 */
var Canada = ["---",
    "Calgary, AB",
    "Charlottetown, PE",
    "Chicoutimi, QC",
    "Edmonton, AB",
    "Guelph, ON",
    "Halifax, NS",
    "Hamilton, ON",
    "Hull, QC",
    "Iqaluit, NU",
    "Kelowna, BC",
    "Kingston, ON",
    "Kitchener, ON",
    "London, ON",
    "Montreal, QC",
    "Oshawa, ON",
    "Ottawa, ON",
    "Quebec City, QC",
    "Regina, SK",
    "Saint John, NB",
    "Saskatoon, SK",
    "Sherbrooke, QC",
    "St Catharines, ON",
    "St Johns, NL",
    "Sudbury, ON",
    "Thunder Bay, ON",
    "Toronto, ON",
    "Trois-Rivieres, QC",
    "Vancouver, BC",
    "Victoria, BC",
    "Whitehorse, YT",
    "Windsor, ON",
    "Winnipeg, MB",
    "Yellowknife, NT"];


/*
 Set Misc. Cities into new array
 */
Afghanistan = ["---","Kabul"];
Albania = ["---","Tirana"];
Algeria = ["---","Algiers"];
Angola = ["---","Luanda"];
Argentina = ["---","Buenos Aires","Cordoba"];
Armenia = ["---","Yerevan"];
Australia = ["---","Adelaide","Brisbane","Darwin","Melbourne","Perth","Sydney"];
Austria = ["---","Vienna"];
Azerbaijan = ["---","Baku"];
Bahamas = ["---"," Nassau"];
Bangladesh = ["---","Chittagong"];
Belarus = ["---","Minsk"];
Belgium = ["---","Brussels"];
Belize = ["---","Belmopan"];
Benin = ["---","Porto Novo"];
Bhutan = ["---","Thimphu"];
Bolivia = ["---","La Paz"];
Bosnia_and_Herzegovina = ["---","Sarajevo"];
Botswana = ["---","Gaborone"];
Brazil =["---","Belem","Rio de Janeiro","Salvador","Sao Paulo"];
Brunei = ["---","Bandar Seri Begawan"];
Bulgaria = ["---","Sofia"];
Burkina_Faso = ["---","Ouagadougou"];
Burma = ["---","Rangoon"];
Burundi = ["---","Bujumbura"];
Cambodia = ["---","Phnom Penh"];
Cameroon = ["---","Yaounde"];
Cape_Verde = ["---","Praia"];
Central_African_Rep = ["---","Bangui"];
Chad = ["---","Ndjamena"];
Chile = ["---","Iquique","Santiago"];
China = ["---","Beijing","Canton","Chongqing","Hong Kong","Nanjing","Shanghai"];
Colombia = ["---","Bogota"];
Congo = ["---","Brazzaville"];
Congo_Dem_Rep = ["---","Kinshasa"];
Costa_Rica = ["---","San Jose"];
Cote_dIvoire = ["---","Abidjan"];
Croatia = ["---","Zagreb"];
Cuba = ["---","Havana"];
Cyprus = ["---","Nicosia"];
Czech_Republic = ["---","Prague"];
Denmark = ["---","Copenhagen"];
Djibouti = ["---","Djibouti"];
Dominican_Rep = ["---","Santo Domingo"];
East_Timor = ["---","Dili"];
Ecuador = ["---","Guayaquil"];
Egypt = ["---","Cairo"];
El_Salvador = ["---","San Salvador"];
Eritrea = ["---","Asmera"];
Estonia = ["---","Tallinn"];
Ethiopia = ["---","Addis Ababa"];
Falkland_Islands = ["---","Port Stanley"];
Fiji = ["---","Suva"];
Finland = ["---","Helsinki"];
France = ["---","Bordeaux","Lyons","Marseilles","Paris"];
French_Guiana = ["---","Cayenne"];
French_Polynesia = ["---","Papeete"];
Gabon = ["---","Libreville"];
Gambia = ["---","Banjul"];
Georgia = ["---","Tbilisi"];
Germany = ["---","Berlin","Bremen","Frankfurt","Hamburg","Munich"];
Ghana = ["---","Accra"];
Greece = ["---","Athens"];
Greenland = ["---","Nuuk"];
Guatemala = ["---","Guatemala City"];
Guinea = ["---","Conakry"];
Guinea_Bissau = ["---","Bissau"];
Guyana = ["---","Georgetown"];
Haiti = ["---","Port-au-Prince"];
Honduras = ["---","Tegucigalpa"];
Hungary = ["---","Budapest"];
Iceland = ["---","Reykjavik"];
India = ["---","Bombay","Calcutta","Delhi"];
Indonesia = ["---","Jakarta"];
Iran = ["---","Teheran"];
Iraq = ["---","Baghdad"];
Ireland = ["---","Dublin"];
Israel = ["---","Jerusalem","Tel Aviv"];
Italy = ["---","Milan","Naples","Rome","Venice"];
Jamaica =["---","Kingston"];
Japan = ["---","Nagasaki","Nagoya","Osaka","Tokyo"];
Jordan = ["---","Amman"];
Kazakhstan = ["---","Almaty"];
Kenya =["---","Nairobi"];
Korea_North = ["---","Pyongyang"];
Korea_South = ["---","Seoul"];
Kyrgyzstan = ["---","Bishkek"];
Laos = ["---","Vientiane"];
Latvia = ["---","Riga"];
Lebanon =["---","Beirut"];
Lesotho = ["---","Maseru"];
Liberia = ["---","Monrovia"];
Libya = ["---","Tripoli"];
Lithuania = ["---","Vilnius"];
Macedonia = ["---","Skopje"];
Madagascar = ["---","Tananarive"];
Malawi = ["---","Lilongwe"];
Malaysia = ["---","Kuala Lumpur"];
Mali = ["---","Bamako"];
Mauritania = ["---","Nouakchott"];
Mexico = ["---","Chihuahua","Mazatlan","Mexico City","Veracruz"];
Moldova = ["---","Kishinev"];
Mongolia = ["---","Ulaanbaatar"];
Morocco = ["---","Casablanca"];
Mozambique = ["---","Maputo"];
Namibia = ["---","Windhoek"];
Nepal = ["---","Kathmandu"];
Netherlands = ["---","Amsterdam"];
New_Zealand = ["---","Auckland","Christchurch","Wellington"];
Nicaragua = ["---","Managua"];
Niger = ["---","Niamey"];
Nigeria = ["---","Lagos"];
Northern_Ireland = ["---","Belfast"];
Norway = ["---","Hammerfest","Oslo"];
Pakistan = ["---","Islamabad"];
Panama = ["---","Panama City"];
Papua_New_Guinea = ["---","Port Moresby"];
Paraguay = ["---","Asuncion"];
Peru = ["---","Lima"];
Philippines = ["---","Manila"];
Poland = ["---","Warsaw"];
Portugal = ["---","Lisbon"];
Puerto_Rico = ["---","San Juan"];
Qatar = ["---","Doha"];
Romania = ["---","Bucharest"];
Russia = ["---","Irkutsk","Moscow","St Petersburg","Vladivostok"];
Rwanda = ["---","Kigali"];
Saudi_Arabia = ["---","Mecca"];
Senegal = ["---","Dakar"];
Sierra_Leone = ["---","Freetown"];
Singapore = ["---","Singapore"];
Slovakia = ["---","Bratislava"];
Slovenia = ["---","Ljubljana"];
Somalia = ["---","Mogadishu"];
South_Africa = ["---","Johannesburg","CapeTown","Durban"];
Spain =["---","Barcelona","Madrid"];
Sri_Lanka = ["---","Colombo"];
Sudan = ["---","Khartoum"];
Suriname = ["---","Paramaribo"];
Swaziland = ["---","Mbabane"];
Sweden = ["---","Stockholm"];
Switzerland = ["---","Zurich"];
Syria = ["---","Damascus"];
Taiwan = ["---","Taipei"];
Tajikistan = ["---","Dushanbe"];
Tanzania = ["---","Dar es Salaam"];
Tasmania = ["---","Hobart"];
Thailand = ["---","Bangkok"];
Togo = ["---","Lome"];
Trinidad_and_Tobago = ["---","Port of Spain"];
Tunisia = ["---","Tunis"];
Turkey = ["---","Ankara"];
Turkmenistan = ["---","Ashgabat"];
Uganda = ["---","Kampala"];
Ukraine = ["---","Odessa"];
United_Arab_Emirates = ["---","Dubai"];
United_Kingdom = ["---","Aberdeen","Birmingham","Bristol","Cardiff","Edinburgh","Glasgow","Leeds","Liverpool","London","Manchester","Newcastle","Norwich","Plymouth"];
Uruguay = ["---","Montevideo"];
Uzbekistan = ["---","Tashkent"];
Vatican_City = ["---","Vatican City"];
Venezuela = ["---","Caracas"];
Vietnam = ["---","Hanoi"];
Yemen = ["---","Sana"];
Yugoslavia = ["---","Belgrade"];
Zambia = ["---","Lusaka"];
Zimbabwe = ["---","Harare"];

City = "...";


/*
 Set city list to match selected state/country- e.g. if "USA" selected city list only shows US cities
 */
function changeval()
{
    var val1 = document.planets.sel1.value;
    var optionArray = eval(val1);
    for (var df=0; df<optionArray.length; df++)
    {
        var ss = document.planets.sel2;
        ss.options.length = 0;
        for (var ff=0; ff<optionArray.length; ff++)
        {
            var val = optionArray[ff];
            ss.options[ff] = new Option(val,val);
        }
    }
}


/*
 Assign correct latitude + longitude to selected city
 */
function recordval()
{
    City = document.planets.sel2.value;

    switch (City) {
        /* US cities listing */
        case 'Albany, NY' :
            latitude = 42.666667;
            longitude = 73.75;
            t_zone = -5;
            break;
        case 'Albuquerque, NM':
            latitude = 35.85;
            longitude = 106.65;
            t_zone = -7;
            break;
        case 'Amarillo, TX':
            latitude = 35.183333;
            longitude = 101.833333;
            t_zone = -6;
            break;
        case 'Anchorage, AK':
            latitude = 61.216667;
            longitude = 149.9;
            t_zone = -9;
            break;
        case 'Atlanta, GA':
            latitude = 33.75;
            longitude = 84.383333;
            t_zone = -5;
            break;
        case 'Austin, TX':
            latitude = 30.266667;
            longitude = 97.733333;
            t_zone = -6;
            break;
        case 'Baker, OR':
            latitude = 44.783333;
            longitude = 117.833333;
            t_zone = -8;
            break;
        case 'Baltimore, MD':
            latitude = 39.3;
            longitude = 76.633333;
            t_zone = -5;
            break;
        case 'Bangor, ME':
            latitude = 44.8;
            longitude = 68.783333;
            t_zone = -5;
            break;
        case 'Birmingham, AL':
            latitude = 33.5;
            longitude = 86.833333;
            t_zone = -6;
            break;
        case 'Bismarck, ND':
            latitude = 46.8;
            longitude = 100.783333;
            t_zone = -6;
            break;
        case 'Boise, ID':
            latitude = 43.6;
            longitude = 116.216667;
            t_zone = -7;
            break;
        case 'Boston, MA':
            latitude = 42.35;
            longitude = 71.083333;
            t_zone = -5;
            break;
        case 'Buffalo, NY':
            latitude = 42.916667;
            longitude = 78.833333;
            t_zone = -5;
            break;
        case 'Carlsbad, NM':
            latitude = 32.433333;
            longitude = 104.25;
            t_zone = -7;
            break;
        case 'Charleston, SC':
            latitude = 32.783333;
            longitude = 79.933333;
            t_zone = -5;
            break;
        case 'Charleston, WV':
            latitude = 38.35;
            longitude = 81.633333;
            t_zone = -5;
            break;
        case 'Charlotte, NC':
            latitude = 35.233333;
            longitude = 80.833333;
            t_zone = -5;
            break;
        case 'Cheyenne, WY':
            latitude = 41.15;
            longitude = 104.866667;
            t_zone = -7;
            break;
        case  'Chicago, IL':
            latitude = 41.833333;
            longitude = 87.616667;
            t_zone = -6;
            break;
        case 'Cincinnati, OH':
            latitude = 39.133333;
            longitude = 84.5;
            t_zone = -5;
            break;
        case 'Cleveland, OH':
            latitude = 41.466667;
            longitude = 81.616667;
            t_zone = -5;
            break;
        case 'Columbia, SC':
            latitude = 34;
            longitude = 81.033333;
            t_zone = -5;
            break;
        case 'Columbus, OH':
            latitude = 40;
            longitude = 83.016667;
            t_zone = -5;
            break;
        case 'Dallas, TX':
            latitude = 32.766667;
            longitude = 96.766667;
            t_zone = -6;
            break;
        case 'Denver, CO':
            latitude = 39.75;
            longitude = 105;
            t_zone = -7;
            break;
        case 'Des Moines, IA':
            latitude = 41.583333;
            longitude = 93.616667;
            t_zone = -6;
            break;
        case 'Detroit, MI':
            latitude = 42.333333;
            longitude = 83.05;
            t_zone = -5;
            break;
        case 'Dubuque, IA':
            latitude = 42.516667;
            longitude = 90.666667;
            t_zone = -6;
            break;
        case 'Duluth, MN':
            latitude = 46.816667;
            longitude = 92.083333;
            t_zone = -6;
            break;
        case 'Eastport, ME':
            latitude = 44.9;
            longitude = 67;
            t_zone = -5;
            break;
        case 'El Centro, CA':
            latitude = 32.633333;
            longitude = 115.55;
            t_zone = -8;
            break;
        case 'El Paso, TX':
            latitude = 31.766667;
            longitude = 106.483333;
            t_zone = -7;
            break;
        case 'Eugene, OR':
            latitude = 44.05;
            longitude = 123.083333;
            t_zone = -8;
            break;
        case 'Fargo, ND':
            latitude = 46.866667;
            longitude = 96.8;
            t_zone = -6;
            break;
        case 'Flagstaff, AZ':
            latitude = 35.216667;
            longitude = 111.683333;
            t_zone = -7;
            break;
        case  'Fort Worth, TX':
            latitude = 32.716667;
            longitude = 97.316667;
            t_zone = -6;
            break;
        case 'Fresno, CA':
            latitude = 36.733333;
            longitude = 119.8;
            t_zone = -8;
            break;
        case 'Grand Junction, CO':
            latitude = 39.083333;
            longitude = 108.55;
            t_zone = -7;
            break;
        case 'Grand Rapids, MI':
            latitude = 42.966667;
            longitude = 85.666667;
            t_zone = -5;
            break;
        case 'Havre, MT':
            latitude = 48.55;
            longitude = 109.716667;
            t_zone = -7;
            break;
        case 'Helena, MT':
            latitude = 46.583333;
            longitude = 112.033333;
            t_zone = -7;
            break;
        case 'Honolulu, HI':
            latitude = 21.3;
            longitude = 157.833333;
            t_zone = -10;
            break;
        case 'Hot Springs, AR':
            latitude = 34.516667;
            longitude = 93.05;
            t_zone = -6;
            break;
        case 'Houston, TX':
            latitude = 29.75;
            longitude = 95.35;
            t_zone = -6;
            break;
        case 'Idaho Falls, ID':
            latitude = 43.5;
            longitude = 112.016667;
            t_zone = -7;
            break;
        case 'Indianapolis, IN':
            latitude = 39.766667;
            longitude = 86.166667;
            t_zone = -5;
            break;
        case 'Jackson, MS':
            latitude = 32.333333;
            longitude = 90.2;
            t_zone = -6;
            break;
        case 'Jacksonville, FL':
            latitude = 30.366667;
            longitude = 81.666667;
            t_zone = -5;
            break;
        case 'Juneau, AK':
            latitude = 58.3;
            longitude = 134.4;
            t_zone = -9;
            break;
        case 'Kansas City, MO':
            latitude = 39.1;
            longitude = 94.583333;
            t_zone = -6;
            break;
        case 'Key West, FL':
            latitude = 24.55;
            longitude = 81.8;
            t_zone = -5;
            break;
        case 'Klamath Falls, OR':
            latitude = 42.166667;
            longitude = 121.733333;
            t_zone = -8;
            break;
        case  'Knoxville, TN':
            latitude = 35.95;
            longitude = 83.933333;
            t_zone = -5;
            break;
        case 'Las Vegas, NV':
            latitude = 36.166667;
            longitude = 115.2;
            t_zone = -8;
            break;
        case  'Lewiston, ID':
            latitude = 46.4;
            longitude = 117.033333;
            t_zone = -8;
            break;
        case 'Lincoln, NE':
            latitude = 40.833333;
            longitude = 96.666667;
            t_zone = -6;
            break;
        case  'Long Beach, CA':
            latitude = 33.766667;
            longitude = 118.183333;
            t_zone = -8;
            break;
        case  'Los Angeles, CA':
            latitude = 34.05;
            longitude = 118.25;
            t_zone = -8;
            break;
        case 'Louisville, KY':
            latitude = 38.25;
            longitude = 85.766667;
            t_zone = -5;
            break;
        case 'Manchester, NH':
            latitude = 43;
            longitude = 71.5;
            t_zone = -5;
            break;
        case 'Memphis, TN':
            latitude = 35.15;
            longitude = 90.05;
            t_zone = -6;
            break;
        case 'Miami, FL':
            latitude = 25.766667;
            longitude = 80.2;
            t_zone = -5;
            break;
        case 'Milwaukee, WI':
            latitude = 43.033333;
            longitude = 87.916667;
            t_zone = -6;
            break;
        case 'Minneapolis, MN':
            latitude = 44.983333;
            longitude = 93.233333;
            t_zone = -6;
            break;
        case 'Mobile, AL':
            latitude = 30.7;
            longitude = 88.05;
            t_zone = -6;
            break;
        case  'Montgomery, AL':
            latitude = 32.35;
            longitude = 86.3;
            t_zone = -6;
            break;
        case 'Montpelier, VT':
            latitude = 44.25;
            longitude = 72.533333;
            t_zone = -5;
            break;
        case  'Nashville, TN':
            latitude = 36.166667;
            longitude = 86.783333;
            t_zone = -6;
            break;
        case  'Newark, NJ':
            latitude = 40.733333;
            longitude = 74.166667;
            t_zone = -5;
            break;
        case  'New Haven, CT':
            longitude = 72.916667;
            t_zone = -5;
            break;
        case  'New Orleans, LA':
            latitude = 29.95;
            longitude = 90.066667;
            t_zone = -6;
            break;
        case 'New York, NY':
            latitude = 40.783333;
            longitude = 73.966667;
            t_zone = -5;
            break;
        case  'Nome, AK':
            latitude = 64.416667;
            longitude = 165.5;
            t_zone = -9;
            break;
        case  'Oakland, CA':
            latitude = 37.8;
            longitude = 122.266667;
            t_zone = -8;
            break;
        case 'Oklahoma City, OK':
            latitude = 35.433333;
            longitude = 97.466667;
            t_zone = -6;
            break;
        case 'Omaha, NE':
            latitude = 41.25;
            longitude = 95.933333;
            t_zone = -6;
            break;
        case 'Philadelphia, PA':
            latitude = 39.95;
            longitude = 75.166667;
            t_zone = -5;
            break;
        case  'Phoenix, AZ':
            latitude = 33.483333;
            longitude = 112.066667;
            t_zone = -7;
            break;
        case 'Pierre, SD':
            latitude = 44.366667;
            longitude = 100.35;
            t_zone = -6;
            break;
        case 'Pittsburgh, PA':
            latitude = 40.45;
            longitude = 79.95;
            t_zone = -5;
            break;
        case  'Portland, ME':
            latitude = 43.666667;
            longitude = 70.25;
            t_zone = -5;
            break;
        case 'Portland, OR':
            latitude = 45.516667;
            longitude = 122.683333;
            t_zone = -8;
            break;
        case 'Providence, RI':
            latitude = 41.833333;
            longitude = 71.4;
            t_zone = -5;
            break;
        case  'Raleigh, NC':
            latitude = 35.766667;
            longitude = 78.65;
            t_zone = -5;
            break;
        case  'Reno, NV':
            latitude = 39.5;
            longitude = 119.816667;
            t_zone = -8;
            break;
        case 'Richfield, UT':
            latitude = 38.766667;
            longitude = 112.083333;
            t_zone = -7;
            break;
        case  'Richmond, VA':
            latitude = 37.55;
            longitude = 77.483333;
            t_zone = -5;
            break;
        case 'Roanoke, VA':
            latitude = 37.283333;
            longitude = 79.95;
            t_zone = -5;
            break;
        case 'Sacramento, CA':
            latitude = 38.583333;
            longitude = 121.5;
            t_zone = -8;
            break;
        case 'St Louis, MO':
            latitude = 38.583333;
            longitude = 90.2;
            t_zone = -6;
            break;
        case  'Salt Lake City, UT':
            latitude = 40.766667;
            longitude = 111.9;
            t_zone = -7;
            break;
        case 'San Antonio, TX':
            latitude = 29.383333;
            longitude = 98.55;
            t_zone = -6;
            break;
        case 'San Diego, CA':
            latitude = 32.7;
            longitude = 117.166667;
            t_zone = -8;
            break;
        case 'San Francisco, CA':
            latitude = 37.783333;
            longitude = 122.433333;
            t_zone = -8;
            break;
        case 'San Jose, CA':
            latitude = 37.333333;
            longitude = 121.883333;
            t_zone = -8;
            break;
        case 'Santa Fe, NM':
            latitude = 35.683333;
            longitude = 105.95;
            t_zone = -7;
            break;
        case 'Savannah, GA':
            latitude = 32.083333;
            longitude = 81.083333;
            t_zone = -5;
            break;
        case 'Seattle, WA':
            latitude = 47.616667;
            longitude = 122.333333;
            t_zone = -8;
            break;
        case  'Shreveport, LA':
            latitude = 32.466667;
            longitude = 93.7;
            t_zone = -6;
            break;
        case 'Sioux Falls, SD':
            latitude = 43.55;
            longitude = 96.733333;
            t_zone = -6;
            break;
        case 'Sitka, AK':
            latitude = 57.166667;
            longitude = 135.25;
            t_zone = -9;
            break;
        case 'Spokane, WA':
            latitude = 47.666667;
            longitude = 117.433333;
            t_zone = -8;
            break;
        case 'Springfield, IL':
            latitude = 39.8;
            longitude = 89.633333;
            t_zone = -6;
            break;
        case 'Springfield, MA':
            latitude = 42.1;
            longitude = 72.566667;
            t_zone = -5;
            break;
        case 'Springfield, MO':
            latitude = 37.216667;
            longitude = 93.283333;
            t_zone = -6;
            break;
        case 'Syracuse, NY':
            latitude = 43.033333;
            longitude = 76.133333;
            t_zone = -5;
            break;
        case 'Tampa, FL':
            latitude = 27.95;
            longitude = 82.45;
            t_zone = -5;
            break;
        case 'Toledo, OH':
            latitude = 41.65;
            longitude = 83.55;
            t_zone = -5;
            break;
        case 'Tulsa, OK':
            latitude = 36.15;
            longitude = 95.983333;
            t_zone = -6;
            break;
        case 'Virginia Beach, VA':
            latitude = 36.85;
            longitude = 75.966667;
            t_zone = -5;
            break;
        case 'Washington, D.C.':
            latitude = 38.883333;
            longitude = 77.033333;
            t_zone = -5;
            break;
        case 'Wichita, KS':
            latitude = 37.716667;
            longitude = 97.283333;
            t_zone = -6;
            break;
        case 'Wilmington, NC':
            latitude = 34.233333;
            longitude = 77.95;
            t_zone = -5;
            break;

        /* Canadian cities listing */
        case 'Calgary, AB':
            latitude = 51 + 6 / 60;
            longitude = 114 + 1 / 60;
            t_zone = -7;
            break;
        case 'Charlottetown, PE':
            latitude = 46 + 14 / 60;
            longitude = 63 + 9 / 60;
            t_zone = -4;
            break;
        case  'Chicoutimi, QC':
            latitude = 48 + 25 / 60;
            longitude = 71 + 5 / 60;
            t_zone = -5;
            break;
        case 'Edmonton, AB':
            latitude = 53 + 34 / 60;
            longitude = 113 + 31 / 60;
            t_zone = -7;
            break;
        case 'Guelph, ON':
            latitude = 43 + 33 / 60;
            longitude = 80 + 15 / 60;
            t_zone = -5;
            break;
        case 'Halifax, NS':
            latitude = 44 + 39 / 60;
            longitude = 63 + 34 / 60;
            t_zone = -4;
            break;
        case 'Hamilton, ON':
            latitude = 43 + 16 / 60;
            longitude = 79 + 54 / 60;
            t_zone = -5;
            break;
        case 'Hull, QC':
            latitude = 45 + 26 / 60;
            longitude = 75 + 44 / 60;
            t_zone = -5;
            break;
        case 'Iqaluit, NU':
            latitude = 63 + 45 / 60;
            longitude = 68 + 31 / 60;
            t_zone = -6;
            break;
        case 'Kelowna, BC':
            latitude = 49 + 53 / 60;
            longitude = 119 + 30 / 60;
            t_zone = -8;
            break;
        case 'Kingston, ON':
            latitude = 44 + 16 / 60;
            longitude = 76 + 30 / 60;
            t_zone = -5;
            break;
        case 'Kitchener, ON':
            latitude = 43 + 26 / 60;
            longitude = 80 + 30 / 60;
            t_zone = -5;
            break;
        case 'London, ON':
            latitude = 43 + 2 / 60;
            longitude = 81 + 9 / 60;
            t_zone = -5;
            break;
        case 'Montreal, QC':
            latitude = 45 + 28 / 60;
            longitude = 73 + 45 / 60;
            t_zone = -5;
            break;
        case 'Oshawa, ON':
            latitude = 43 + 54 / 60;
            longitude = 78 + 52 / 60;
            t_zone = -5;
            break;
        case  'Ottawa, ON':
            latitude = 45 + 19 / 60;
            longitude = 75 + 40 / 60;
            t_zone = -5;
            break;
        case 'Quebec City, QC':
            latitude = 46 + 48 / 60;
            longitude = 71 + 23 / 60;
            t_zone = -5;
            break;
        case 'Regina, SK':
            latitude = 50 + 26 / 60;
            longitude = 104 + 40 / 60;
            t_zone = -7;
            break;
        case 'Saint John, NB':
            latitude = 46 + 6 / 60;
            longitude = 64 + 46 / 60;
            t_zone = -4;
            break;
        case 'Saskatoon, SK':
            latitude = 52 + 10 / 60;
            longitude = 106 + 41 / 60;
            t_zone = -7;
            break;
        case 'Sherbrooke, QC':
            latitude = 45 + 24 / 60;
            longitude = 71 + 54 / 60;
            t_zone = -5;
            break;
        case 'St Catharines, ON':
            latitude = 43 + 11 / 60;
            longitude = 79 + 14 / 60;
            t_zone = -5;
            break;
        case 'St Johns, NL':
            latitude = 47 + 37 / 60;
            longitude = 52 + 45 / 60;
            t_zone = -3.5;
            break;
        case 'Sudbury, ON':
            latitude = 46 + 37 / 60;
            longitude = 80 + 48 / 60;
            t_zone = -5;
            break;
        case  'Thunder Bay, ON':
            latitude = 48 + 22 / 60;
            longitude = 89 + 19 / 60;
            t_zone = -5;
            break;
        case 'Toronto, ON':
            latitude = 43 + 41 / 60;
            longitude = 79 + 38 / 60;
            t_zone = -5;
            break;
        case 'Trois-Rivieres, QC':
            latitude = 46 + 21 / 60;
            longitude = 72 + 35 / 60;
            t_zone = -5;
            break;
        case 'Vancouver, BC':
            latitude = 49 + 11 / 60;
            longitude = 123 + 10 / 60;
            t_zone = -8;
            break;
        case 'Victoria, BC':
            latitude = 48 + 25 / 60;
            longitude = 123 + 19 / 60;
            t_zone = -8;
            break;
        case 'Whitehorse, YT':
            latitude = 60 + 43 / 60;
            longitude = 135 + 3 / 60;
            t_zone = -8;
            break;
        case 'Windsor, ON':
            latitude = 42 + 16 / 60;
            longitude = 82 + 58 / 60;
            t_zone = -4;
            break;
        case 'Winnipeg, MB':
            latitude = 49 + 54 / 60;
            longitude = 97 + 14 / 60;
            t_zone = -6;
            break;
        case 'Yellowknife, NT':
            latitude = 62 + 27 / 60;
            longitude = 114 + 24 / 60;
            t_zone = -7;
            break;

        case 'Kabul':
            latitude = 34 + 35 / 60;
            longitude = -(69 + 12 / 60);
            t_zone = 4.5;
            break;

        case 'Luanda':
            latitude = -(8 + 50 / 60);
            longitude = -(13 + 20 / 60);
            t_zone = 0;
            break;

        case 'Algiers':
            latitude = 36.833333;
            longitude = -3;
            t_zone = 1;
            break;
        case 'Buenos Aires':
            latitude = -34.583333;
            longitude = 58.366667;
            t_zone = -3;
            break;
        case 'Cordoba':
            latitude = -31.466667;
            longitude = 64.166667;
            t_zone = -3;
            break;

        case 'Yerevan':
            latitude = 40 + 16 / 60;
            longitude = -(44 + 34 / 60);
            t_zone = 4;
            break;


        case 'Adelaide':
            latitude = -34.916667;
            longitude = -138.6;
            t_zone = 9.5;
            break;
        case  'Brisbane':
            latitude = -27.483333;
            longitude = -153.133333;
            t_zone = 10;
            break;
        case 'Darwin':
            latitude = -12.466667;
            longitude = -130.85;
            t_zone = 9.5;
            break;
        case 'Melbourne':
            latitude = -37.783333;
            longitude = -144.966667;
            t_zone = 10;
            break;
        case 'Perth':
            latitude = -31.95;
            longitude = -115.866667;
            t_zone = 8;
            break;
        case 'Sydney':
            latitude = -34;
            longitude = -151;
            t_zone = 10;
            break;
        case 'Vienna':
            latitude = 48.233333;
            longitude = -16.333333;
            t_zone = 1;
            break;
        case 'Baku':
            latitude = 40 + 22 / 60;
            longitude = -(49 + 53 / 60);
            t_zone = 4;
            break;
        case 'Chittagong':
            latitude = 22 + 21 / 60;
            longitude = -(91 + 50 / 60);
            t_zone = 6;
            break;
        case 'Minsk':
            latitude = 53 + 54 / 60;
            longitude = -(27 + 33 / 60);
            t_zone = 2;
            break;
        case 'Brussels':
            latitude = 50.866667;
            longitude = -4.366667;
            t_zone = 1;
            break;
        case 'La Paz':
            latitude = -16.45;
            longitude = 68.366667;
            t_zone = -4;
            break;
        case 'Belem':
            latitude = -1.466667;
            longitude = 48.483333;
            t_zone = -3;
            break;
        case 'Rio de Janeiro':
            latitude = -22.95;
            longitude = 43.2;
            t_zone = -3;
            break;
        case 'Salvador':
            latitude = -12.933333;
            longitude = 38.45;
            t_zone = -3;
            break;
        case 'Sao Paulo':
            latitude = -23.516667;
            longitude = 46.516667;
            t_zone = -3;
            break;
        case 'Sofia':
            latitude = 42.666667;
            longitude = -23.333333;
            t_zone = 2;
            break;
        case 'Rangoon':
            latitude = 16 + 47 / 60;
            longitude = -(96 + 9 / 60);
            t_zone = 6.5;
            break;
        case 'Phnom Penh':
            latitude = 11 + 33 / 60;
            longitude = -(104 + 51 / 60);
            t_zone = 7;
            break;
        case 'Iquique':
            latitude = -20.166667;
            longitude = 70.116667;
            t_zone = -4;
            break;
        case  'Santiago':
            latitude = -33.466667;
            longitude = 70.75;
            t_zone = -4;
            break;
        case 'Beijing':
            latitude = 39.916667;
            longitude = -116.416667;
            t_zone = 8;
            break;
        case 'Canton':
            latitude = 23.116667;
            longitude = -113.25;
            t_zone = 8;
            break;
        case 'Chongqing':
            latitude = 29.766667;
            longitude = -106.566667;
            t_zone = 8;
            break;
        case  'Hong Kong':
            latitude = 22 + 17 / 60;
            longitude = -(114 + 8 / 60);
            t_zone = 8;
            break;
        case 'Nanjing':
            latitude = 32.05;
            longitude = -118.883333;
            t_zone = 8;
            break;
        case 'Shanghai':
            latitude = 31.166667;
            longitude = -121.466667;
            t_zone = 8;
            break;
        case  'Bogota':
            latitude = 4.533333;
            longitude = 74.25;
            t_zone = -5;
            break;
        case 'Kinshasa':
            latitude = -4.3;
            longitude = -15.283333;
            t_zone = 1;
            break;
        case 'Havana':
            latitude = 23.133333;
            longitude = 82.383333;
            t_zone = -5;
            break;
        case 'Prague':
            latitude = 50.083333;
            longitude = -14.433333;
            t_zone = 1;
            break;
        case 'Copenhagen':
            latitude = 55.666667;
            longitude = -12.566667;
            t_zone = 1;
            break;
        case 'Djibouti':
            latitude = 11.5;
            longitude = -43.05;
            t_zone = 3;
            break;
        case 'Guayaquil':
            latitude = -2.166667;
            longitude = 79.933333;
            t_zone = -5;
            break;
        case 'Cairo':
            latitude = 30.033333;
            longitude = -31.35;
            t_zone = 2;
            break;
        case 'Birmingham':
            latitude = 52.416667;
            longitude = 1.916667;
            t_zone = 0;
            break;
        case 'Bristol':
            latitude = 51.466667;
            longitude = 2.583333;
            t_zone = 0;
            break;
        case 'Leeds':
            latitude = 53.75;
            longitude = 1.5;
            t_zone = 0;
            break;
        case 'Liverpool':
            latitude = 53.416667;
            longitude = 3;
            t_zone = 0;
            break;
        case 'London':
            latitude = 51.533333;
            longitude = 0.083333;
            t_zone = 0;
            break;
        case 'Manchester':
            latitude = 53.5;
            longitude = 2.25;
            t_zone = 0;
            break;
        case 'Newcastle':
            latitude = 54.966667;
            longitude = 1.616667;
            t_zone = 0;
            break;
        case 'Norwich':
            latitude = 52.6309;
            longitude = -1.2973;
            t_zone = 0;
            break;
        case 'Plymouth':
            latitude = 50.416667;
            longitude = 4.083333;
            t_zone = 0;
            break;
        case  'Helsinki':
            latitude = 60.166667;
            longitude = -25;
            t_zone = 2;
            break;
        case 'Bordeaux':
            latitude = 44.833333;
            longitude = 0.516667;
            t_zone = 1;
            break;
        case 'Lyons':
            latitude = 45.75;
            longitude = -4.833333;
            t_zone = 1;
            break;
        case 'Marseilles':
            latitude = 43.333333;
            longitude = -5.333333;
            t_zone = 1;
            break;
        case 'Paris':
            latitude = 48.8;
            longitude = -2.333333;
            t_zone = 1;
            break;
        case 'Cayenne':
            latitude = 4.816667;
            longitude = 52.3;
            t_zone = -4;
            break;
        case 'Berlin':
            latitude = 52.5;
            longitude = -13.416667;
            t_zone = 1;
            break;
        case 'Bremen':
            latitude = 53.083333;
            longitude = -8.816667;
            t_zone = 1;
            break;
        case 'Frankfurt':
            latitude = 50.116667;
            longitude = -8.683333;
            t_zone = 1;
            break;
        case 'Hamburg':
            latitude = 53.55;
            longitude = -10.033333;
            t_zone = 1;
            break;
        case 'Munich':
            latitude = 48.133333;
            longitude = -11.583333;
            t_zone = 1;
            break;
        case 'Athens':
            latitude = 37.966667;
            longitude = -23.716667;
            t_zone = 2;
            break;
        case 'Guatemala City':
            latitude = 14.616667;
            longitude = 90.516667;
            t_zone = -6;
            break;
        case 'Georgetown':
            latitude = 6.75;
            longitude = 58.25;
            t_zone = -3.75;
            break;
        case 'Budapest':
            latitude = 47.5;
            longitude = -19.083333;
            t_zone = 1;
            break;
        case 'Reykjavik':
            latitude = 64.066667;
            longitude = 21.966667;
            t_zone = -1;
            break;
        case 'Bombay':
            latitude = 19;
            longitude = -72.8;
            t_zone = 5.5;
            break;
        case 'Calcutta':
            latitude = 22.566667;
            longitude = -88.4;
            t_zone = 5.5;
            break;
        case 'Delhi':
            latitude = 28.667;
            longitude = -77.233;
            t_zone = 5.5;
            break;
        case 'Jakarta':
            latitude = -6.266667;
            longitude = -106.8;
            t_zone = 7.5;
            break;
        case 'Teheran':
            latitude = 35.75;
            longitude = -51.75;
            t_zone = 3.5;
            break;
        case 'Baghdad':
            latitude = 33 + 20 / 60;
            longitude = -(44 + 26 / 60);
            t_zone = 3;
            break;
        case 'Dublin':
            latitude = 53.333333;
            longitude = 6.25;
            t_zone = 0;
            break;

        case 'Jerusalem':
            latitude = 31 + 47 / 60;
            longitude = -(35 + 13 / 60);
            t_zone = 2;
            break;
        case 'Tel Aviv':
            latitude = 32 + 5 / 60;
            longitude = -(34 + 46 / 60);
            t_zone = 2;
            break;

        case 'Milan':
            latitude = 45.45;
            longitude = -9.166667;
            t_zone = 1;
            break;
        case 'Naples':
            latitude = 40.833333;
            longitude = -14.25;
            t_zone = 1;
            break;
        case 'Rome':
            latitude = 41.9;
            longitude = -12.45;
            t_zone = 1;
            break;
        case 'Venice':
            latitude = 45.433333;
            longitude = -12.333333;
            t_zone = 1;
            break;
        case 'Kingston':
            latitude = 17.983333;
            longitude = 76.816667;
            t_zone = -5;
            break;
        case 'Nagasaki':
            latitude = 32.8;
            longitude = -129.95;
            t_zone = 9;
            break;
        case 'Nagoya':
            latitude = 35.116667;
            longitude = -136.933333;
            t_zone = 9;
            break;
        case 'Osaka':
            latitude = 34.533333;
            longitude = -135.5;
            t_zone = 9;
            break;
        case 'Tokyo':
            latitude = 35.666667;
            longitude = -139.75;
            t_zone = 9;
            break;
        case 'Nairobi':
            latitude = -1.416667;
            longitude = -36.916667;
            t_zone = 3;
            break;
        case 'Beirut':
            latitude = 33 + 54 / 60;
            longitude = -(35 + 32 / 60);
            t_zone = 2;
            break;
        case 'Tripoli':
            latitude = 32.95;
            longitude = -13.2;
            t_zone = 2;
            break;
        case 'Tananarive':
            latitude = -18.833333;
            longitude = -47.55;
            t_zone = 3;
            break;
        case 'Chihuahua':
            latitude = 28.616667;
            longitude = 106.083333;
            t_zone = -6;
            break;
        case 'Mazatlan':
            latitude = 23.2;
            longitude = 106.416667;
            t_zone = -7;
            break;
        case 'Mexico City':
            latitude = 19.433333;
            longitude = 99.116667;
            t_zone = -6;
            break;
        case 'Veracruz':
            latitude = 19.166667;
            longitude = 96.166667;
            t_zone = -6;
            break;
        case 'Amsterdam':
            latitude = 52.366667;
            longitude = -4.883333;
            t_zone = 1;
            break;
        case 'Auckland':
            latitude = -36.883333;
            longitude = -174.75;
            t_zone = 12;
            break;
        case 'Christchurch':
            latitude = -(43 + 31.8 / 60);
            longitude = -(172 + 37.2 / 60);
            t_zone = 12;
            break;
        case 'Wellington':
            latitude = -41.283333;
            longitude = -174.783333;
            t_zone = 12;
            break;
        case 'Belfast':
            latitude = 54.616667;
            longitude = 5.933333;
            t_zone = 0;
            break;
        case 'Hammerfest':
            latitude = 70.633333;
            longitude = -23.633333;
            t_zone = 1;
            break;
        case 'Oslo':
            latitude = 59.95;
            longitude = -10.7;
            t_zone = 1;
            break;
        case 'Islamabad':
            latitude = 33 + 40 / 60;
            longitude = -(73 + 10 / 60);
            t_zone = 5;
            break;
        case 'Panama City':
            latitude = 8.966667;
            longitude = 79.533333;
            t_zone = -5;
            break;
        case 'Port Moresby':
            latitude = -9.416667;
            longitude = -147.133333;
            t_zone = 10;
            break;
        case 'Asuncion':
            latitude = -25.25;
            longitude = 57.666667;
            t_zone = -4;
            break;
        case 'Lima':
            latitude = -12;
            longitude = 77.033333;
            t_zone = -5;
            break;
        case 'Manila':
            latitude = 14.583333;
            longitude = -120.95;
            t_zone = 8;
            break;
        case 'Warsaw':
            latitude = 52.233333;
            longitude = -21;
            t_zone = 1;
            break;
        case 'Lisbon':
            latitude = 38.733333;
            longitude = 9.15;
            t_zone = 0;
            break;
        case 'Bucharest':
            latitude = 44.416667;
            longitude = -26.116667;
            t_zone = 2;
            break;
        case 'Irkutsk':
            latitude = 52.5;
            longitude = -104.333333;
            t_zone = 8;
            break;
        case 'Moscow':
            latitude = 55.75;
            longitude = -37.6;
            t_zone = 3;
            break;
        case 'St Petersburg':
            latitude = 59.933333;
            longitude = -30.3;
            t_zone = 3;
            break;
        case 'Vladivostok':
            latitude = 43.166667;
            longitude = -132;
            t_zone = 10;
            break;
        case 'Mecca':
            latitude = 21.483333;
            longitude = -39.75;
            t_zone = 3;
            break;
        case  'Aberdeen':
            latitude = 57.15;
            longitude = 2.15;
            t_zone = 0;
            break;
        case 'Edinburgh':
            latitude = 55.916667;
            longitude = 3.166667;
            t_zone = 0;
            break;
        case 'Glasgow':
            latitude = 55.833333;
            longitude = 4.25;
            t_zone = 0;
            break;
        case 'Dakar':
            latitude = 14.666667;
            longitude = 17.466667;
            t_zone = 0;
            break;
        case 'Singapore':
            latitude = 1.233333;
            longitude = -103.916667;
            t_zone = 7.5;
            break;
        case 'Johannesburg':
            latitude = -26.2;
            longitude = -28.066667;
            t_zone = 2;
            break;
        case 'CapeTown':
            latitude = -33.916667;
            longitude = -18.366667;
            t_zone = 2;
            break;
        case 'Durban':
            latitude = -29.883333;
            longitude = -30.883333;
            t_zone = 2;
            break;
        case 'Barcelona':
            latitude = 41.383333;
            longitude = -2.15;
            t_zone = 1;
            break;
        case 'Madrid':
            latitude = 40.433333;
            longitude = 3.7;
            t_zone = 1;
            break;
        case 'Paramaribo':
            latitude = 5.75;
            longitude = 55.25;
            t_zone = -3.5;
            break;
        case 'Stockholm':
            latitude = 59.283333;
            longitude = -18.05;
            t_zone = 1;
            break;
        case 'Zurich':
            latitude = 47.35;
            longitude = -8.516667;
            t_zone = 1;
            break;
        case 'Hobart':
            latitude = -42.866667;
            longitude = -147.166667;
            t_zone = 10;
            break;
        case 'Bangkok':
            latitude = 13.75;
            longitude = -100.5;
            t_zone = 7;
            break;
        case 'Ankara':
            latitude = 39.916667;
            longitude = -32.916667;
            t_zone = 2;
            break;
        case 'Odessa':
            latitude = 46.45;
            longitude = -30.8;
            t_zone = 3;
            break;
        case  'Montevideo':
            latitude = -34.883333;
            longitude = 56.166667;
            t_zone = -3;
            break;
        case 'Caracas':
            latitude = 10.466667;
            longitude = 67.033333;
            t_zone = -4;
            break;
        case 'Hanoi':
            latitude = 21 + 2 / 60;
            longitude = -(105 + 51 / 60);
            t_zone = 7;
            break;
        case 'Cardiff':
            latitude = 51 + 29 / 60;
            longitude = 3 + 11 / 60;
            t_zone = 0;
            break;
        case 'Belgrade':
            latitude = 44.866667;
            longitude = -20.533333;
            t_zone = 1;
            break;

        /* Added August 6th */
        case 'Tirana':
            latitude = 41.33;
            longitude = -19.82;
            t_zone = 1;
            break;

        case 'Nassau':
            latitude = 25.06;
            longitude = 77.33;
            t_zone = -5;
            break;

        case 'Belmopan':
            latitude = 17 + 25 / 60;
            longitude = 88 + 46 / 60;
            t_zone = -6;
            break;

        case 'Porto Novo':
            latitude = 6.5;
            longitude = -(2 + 47 / 60);
            t_zone = 1;
            break;

        case 'Thimphu':
            latitude = 27.48;
            longitude = -89.70;
            t_zone = 6;
            break;

        case 'Sarajevo':
            latitude = 43.85;
            longitude = -18.38;
            t_zone = 1;
            break;

        case 'Gaborone':
            latitude = -24.65;
            longitude = -25.91;
            t_zone = 2;
            break;

        case 'Bandar Seri Begawan':
            latitude = 4.93;
            longitude = -114.95;
            t_zone = 8;
            break;

        case 'Ouagadougou':
            latitude = 12.37;
            longitude = 1.53;
            t_zone = 0;
            break;

        case 'Bujumbura':
            latitude = -3.37;
            longitude = -29.35;
            t_zone = 2;
            break;

        case 'Yaounde':
            latitude = 3 + 51 / 60;
            longitude = -(11 + 31 / 60);
            t_zone = 1;
            break;

        case 'Praia':
            latitude = 14.93;
            longitude = 23.54;
            t_zone = -1;
            break;

        case 'Bangui':
            latitude = 4.36;
            longitude = -18.56;
            t_zone = 1;
            break;

        case 'Ndjamena':
            latitude = 12.11;
            longitude = -15.05;
            t_zone = 1;
            break;

        case 'Brazzaville':
            latitude = -4.25;
            longitude = -15.26;
            t_zone = 1;
            break;

        case 'San Jose':
            latitude = 9.93;
            longitude = 84.08;
            t_zone = -6;
            break;

        case 'Abidjan':
            latitude = 5.33;
            longitude = 4.03;
            t_zone = 0;
            break;

        case 'Zagreb':
            latitude = 45.80;
            longitude = -15.97;
            t_zone = 1;
            break;

        case  'Nicosia':
            latitude = 35.16;
            longitude = -33.38;
            t_zone = 2;
            break;

        case  'Santo Domingo':
            latitude = 18.48;
            longitude = 69.91;
            t_zone = -4;
            break;

        case 'Dili':
            latitude = -8.57;
            longitude = -125.58;
            t_zone = 9;
            break;

        case 'San Salvador':
            latitude = 13.69;
            longitude = 89.19;
            t_zone = -6;
            break;

        case 'Asmera':
            latitude = 15.33;
            longitude = -38.94;
            t_zone = 3;
            break;

        case 'Tallinn':
            latitude = 59.44;
            longitude = -24.74;
            t_zone = 2;
            break;

        case 'Addis Ababa':
            latitude = 9.03;
            longitude = -38.74;
            t_zone = 3;
            break;

        case 'Port Stanley':
            latitude = -51.70;
            longitude = 57.82;
            t_zone = -4;
            break;

        case 'Suva':
            latitude = -18.13;
            longitude = -178.43;
            t_zone = 12;
            break;

        case 'Papeete':
            latitude = -(17 + 32 / 60);
            longitude = 149 + 34 / 60;
            t_zone = -10;
            break;

        case 'Libreville':
            latitude = 0.39;
            longitude = -9.45;
            t_zone = 1;
            break;

        case 'Banjul':
            latitude = 13.45;
            longitude = 16.68;
            t_zone = 0;
            break;

        case 'Tbilisi':
            latitude = 41.72;
            longitude = -44.79;
            t_zone = 4;
            break;

        case 'Accra':
            latitude = 5.56;
            longitude = 0.20;
            t_zone = 0;
            break;

        case 'Nuuk':
            latitude = 64 + 10 / 60;
            longitude = 51 + 40 / 60;
            t_zone = -3;
            break;

        case 'Conakry':
            latitude = 9.55;
            longitude = 13.67;
            t_zone = 0;
            break;

        case 'Bissau':
            latitude = 11.87;
            longitude = 15.60;
            t_zone = 0;
            break;

        case 'Port-au-Prince':
            latitude = 18.54;
            longitude = 72.34;
            t_zone = -5;
            break;

        case 'Tegucigalpa':
            latitude = 14.09;
            longitude = 87.22;
            t_zone = -6;
            break;

        case 'Amman':
            latitude = 31.95;
            longitude = -35.93;
            t_zone = 2;
            break;

        case 'Almaty':
            latitude = 43.32;
            longitude = -76.92;
            t_zone = 6;
            break;

        case 'Pyongyang':
            latitude = 39.02;
            longitude = -125.75;
            t_zone = 9;
            break;

        case 'Seoul':
            latitude = 37.56;
            longitude = -126.99;
            t_zone = 9;
            break;

        case 'Bishkek':
            latitude = 42.87;
            longitude = -74.57;
            t_zone = 6;
            break;

        case 'Vientiane':
            latitude = 17.97;
            longitude = -102.61;
            t_zone = 7;
            break;

        case 'Riga':
            latitude = 56.97;
            longitude = -24.13;
            t_zone = 2;
            break;

        case 'Maseru':
            latitude = -29.31;
            longitude = -27.49;
            t_zone = 2;
            break;

        case 'Monrovia':
            latitude = 6.31;
            longitude = 10.80;
            t_zone = 0;
            break;

        case 'Vilnius':
            latitude = 54.70;
            longitude = -25.27;
            t_zone = 2;
            break;

        case 'Skopje':
            latitude = 42.00;
            longitude = -21.47;
            t_zone = 1;
            break;

        case 'Lilongwe':
            latitude = -13.97;
            longitude = -33.80;
            t_zone = 2;
            break;

        case 'Kuala Lumpur':
            latitude = 3.16;
            longitude = -101.71;
            t_zone = 8;
            break;

        case 'Bamako':
            latitude = 12.65;
            longitude = 7.99;
            t_zone = 0;
            break;

        case 'Nouakchott':
            latitude = 18.09;
            longitude = 15.98;
            t_zone = 0;
            break;

        case 'Kishinev':
            latitude = 47.03;
            longitude = -28.83;
            t_zone = 2;
            break;

        case 'Ulaanbaatar':
            latitude = 47.93;
            longitude = -106.91;
            t_zone = 8;
            break;

        case 'Casablanca':
            latitude = 33.60;
            longitude = 7.62;
            t_zone = 0;
            break;

        case 'Maputo':
            latitude = -25.95;
            longitude = -32.57;
            t_zone = 2;
            break;

        case 'Windhoek':
            latitude = -22.56;
            longitude = -17.09;
            t_zone = 1;
            break;

        case 'Kathmandu':
            latitude = 27.71;
            longitude = -85.31;
            t_zone = 5.75;
            break;

        case 'Managua':
            latitude = 12.15;
            longitude = 86.27;
            t_zone = -6;
            break;

        case 'Niamey':
            latitude = 13.52;
            longitude = -2.12;
            t_zone = 1;
            break;

        case 'Lagos':
            latitude = 6.45;
            longitude = -3.47;
            t_zone = 1;
            break;

        case 'San Juan':
            latitude = 18.44;
            longitude = 66.13;
            t_zone = -4;
            break;

        case 'Doha':
            latitude = 25.30;
            longitude = -51.51;
            t_zone = 3;
            break;

        case 'Kigali':
            latitude = -1.94;
            longitude = -30.06;
            t_zone = 2;
            break;

        case  'Freetown':
            latitude = 8.49;
            longitude = 13.24;
            t_zone = 0;
            break;

        case 'Bratislava':
            latitude = 48.16;
            longitude = -17.13;
            t_zone = 1;
            break;

        case 'Ljubljana':
            latitude = 46.06;
            longitude = -14.51;
            t_zone = 1;
            break;

        case 'Mogadishu':
            latitude = 2.05;
            longitude = -45.33;
            t_zone = 3;
            break;

        case  'Colombo':
            latitude = 6.93;
            longitude = -79.85;
            t_zone = 5.5;
            break;

        case 'Khartoum':
            latitude = 15.58;
            longitude = -32.52;
            t_zone = 3;
            break;

        case 'Mbabane':
            latitude = -(26 + 19 / 60);
            longitude = -(31 + 7 / 60);
            t_zone = 2;
            break;

        case 'Damascus':
            latitude = 33.4;
            longitude = -36.5;
            t_zone = 2;
            break;

        case 'Taipei':
            latitude = 25.02;
            longitude = -121.45;
            t_zone = 8;
            break;

        case 'Dushanbe':
            latitude = 38.57;
            longitude = -68.78;
            t_zone = 5;
            break;

        case 'Dar es Salaam':
            latitude = -6.82;
            longitude = -39.28;
            t_zone = 3;
            break;

        case  'Lome':
            latitude = 6.17;
            longitude = -1.35;
            t_zone = 0;
            break;

        case  'Port of Spain':
            latitude = 10 + 38 / 60;
            longitude = 61 + 31 / 60;
            t_zone = -4;
            break;

        case 'Tunis':
            latitude = 36.84;
            longitude = -10.22;
            t_zone = 1;
            break;

        case 'Ashgabat':
            latitude = 37.95;
            longitude = -58.38;
            t_zone = 5;
            break;

        case 'Kampala':
            latitude = 0.32;
            longitude = -32.58;
            t_zone = 3;
            break;

        case 'Dubai':
            latitude = 25.27;
            longitude = -55.33;
            t_zone = 4;
            break;

        case 'Tashkent':
            latitude = 41.31;
            longitude = -69.30;
            t_zone = 5;
            break;

        case 'Vatican City':
            latitude = 41.90;
            longitude = -12.46;
            t_zone = 1;
            break;

        case 'Sana':
            latitude = 15.38;
            longitude = -44.21;
            t_zone = 3;
            break;

        case 'Lusaka':
            latitude = -15.42;
            longitude = -28.29;
            t_zone = 2;
            break;

        case 'Harare':
            latitude = -17.82;
            longitude = -31.05;
            t_zone = 2;
            break;
    }
    planets();
}


/*
 Set current date and time
 */
function setup()
{
    planet_name = ["the Sun","Mercury","Venus","Mars","Jupiter","Saturn","Moon","the Pleiades","Aldebaran","the Beehive","Regulus","Spica","Antares","Pollux","Elnath","Alhena","Nunki"];
    planet_decl = new Array(16);
    planet_r_a = new Array(16);
    planet_altitude = new Array(6);
    planet_azimuth = new Array(6);
    planet_mag = new Array(6);
    planet_decl[7] = 0.42121;
    planet_r_a[7] = 0.99222;
    planet_decl[8] = 0.28827;
    planet_r_a[8] = 1.20559;
    planet_decl[9] = 0.34296;
    planet_r_a[9] = 2.27242;
    planet_decl[10] = 0.20828;
    planet_r_a[10] = 2.65595;
    planet_decl[11] = -0.19548;
    planet_r_a[11] = 3.51466;
    planet_decl[12] = -0.46164;
    planet_r_a[12] = 4.31882;
    planet_decl[13] = 0.48898;
    planet_r_a[13] = 2.03199;
    planet_decl[14] = 0.49946;
    planet_r_a[14] = 1.42549;
    planet_decl[15] = 0.28623;
    planet_r_a[15] = 1.73704;
    planet_decl[16] = -0.45873;
    planet_r_a[16] = 4.95543;
    phenomena = "...";
    phases = new Array(3);
    phase = new Array(3);
    moon_data = "...";
    new_moon = 0;
    var nowdate = new Date();
    var utc_day = nowdate.getUTCDate();
    var utc_month = nowdate.getUTCMonth() + 1;
    var utc_year = nowdate.getUTCFullYear();
    zone_comp = nowdate.getTimezoneOffset() / 1440;
    var utc_hours = nowdate.getUTCHours();
    var utc_mins = nowdate.getUTCMinutes();
    var utc_secs = nowdate.getUTCSeconds();
    utc_mins += utc_secs / 60.0;
    utc_mins = Math.floor((utc_mins + 0.5));
    if (utc_mins < 10) utc_mins = "0" + utc_mins;
    if (utc_mins > 59) utc_mins = 59;
    if (utc_hours < 10) utc_hours = "0" + utc_hours;
    if (utc_month < 10) utc_month = "0" + utc_month;
    if (utc_day < 10) utc_day = "0" + utc_day;
    if (UTdate == "now")
    {
        document.planets.date_txt.value = utc_month + "/" + utc_day + "/" + utc_year;
    }
    else
    {
        document.planets.date_txt.value = UTdate;
    }
    if (UTtime == "now")
    {
        document.planets.ut_h_m.value = utc_hours + ":" + utc_mins;
    }
    else
    {
        document.planets.ut_h_m.value = UTtime;
    }
    planets();
    dst();
}


/*
 Reset date and time to current local time
 */
function back_to_now()
{
    var nowdate = new Date();
    var utc_day = nowdate.getUTCDate();
    var utc_month = nowdate.getUTCMonth() + 1;
    var utc_year = nowdate.getUTCFullYear();
    zone_comp = nowdate.getTimezoneOffset() / 1440;
    var utc_hours = nowdate.getUTCHours();
    var utc_mins = nowdate.getUTCMinutes();
    var utc_secs = nowdate.getUTCSeconds();
    utc_mins += utc_secs / 60.0;
    utc_mins = Math.floor((utc_mins + 0.5));
    if (utc_mins < 10) utc_mins = "0" + utc_mins;
    if (utc_mins > 59) utc_mins = 59;
    if (utc_hours < 10) utc_hours = "0" + utc_hours;
    if (utc_month < 10) utc_month = "0" + utc_month;
    if (utc_day < 10) utc_day = "0" + utc_day;

    document.planets.date_txt.value = utc_month + "/" + utc_day + "/" + utc_year;
    document.planets.ut_h_m.value = utc_hours + ":" + utc_mins;

    /*
     if (UTdate == "now")
     {
     document.planets.date_txt.value = utc_month + "/" + utc_day + "/" + utc_year;
     }
     else
     {
     document.planets.date_txt.value = UTdate;
     }
     if (UTtime == "now")
     {
     document.planets.ut_h_m.value = utc_hours + ":" + utc_mins;
     }
     else
     {
     document.planets.ut_h_m.value = UTtime;
     }
     */
    planets();
}


/*
 todo: Unnecessary function
 */
function resizeDocumentTo(setw,seth)
{
    return window.resizeTo(setw,seth),window.resizeTo(setw*2-((typeof window.innerWidth == 'undefined')?document.body.clientWidth:window.innerWidth),seth*2-((typeof window.innerHeight == 'undefined')?document.body.clientHeight:window.innerHeight));
}

/*
 Determine if "x" is 0, 1, or -1
 */
function sign_of(x)
{
    return x == 0 ? 0 : (x > 0 ? 1 : -1);
}


/*
 todo: Do not know what this function does
 */
function proper_ang(big)
{
    with (Math)
    {
        var tmp = 0;
        if (big > 0)
        {
            tmp = big / 360.0;
            tmp = (tmp - floor(tmp)) * 360.0;
        }
        else
        {
            tmp = ceil(abs(big / 360.0));
            tmp = big + tmp * 360.0;
        }
    }
    return tmp;
}

/*
 todo: Do not know what this function does
 */
function proper_ang_rad(big)
{
    with (Math)
    {
        var tmp = 0;
        if (big > 0)
        {
            tmp = big / 2 / PI;
            tmp = (tmp - floor(tmp)) * 2 * PI;
        }
        else
        {
            tmp = ceil(abs(big / 2 / PI));
            tmp = big + tmp * 2 * PI;
        }
    }
    return tmp;
}

/*
 todo: Do not know what this function does
 */
function range_1(num)
{
    var temp = num;
    if (num > 1) temp = num - 1;
    if (num < 0) temp = num + 1;
    return temp;
}


/*
 fixme: Unecessary function
 */
//function range_24(big)
//{
//    with (Math)
//    {
//        var tmp = 0;
//        if (big > 0)
//        {
//            tmp = big / 24.0;
//            tmp = (tmp - floor(tmp)) * 24.0;
//        }
//        else
//        {
//            tmp = ceil(abs(big / 24.0));
//            tmp = big + tmp * 24.0;
//        }
//    }
//    return tmp;
//}


/*
 Math functions to round to nearest whole number
 */
function round_10(num)
{
    return Math.floor((num + 0.05) * 10) / 10;
}

function round_100(num)
{
    return Math.floor((num + 0.005) * 100) / 100;
}

function round_1000(num)
{
    return Math.floor((num + 0.0005) * 1000) / 1000;
}

function hundred_miles(num)
{
    return Math.floor((num / 100) + 0.5) * 100;
}


/*
 Validate date and time
 */
function isValidDate(dateStr)
{
    if (IsValidTime(document.planets.ut_h_m.value) == true)
    {
        var datePat = /^(\d{1,2})(\/|-)(\d{1,2})\2(\d{4})$/;
        var matchArray = dateStr.match(datePat);
        if (matchArray == null) {
            alert("Date is not in a valid format.");
            return false;
        }
        var month = matchArray[1];
        var day = matchArray[3];
        var year = matchArray[4];
        if (month < 1 || month > 12) {
            alert("Month must be between 1 and 12.");
            return false;
        }
        if (year < 1600 || year > 2400) {
            alert("Year must be between 1600 and 2400.");
            return false;
        }
        if (day < 1 || day > 31) {
            alert("Day must be between 1 and 31.");
            return false;
        }
        if ((month == 4 || month == 6 || month == 9 || month == 11) && day == 31) {
            alert("Month " + month + " doesn't have 31 days!");
            return false
        }
        if (month == 2) {
            var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
            if (day > 29 || (day == 29 && !isleap))
            {
                alert("February " + year + " doesn't have " + day + " days!");
                return false;
            }
        }
        if (month < 10 && month.length == 1) month = "0" + month;
        if (day < 10 && day.length == 1) day = "0" + day;
        document.planets.date_txt.value = month + "/" + day + "/" + year;
        var dt_str = document.planets.date_txt.value;
        if ((dt_str.substring(2,3) != "/") || (dt_str.substring(5,6) != "/"))
        {
            alert ("Date is not in a valid format.");
            return false;
        }
        planets();
        return true;
    }
    else
    {
        return false;
    }
}

/*
 Error messages for invalid times
 */
function IsValidTime(timeStr)
{
    var timePat = /^(\d{1,2}):(\d{2})(:(\d{2}))?(\s?(AM|am|PM|pm))?$/;
    var matchArray = timeStr.match(timePat);
    if (matchArray == null)
    {
        alert("Time is not in a valid format.");
        return false;
    }
    var hour = matchArray[1];
    var minute = matchArray[2];
    if (hour < 0 || hour > 23)
    {
        alert("Hour must be between 0 and 23.");
        return false;
    }
    if (minute < 0 || minute > 59)
    {
        alert ("Minute must be between 0 and 59.");
        return false;
    }
    if (hour < 10 && hour.length == 1) hour = "0" + hour;
    if (minute < 10 && minute.length == 1) minute = "0" + minute;
    document.planets.ut_h_m.value = hour + ":" + minute;
    var tm_str = document.planets.ut_h_m.value;
    if ((tm_str.substring(2,3) != ":") && (dt_str.length != 5))
    {
        alert ("Time is not in a valid format.");
        return false;
    }
    return true;
}


/*
 Manually change time from almanac
 */
function time_change(tmp)
{
    if (isValidDate(document.planets.date_txt.value) == true)
    {
        var jd_temp, zz, ff, alpha, aa, bb, cc, dd, ee;
        var calendar_day, calendar_month, calendar_year;
        var int_day, hours, minutes;

        var tm_as_str, ut_hrs, ut_mns, part_day;

        var jd = julian_date();

        tm_as_str = document.planets.ut_h_m.value;
        ut_hrs = eval(tm_as_str.substring(0,2));
        ut_mns = eval(tm_as_str.substring(3,5));
        part_day = ut_hrs / 24.0 + ut_mns / 1440.0;

        with (Math) {

            jd_temp = jd + part_day + tmp / 24.0 + 0.5;

            zz = floor(jd_temp);
            ff = jd_temp - zz;
            alpha = floor((zz - 1867216.25) / 36524.25);
            aa = zz + 1 + alpha - floor(alpha / 4);
            bb = aa + 1524;
            cc = floor((bb - 122.1) / 365.25);
            dd = floor(365.25 * cc);
            ee = floor((bb - dd) / 30.6001);
            calendar_day = bb - dd - floor(30.6001 * ee) + ff;
            calendar_month = ee;
            if (ee < 13.5) calendar_month = ee - 1;
            if (ee > 13.5) calendar_month = ee - 13;
            calendar_year = cc;
            if (calendar_month > 2.5) calendar_year = cc - 4716;
            if (calendar_month < 2.5) calendar_year = cc - 4715;
            int_day = floor(calendar_day);
            hours = (calendar_day - int_day) * 24;
            minutes = floor((hours - floor(hours)) * 60 + 0.5);
            hours = floor(hours);
            if (minutes > 59)
            {minutes = 0; hours = hours + 1;}
            if (calendar_month < 10) calendar_month = "0" + calendar_month;
            if (int_day < 10) int_day = "0" + int_day;
            if (hours < 10) hours = "0" + floor(hours);
            if (minutes < 10) minutes = "0" + minutes;
        }
        document.planets.date_txt.value = calendar_month + "/" + int_day + "/" + calendar_year;
        document.planets.ut_h_m.value = hours + ":" + minutes;
        planets();
        return true;
    }
    else
    {
        return false;
    }
}


/*
 Calculate t-zone and compass direction of location
 */
function zone_date_time()
{
    var jd_temp, zz, ff, alpha, aa, bb, cc, dd, ee;
    var calendar_day, calendar_month, calendar_year;
    var int_day, hours, minutes, daylight_saving;
    var d_of_w, dow_str, x, y, lat_long = "", dst = 0;
    var tm_as_str, ut_hrs, ut_mns, part_day;

    var jd = julian_date();

    tm_as_str = document.planets.ut_h_m.value;
    ut_hrs = eval(tm_as_str.substring(0,2));
    ut_mns = eval(tm_as_str.substring(3,5));
    part_day = ut_hrs / 24.0 + ut_mns / 1440.0;

    with (Math) {

        x = floor(abs(latitude));
        y = floor((abs(latitude) - x) * 60 + 0.5);
        if (x < 10) lat_long += "0";
        lat_long = lat_long + x + "\u00B0" + " ";
        if (y < 10) lat_long += "0";
        lat_long = lat_long + y + "\'";
        if (latitude >= 0)
        {
            lat_long += " N  ";
        }
        else
        {
            lat_long += " S  ";
        }

        x = floor(abs(longitude));
        y = floor((abs(longitude) - x) * 60 + 0.5);
        if (x < 100) lat_long += "0";
        if (x < 10) lat_long += "0";
        lat_long = lat_long + x + "\u00B0" + " ";
        if (y < 10) lat_long += "0";
        lat_long = lat_long + y + "\'";
        if (longitude >= 0)
        {
            lat_long += " W";
        }
        else
        {
            lat_long += " E";
        }

        document.planets.lat_long.value = lat_long;

        daylight_saving = document.planets.d_s_t.checked;
        if (daylight_saving == true)
        {
            dst = 1;
            zone = t_zone + dst;
        }
        else
        {
            zone = t_zone;
        }

        document.planets.timezone.value = zone;

        jd_temp = jd + part_day + zone / 24 + 0.5;

        d_of_w = floor(jd_temp + 1.0) % 7;

        if (d_of_w == 0) dow_str = "Sun.";
        if (d_of_w == 1) dow_str = "Mon.";
        if (d_of_w == 2) dow_str = "Tue.";
        if (d_of_w == 3) dow_str = "Wed.";
        if (d_of_w == 4) dow_str = "Thu.";
        if (d_of_w == 5) dow_str = "Fri.";
        if (d_of_w == 6) dow_str = "Sat.";

        zz = floor(jd_temp);
        ff = jd_temp - zz;
        alpha = floor((zz - 1867216.25) / 36524.25);
        aa = zz + 1 + alpha - floor(alpha / 4);
        bb = aa + 1524;
        cc = floor((bb - 122.1) / 365.25);
        dd = floor(365.25 * cc);
        ee = floor((bb - dd) / 30.6001);
        calendar_day = bb - dd - floor(30.6001 * ee) + ff;
        calendar_month = ee;
        if (ee < 13.5) calendar_month = ee - 1;
        if (ee > 13.5) calendar_month = ee - 13;
        calendar_year = cc;
        if (calendar_month > 2.5) calendar_year = cc - 4716;
        if (calendar_month < 2.5) calendar_year = cc - 4715;
        int_day = floor(calendar_day);
        hours = (calendar_day - int_day) * 24;
        minutes = floor((hours - floor(hours)) * 60 + 0.5);
        hours = floor(hours);
        if (minutes > 59)
        {minutes = 0; hours = hours + 1;}
        if (calendar_month < 10) calendar_month = "0" + calendar_month;
        if (int_day < 10) int_day = "0" + int_day;

    }
    document.planets.date_zone.value = dow_str + " " + calendar_month + "/" + int_day + "/" + calendar_year + " " + am_pm(hours + minutes / 60.0);
}


function dst () {

    var arr = [];
    for (var i = 0; i < 365; i++) {
        var d = new Date();
        d.setDate(i);
        newoffset1 = d.getTimezoneOffset();
        arr.push(newoffset1);
    }
    DST = Math.min.apply(null, arr);
    nonDST = Math.max.apply(null, arr);


    var dstDate = new Date();
    var newOffset = dstDate.getTimezoneOffset();
    if (DST == newOffset) {
        document.getElementById("d_s_t").checked = true;
    }
}
/*
 Convert UT to am/pm
 */
function am_pm(h)
{
    with (Math) {
        var hours, minutes, ampm, result="";
        hours = floor(h);
        minutes = floor((h - hours) * 60 + 0.5);
        if (minutes == 60)
        {
            hours += 1;
            minutes = 0;
        }

        if (hours > 11)
        {
            hours -= 12;
            ampm = " pm";
        }
        else
        {ampm = " am";}
        if (hours < 1) hours = 12;

        if (hours < 10)
        {result = result + "0" + floor(hours);}
        else
        {result = result + floor(hours);}
        result = result + ":";

        if (minutes < 10)
        {result = result + "0" + minutes;}
        else
        {result = result + minutes;}
    }
    result = result + ampm;
    return result;
}


/*
 Find julian Moon date
 */
function julian_date()
{
    var dt_as_str, mm, dd, yy;
    var a, b;

    dt_as_str = document.planets.date_txt.value;

    mm = eval(dt_as_str.substring(0,2));
    dd = eval(dt_as_str.substring(3,5));
    yy = eval(dt_as_str.substring(6,10));

    with (Math) {
        var yyy=yy;
        var mmm=mm;
        if (mm < 3)
        {
            yyy = yy - 1;
            mmm = mm + 12;
        }
        a = floor(yyy/100);
        b = 2 - a + floor(a/4);

        return floor(365.25*yyy) + floor(30.6001*(mmm+1)) + dd + 1720994.5 + b;
    }
}


/*
 find Julian t-zone
 */
function julian_date_zoned()
{
    var dt_as_str, mm, dd, yy;
    var a, b;

    dt_as_str = document.planets.date_zone.value;

    mm = eval(dt_as_str.substring(5,7));
    dd = eval(dt_as_str.substring(8,10));
    yy = eval(dt_as_str.substring(11,15));

    with (Math) {
        var yyy=yy;
        var mmm=mm;
        if (mm < 3)
        {
            yyy = yy - 1;
            mmm = mm + 12;
        }
        a = floor(yyy/100);
        b = 2 - a + floor(a/4);

        return floor(365.25*yyy) + floor(30.6001*(mmm+1)) + dd + 1720994.5 + b;
    }
}


/*
 Calculate moon age, phase, and other facts
 */
function moon_facts()
{
    with (Math) {

        var dt_as_str, mm,  yy,  i, tmp, k, t;
        var M, M1, F, NM_FM, FQ_LQ, RAD = 180 / PI;
        var jd_temp, d_of_w, dow_str, zz, ff, alpha;
        var aa, bb, cc, dd, ee, calendar_day, month;
        var calendar_month, int_day, hours, minutes;

        moon_data = "";
        phase[0] = "New Moon ";
        phase[1] = "Moon at first quarter ";
        phase[2] = "Full Moon ";
        phase[3] = "Moon at last quarter ";

        dt_as_str = document.planets.date_txt.value;
        yy = eval(dt_as_str.substring(6,10));
        mm = eval(dt_as_str.substring(0,2));
        dd = eval(dt_as_str.substring(3,5));

        tmp = floor(((yy + (mm - 1) / 12 + dd / 365.25) - 1900.0) * 12.3685);

        for (i=0; i<4; i++)
        {
            k = tmp + i * 0.25;
            t = k / 1236.85;
            M = proper_ang(359.2242 + 29.10535608 * k - 0.0000333 * pow(t,2) - 0.00000347 * pow(t,3)) / RAD;
            M1 = proper_ang(306.0253 + 385.81691806 * k + 0.0107306 * pow(t,2) + 0.00001236 * pow(t,3)) / RAD;
            F = proper_ang(21.2964 + 390.67050646 * k - 0.0016528 * pow(t,2) - 0.00000239 * pow(t,3)) / RAD;
            NM_FM = (0.1734 - 0.000393 * t) * sin(M) + 0.0021 * sin(2 * M) - 0.4068 * sin(M1) + 0.0161 * sin(2 * M1) - 0.0004 * sin(3 * M1) + 0.0104 * sin(2 * F) - 0.0051 * sin(M + M1) - 0.0074 * sin(M - M1) + 0.0004 * sin(2 * F + M) - 0.0004 * sin(2 * F - M) - 0.0006 * sin(2 * F + M1) + 0.0010 * sin(2 * F - M1) + 0.0005 * sin(M + 2 * M1);
            FQ_LQ = (0.1721 - 0.0004 * t) * sin(M) + 0.0021 * sin(2 * M) - 0.628 * sin(M1) + 0.0089 * sin(2 * M1) - 0.0004 * sin(3 * M1) + 0.0079 * sin(2 * F) - 0.0119 * sin(M + M1) - 0.0047 * sin(M - M1) + 0.0003 * sin(2 * F + M) - 0.0004 * sin(2 * F - M) - 0.0006 * sin(2 * F + M1) + 0.0021 * sin(2 * F - M1) + 0.0003 * sin(M + 2 * M1) + 0.0004 * sin(M - 2 * M1) - 0.0003 * sin(2 * M + M1);
            phases[i] = 2415020.75933 + 29.53058868 * k + 0.0001178 * pow(t,2) - 0.000000155 * pow(t,3) + 0.00033 * sin(2.907 + 2.319 * t - 1.6e-4 * pow(t,2));
            if (i == 0 || i == 2) phases[i] += NM_FM;
            if (i == 1) phases[i] = phases[i] + FQ_LQ + 0.0028 - 0.0004 * cos(M) + 0.0003 * cos(M1);
            if (i == 3) phases[i] = phases[i] + FQ_LQ - 0.0028 + 0.0004 * cos(M) - 0.0003 * cos(M1);
        }
        new_moon = phases[0];

        for (i=0; i<4; i++)
        {
            jd_temp = phases[i] + 0.5;

            d_of_w = floor(jd_temp + 1.0) % 7;
            if (d_of_w == 0) dow_str = "Sun.";
            if (d_of_w == 1) dow_str = "Mon.";
            if (d_of_w == 2) dow_str = "Tue.";
            if (d_of_w == 3) dow_str = "Wed.";
            if (d_of_w == 4) dow_str = "Thu.";
            if (d_of_w == 5) dow_str = "Fri.";
            if (d_of_w == 6) dow_str = "Sat.";

            zz = floor(jd_temp);
            ff = jd_temp - zz;
            alpha = floor((zz - 1867216.25) / 36524.25);
            aa = zz + 1 + alpha - floor(alpha / 4);
            bb = aa + 1524;
            cc = floor((bb - 122.1) / 365.25);
            dd = floor(365.25 * cc);
            ee = floor((bb - dd) / 30.6001);
            calendar_day = bb - dd - floor(30.6001 * ee) + ff;
            calendar_month = ee;
            if (ee < 13.5) calendar_month = ee - 1;
            if (ee > 13.5) calendar_month = ee - 13;
            calendar_year = cc;
            if (calendar_month > 2.5) calendar_year = cc - 4716;
            if (calendar_month < 2.5) calendar_year = cc - 4715;
            int_day = floor(calendar_day);
            hours = (calendar_day - int_day) * 24;
            minutes = floor((hours - floor(hours)) * 60 + 0.5);
            hours = floor(hours);
            if (minutes > 59)
            {minutes = 0; hours = hours + 1;}
            if (hours < 10) hours = "0" + hours;
            if (minutes < 10) minutes = "0" + minutes;
            if (int_day < 10) int_day = "0" + int_day;

            if (calendar_month == 1) month = "Jan.";
            if (calendar_month == 2) month = "Feb.";
            if (calendar_month == 3) month = "Mar.";
            if (calendar_month == 4) month = "Apr.";
            if (calendar_month == 5) month = "May ";
            if (calendar_month == 6) month = "Jun.";
            if (calendar_month == 7) month = "Jul.";
            if (calendar_month == 8) month = "Aug.";
            if (calendar_month == 9) month = "Sep.";
            if (calendar_month == 10) month = "Oct.";
            if (calendar_month == 11) month = "Nov.";
            if (calendar_month == 12) month = "Dec.";

            moon_data += dow_str + " " + month + " " + int_day + ", " + calendar_year + "   " + phase[i] + "(" + hours + ":" + minutes + " UT)";

            if (i == 1 || i == 3)
            {
                moon_data += "\n"
            }
            else
            {
                moon_data += "     ";
            }

        }

    }
}

/*
 Calculate planet location's based on user location, along with values for R.A., Dec, etc.
 */
function planets()
{
    with (Math) {

        var RAD = 180 / PI;

        document.planets.size6.value = "";
        document.planets.size7.value = "";
        document.planets.phase7.value = "";
        document.planets.phase6.value = "";
        document.planets.dawn.value = "";
        document.planets.dusk.value = "";
        document.planets.tr0.value = "...";
        document.planets.tr1.value = "...";
        document.planets.tr2.value = "...";
        document.planets.tr3.value = "...";
        document.planets.tr4.value = "...";
        document.planets.tr5.value = "...";
        document.planets.tr6.value = "...";

        var planet_dec = new Array(9);
        var planet_ra = new Array(9);
        var planet_dist = new Array(9);
        var planet_phase = new Array(9);
        var planet_size = new Array(9);
        var planet_alt = new Array(9);
        var planet_az = new Array(9);

        var ang_at_1au = [1,6.68,16.82,9.36,196.94,166.6];

        var planet_a = new Array(5);
        var planet_l = new Array(5);
        var planet_i = new Array(5);
        var planet_e = new Array(5);
        var planet_n = new Array(5);
        var planet_m = new Array(5);
        var planet_r = new Array(5);

        var jd, t, l, m, e, c, sl, r, R, Rm, u, p, q, v, z, a, b, ee, oe, t0, gt;
        var g0, x, y, i, indx, count, hl, ha, eg, et, dr, en, de, ra, la, lo, height;
        var az, al, size, il, dist, rho_sin_lat, rho_cos_lat, rise_str, set_str;
        var dawn, dusk, gt_dawn, gt_dusk, dawn_planets, dusk_planets, morn, eve;
        var sunrise_set, moonrise_set, twilight_begin_end, twilight_start, twilight_end;
        var sat_lat, phase_angle, dt_as_str, tm_as_str, ut_hrs, ut_mns, part_day, dt;
        var moon_days, Days, age, N, M_sun, Ec, lambdasun, P0, N0, rise, set, tr, h0;
        var i_m, e_m, l_m, M_m, N_m, Ev, Ae, A3, A4, lambda_moon, beta_moon, phase;
        var transit, lambda_transit, beta_transit,  ra_transit;
        var ra_np, d_beta, d_lambda, lambda_moon_D, beta_moon_D, yy;

        la = latitude / RAD;
        lo = longitude / RAD;
        height = 10.0;

        tm_as_str = document.planets.ut_h_m.value;
        ut_hrs = eval(tm_as_str.substring(0,2));
        ut_mns = eval(tm_as_str.substring(3,5));
        dt_as_str = document.planets.date_txt.value;
        yy = eval(dt_as_str.substring(6,10));


        part_day = ut_hrs / 24.0 + ut_mns / 1440.0;

        jd = julian_date() + part_day;

        document.planets.phase8.value = round_1000(jd);

        y = floor(yy / 50 + 0.5) * 50;
        if (y == 1600) dt = 2;
        if (y == 1650) dt = 0.8;
        if (y == 1700) dt = 0.1;
        if (y == 1750) dt = 0.2;
        if (y == 1800) dt = 0.2;
        if (y == 1850) dt = 0.1;
        if (y == 1900) dt = 0;
        if (y == 1950) dt = 0.5;
        if (y == 2000) dt = 1.1;
        if (y == 2050) dt = 3;
        if (y == 2100) dt = 5;
        if (y == 2150) dt = 6;
        if (y == 2200) dt = 8;
        if (y == 2250) dt = 11;
        if (y == 2300) dt = 13;
        if (y == 2350) dt = 16;
        if (y == 2400) dt = 19;
        dt = dt / 1440;

        jd += dt;

        zone_date_time();

        moon_facts();

        t=(jd-2415020.0)/36525;
        oe=.409319747-2.271109689e-4*t-2.8623e-8*pow(t,2)+8.779e-9*pow(t,3);
        t0=(julian_date()-2415020.0)/36525;
        g0=.276919398+100.0021359*t0+1.075e-6*pow(t0,2);
        g0=(g0-floor(g0))*2*PI;
        gt=proper_ang_rad(g0+part_day*6.30038808);

        l=proper_ang_rad(4.88162797+628.331951*t+5.27962099e-6*pow(t,2));
        m=proper_ang_rad(6.2565835+628.3019457*t-2.617993878e-6*pow(t,2)-5.759586531e-8*pow(t,3));
        e=.01675104-4.18e-5*t-1.26e-7*pow(t,2);
        c=proper_ang_rad(3.3500896e-2-8.358382e-5*t-2.44e-7*pow(t,2))*sin(m)+(3.50706e-4-1.7e-6*t)*sin(2*m)+5.1138e-6*sin(3*m);
        sl=proper_ang_rad(l+c);
        R=1.0000002*(1-pow(e,2))/(1+e*cos(m+c));

        de = asin(sin(oe) * sin(sl));
        y = sin(sl) * cos(oe);
        x = cos(sl);
        ra = proper_ang_rad(atan2(y,x));
        dist = R;
        size = 31.9877 / dist;

        ha=proper_ang_rad(gt-lo-ra);
        al=asin(sin(la)*sin(de)+cos(la)*cos(de)*cos(ha));
        az=acos((sin(de)-sin(la)*sin(al))/(cos(la)*cos(al)));
        if (sin(ha)>=0) az=2*PI-az;

        planet_decl[0] = de;
        planet_r_a[0] = ra;
        planet_mag[0] = -26.7;

        planet_dec[8] = "";
        planet_ra[8] = "";
        planet_dist[8] = "";
        planet_size[8] = "";
        planet_alt[8] = "";
        planet_az[8] = "";

        y = sin(-0.8333/RAD) - sin(la) * sin(de);
        x = cos(la) * cos(de);
        if (y/x >= 1)
        {
            //rise_str = "No rise";
            //set_str = "No rise";
            h0 = 0;
        }
        if (y/x <= -1)
        {
            //rise_str = "No set";
            //set_str = "No set";
            h0 = 0;
        }
        if (abs(y/x) < 1)
        {
            h0 = acos(y/x) * RAD;
            tr = range_1((ra * RAD + lo * RAD - g0 * RAD) / 360);
            document.planets.tr0.value = am_pm(range_1(tr + zone / 24 - dt) * 24);
            document.planets.tr0_2.value = am_pm(range_1(tr + zone / 24 - dt) * 24);


        }
        y = sin(-18/RAD) - sin(la) * sin(de);
        x = cos(la) * cos(de);
        if (y/x >= 1)
        {
            twilight_start = "No twilight";
            twilight_end = "No twilight";
            h0 = 0;
        }
        if (y/x <= -1)
        {
            twilight_start = "All night";
            twilight_end = "All night";
            h0 = 0;
        }
        if (abs(y/x) < 1)
        {
            h0 = acos(y/x) * RAD;
            tr = range_1((ra * RAD + lo * RAD - g0 * RAD) / 360);
            twilight_start = am_pm(range_1(tr - h0 / 360 + zone / 24 - dt) * 24);
            twilight_end = am_pm(range_1(tr + h0 / 360 + zone / 24 - dt) * 24);
        }

        al = al * RAD;
        az = az * RAD;
        if (al >= 0)
        {
            al += 1.2 / (al + 2);
        }
        else
        {
            al += 1.2 / (abs(al) + 2);
        }

        planet_altitude[0] = al;
        planet_azimuth[0] = az;

        de = de * RAD;
        ra = ra * RAD / 15;

        x = round_10(abs(de));
        if (de < 0)
        {
            planet_dec[8] += "-";
        }
        else
        {
            planet_dec[8] += "+";
        }
        if (x < 10) planet_dec[8] += "0";
        planet_dec[8] += x;
        if (x == floor(x)) planet_dec[8] += ".0";
        planet_dec[8] += "\u00B0";

        x = floor(ra);
        y = floor((ra - x) * 60 + 0.5);
        if (y == 60)
        {
            x += 1;
            y = 0;
        }
        if (x < 10) planet_ra[8] += "0";
        planet_ra[8] += x + "h ";
        if (y < 10) planet_ra[8] += "0";
        planet_ra[8] += y + "m";

        dist = round_100(dist);
        if (dist < 10)
            planet_dist[8] += "0";
        planet_dist[8] += dist;

        size = round_10(size);
        planet_size[8] += size;
        if (size == floor(size)) planet_size[8] += ".0";
        planet_size[8] += "\'";

        sunrise_set = sun_rise_set(la,lo,zone);
        rise_str = sunrise_set.substring(0,8);
        set_str = sunrise_set.substring(11,19);
        planet_alt[8] = rise_str;
        planet_az[8] = set_str;

        /*
         planet_alt[8] = rise_str;
         planet_az[8] = set_str;
         sunrise_set = rise_str + " / " + set_str;
         */

        moon_days = 29.53058868;
        Days = jd - 2444238.5;
        N = proper_ang(Days / 1.01456167);
        M_sun = proper_ang(N - 3.762863);
        Ec = 1.91574168 * sin(M_sun / RAD);
        lambdasun = proper_ang(N + Ec + 278.83354);
        l0 = 64.975464;
        P0 = 349.383063;
        N0 = 151.950429;
        i_m = 5.145396;
        e_m = 0.0549;
        l_m = proper_ang(13.1763966 * Days + l0);
        M_m = proper_ang(l_m - 0.111404 * Days - P0);
        N_m = proper_ang(N0 - 0.0529539 * Days);
        Ev = 1.2739 * sin((2 * (l_m - lambdasun) - M_m) / RAD);
        Ae = 0.1858 * sin(M_sun / RAD);
        A3 = 0.37 * sin(M_sun / RAD);
        M_m += Ev - Ae - A3;
        Ec = 6.2886 * sin(M_m / RAD);
        dist = hundred_miles(238855.7 * (1 - pow(e_m,2)) / (1 + e_m * cos((M_m + Ec) / RAD)));
        A4 = 0.214 * sin(2 * M_m / RAD);
        l_m += Ev + Ec - Ae + A4;
        l_m += 0.6583 * sin(2 * (l_m - lambdasun) / RAD);
        N_m -= 0.16 * sin(M_sun / RAD);

        y = sin((l_m - N_m) / RAD) * cos(i_m / RAD);
        x = cos((l_m - N_m) / RAD);
        lambda_moon = proper_ang_rad(atan2(y,x) + N_m / RAD);
        beta_moon = asin(sin((l_m - N_m) / RAD) * sin(i_m / RAD));
        d_beta = 8.727e-4 * cos((l_m - N_m) / RAD);
        d_lambda = 9.599e-3 + 1.047e-3 * cos(M_m / RAD);

        de = asin(sin(beta_moon) * cos(oe) + cos(beta_moon) * sin(oe) * sin(lambda_moon));
        y = sin(lambda_moon) * cos(oe) - tan(beta_moon) * sin(oe);
        x = cos(lambda_moon);
        ra = proper_ang_rad(atan2(y,x));

        ra_np = ra;

        size = 2160 / dist * RAD * 60;

        x = proper_ang(l_m - lambdasun);
        phase = 50.0 * (1.0 - cos(x / RAD));

        age = jd - new_moon;
        if (age > moon_days) age -= moon_days;
        if (age < 0) age += moon_days;

        /*
         age = (x / 360 * moon_days);
         */
        Rm = R - dist * cos(x / RAD) / 92955800;
        phase_angle = acos((pow(Rm,2) + pow((dist/92955800),2) - pow(R,2)) / (2 * Rm * dist/92955800)) * RAD / 100;
        planet_mag[6] = round_10(0.21 + 5 * log(Rm * dist / 92955800)/log(10) + 3.05 * (phase_angle) - 1.02 * pow(phase_angle,2) + 1.05 * pow(phase_angle,3));
        if (planet_mag[6] == floor(planet_mag[6])) planet_mag[6] += ".0";

        /*
         thisImage = floor(age / moon_days * 28);
         */

        thisImage = floor(age / moon_days * 27 + 0.5);
        document.myPicture.src = myImage[thisImage];
        age = round_10(age);

        x = atan(0.996647 * tan(la));
        rho_sin_lat = 0.996647 * sin(x) + height * sin(la) / 6378140;
        rho_cos_lat = cos(x) + height * cos(la) / 6378140;
        r = dist / 3963.2;
        ha = proper_ang_rad(gt - lo - ra);
        x = atan(rho_cos_lat * sin(ha) / (r * cos(de) - rho_cos_lat * cos(ha)));
        ra -= x;
        y = ha;
        ha += x;
        de = atan(cos(ha) * (r * sin(de) - rho_sin_lat) / (r * cos(de) * cos(y) - rho_cos_lat));
        ha = proper_ang_rad(gt - lo - ra);
        al = asin(sin(de) * sin(la) + cos(de) * cos(la) * cos(ha));
        az = acos((sin(de) - sin(la) * sin(al)) / (cos(la) * cos(al)));
        if (sin(ha) >= 0) az = 2 * PI - az;

        planet_decl[6] = de;
        planet_r_a[6] = ra;

        planet_dec[9] = "";
        planet_ra[9] = "";
        planet_dist[9] = "";
        planet_dist[6] = "";
        planet_phase[9] = "";
        planet_size[9] = "";
        planet_alt[9] = "";
        planet_az[9] = "";

        lambda_moon_D = lambda_moon - d_lambda * part_day * 24;
        beta_moon_D = beta_moon - d_beta * part_day * 24;

        tr = range_1((ra_np * RAD + lo * RAD - g0 * RAD) / 360);
        transit = tr * 24;
        lambda_transit = lambda_moon_D + transit * d_lambda;
        beta_transit = beta_moon_D + transit * d_beta;

        y = sin(lambda_transit) * cos(oe) - tan(beta_transit) * sin(oe);
        x = cos(lambda_transit);
        ra_transit = proper_ang_rad(atan2(y,x));
        tr = range_1((ra_transit * RAD + lo * RAD - g0 * RAD) / 360);
        document.planets.tr6.value = am_pm(range_1(tr + zone / 24 - dt) * 24);
        document.planets.tr6_2.value = am_pm(range_1(tr + zone / 24 - dt) * 24);


        al = al * RAD;
        az = az * RAD;
        if (al >= 0)
        {
            al += 1.2 / (al + 2);
        }
        else
        {
            al += 1.2 / (abs(al) + 2);
        }

        planet_altitude[6] = al;
        planet_azimuth[6] = az;

        de = de * RAD;
        ra = ra * RAD / 15;

        x = round_10(abs(de));
        if (de < 0)
        {
            planet_dec[9] += "-";
        }
        else
        {
            planet_dec[9] += "+";
        }
        if (x < 10) planet_dec[9] += "0";
        planet_dec[9] += x;
        if (x == floor(x)) planet_dec[9] += ".0";
        planet_dec[9] += "\u00B0";

        x = floor(ra);
        y = floor((ra - x) * 60 + 0.5);
        if (y == 60)
        {
            x += 1;
            y = 0;
        }
        if (x < 10) planet_ra[9] += "0";
        planet_ra[9] += x + "h ";
        if (y < 10) planet_ra[9] += "0";
        planet_ra[9] += y + "m";

        if (age < 10)
            planet_dist[6] += "0";
        planet_dist[6] += age;
        if (age == floor(age)) planet_dist[6] += ".0";
        planet_dist[6] += " days";

        planet_dist[9] += dist;

        size = round_10(size);
        planet_size[9] += size;
        if (size == floor(size)) planet_size[9] += ".0";
        planet_size[9] += "\'";

        phase = floor(phase + 0.5);
        planet_phase[9] += phase + "%";

        moonrise_set = moon_rise_set(la,lo,zone);
        rise_str = moonrise_set.substring(0,8);
        set_str = moonrise_set.substring(11,19);
        planet_alt[9] = rise_str;
        planet_az[9] = set_str;


        if (twilight_start == "All night")
        {
            twilight_begin_end = "twilight all night";
        }
        else if (twilight_start == "No twilight")
        {
            twilight_begin_end = "Sky dark for 24h";
        }
        else
        {
            twilight_begin_end = twilight_start + " / " + twilight_end;
        }

        u=t/5+.1;
        p=proper_ang_rad(4.14473024+52.96910393*t);
        q=proper_ang_rad(4.64111846+21.32991139*t);

        v=5*q-2*p;
        z=proper_ang_rad(q-p);
        if (v >= 0)
        {
            v=proper_ang_rad(v);
        }
        else
        {
            v=proper_ang_rad(abs(v))+2*PI;
        }

        planet_a[1]=0.3870986;
        planet_l[1]=proper_ang_rad(3.109811569+2608.814681*t+5.2552e-6*pow(t,2));
        planet_e[1]=.20561421+2.046e-5*t-3e-8*pow(t,2);
        planet_i[1]=.12222333+3.247708672e-5*t-3.194e-7*pow(t,2);
        planet_n[1]=.822851951+.020685787*t+3.035127569e-6*pow(t,2);
        planet_m[1]=proper_ang_rad(1.785111938+2608.787533*t+1.22e-7*pow(t,2));
        planet_a[2]=0.7233316;
        planet_l[2]=proper_ang_rad(5.982413642+1021.352924*t+5.4053e-6*pow(t,2));
        planet_e[2]=.00682069-4.774e-5*t+9.1e-8*pow(t,2);
        planet_i[2]=.059230034+1.75545e-5*t-1.7e-8*pow(t,2);
        planet_n[2]=1.322604346+.0157053*t+7.15585e-6*pow(t,2);
        planet_m[2]=proper_ang_rad(3.710626189+1021.328349*t+2.2445e-5*pow(t,2));
        planet_a[3]=1.5236883;
        planet_l[3]=proper_ang_rad(5.126683614+334.0856111*t+5.422738e-6*pow(t,2));
        planet_e[3]=.0933129+9.2064e-5*t-7.7e-8*pow(t,2);
        planet_i[3]=.032294403-1.178097e-5*t+2.199e-7*pow(t,2);
        planet_n[3]=.851484043+.013456343*t-2.44e-8*pow(t,2)-9.3026e-8*pow(t,3);
        planet_m[3]=proper_ang_rad(5.576660841+334.0534837*t+3.159e-6*pow(t,2));
        planet_l[4]=proper_ang_rad(4.154743317+52.99346674*t+5.841617e-6*pow(t,2)-2.8798e-8*pow(t,3));
        a=.0058*sin(v)-1.1e-3*u*cos(v)+3.2e-4*sin(2*z)-5.8e-4*cos(z)*sin(q)-6.2e-4*sin(z)*cos(q)+2.4e-4*sin(z);
        b=-3.5e-4*cos(v)+5.9e-4*cos(z)*sin(q)+6.6e-4*sin(z)*cos(q);
        ee=3.6e-4*sin(v)-6.8e-4*sin(z)*sin(q);
        planet_e[4]=.04833475+1.6418e-4*t-4.676e-7*pow(t,2)-1.7e-9*pow(t,3);
        planet_a[4]=5.202561+7e-4*cos(2*z);
        planet_i[4]=.022841752-9.941569952e-5*t+6.806784083e-8*pow(t,2);
        planet_n[4]=1.735614994+.017637075*t+6.147398691e-6*pow(t,2)-1.485275193e-7*pow(t,3);
        planet_m[4]=proper_ang_rad(3.932721257+52.96536753*t-1.26012772e-5*pow(t,2));
        planet_m[4]=proper_ang_rad(planet_m[4]+a-b/planet_e[4]);
        planet_l[4]=proper_ang_rad(planet_l[4]+a);
        planet_e[4]=planet_e[4]+ee;

        planet_a[5]=9.554747;
        planet_l[5]=proper_ang_rad(4.652426047+21.35427591*t+5.663593422e-6*pow(t,2)-1.01229e-7*pow(t,3));
        a=-.014*sin(v)+2.8e-3*u*cos(v)-2.6e-3*sin(z)-7.1e-4*sin(2*z)+1.4e-3*cos(z)*sin(q);
        a=a+1.5e-3*sin(z)*cos(q)+4.4e-4*cos(z)*cos(q);
        a=a-2.7e-4*sin(3*z)-2.9e-4*sin(2*z)*sin(q)+2.6e-4*cos(2*z)*sin(q);
        a=a+2.5e-4*cos(2*z)*cos(q);
        b=1.4e-3*sin(v)+7e-4*cos(v)-1.3e-3*sin(z)*sin(q);
        b=b-4.3e-4*sin(2*z)*sin(q)-1.3e-3*cos(q)-2.6e-3*cos(z)*cos(q)+4.7e-4*cos(2*z)*cos(q);
        b=b+1.7e-4*cos(3*z)*cos(q)-2.4e-4*sin(z)*sin(2*q);
        b=b+2.3e-4*cos(2*z)*sin(2*q)-2.3e-4*sin(z)*cos(2*q)+2.1e-4*sin(2*z)*cos(2*q);
        b=b+2.6e-4*cos(z)*cos(2*q)-2.4e-4*cos(2*z)*cos(2*q);
        planet_l[5]=proper_ang_rad(planet_l[5]+a);
        planet_e[5]=.05589232-3.455e-4*t-7.28e-7*pow(t,2)+7.4e-10*pow(t,3);
        planet_i[5]=.043502663-6.839770806e-5*t-2.703515011e-7*pow(t,2)+6.98e-10*pow(t,3);
        planet_n[5]=1.968564089+.015240129*t-2.656042056e-6*pow(t,2)-9.2677e-8*pow(t,3);
        planet_m[5]=proper_ang_rad(3.062463265+21.32009513*t-8.761552845e-6*pow(t,2));
        planet_m[5]=proper_ang_rad(planet_m[5]+a-b/planet_e[5]);
        planet_a[5]=planet_a[5]+.0336*cos(z)-.00308*cos(2*z)+.00293*cos(v);
        planet_e[5]=planet_e[5]+1.4e-3*cos(v)+1.2e-3*sin(q)+2.7e-3*cos(z)*sin(q)-1.3e-3*sin(z)*cos(q);

        for (i=1; i<6; i++)

        {

            e = planet_m[i];
            for (count = 1; count <= 50; count++)
            {
                de = e - planet_e[i] * sin(e) - planet_m[i];
                if (abs(de) <= 5e-8) break;
                e = e - de / (1 - planet_e[i] * cos(e));
            }
            v=2*atan(sqrt((1+planet_e[i])/(1-planet_e[i]))*tan(e/2));
            planet_r[i]=planet_a[i]*(1-planet_e[i]*cos(e));
            u=proper_ang_rad(planet_l[i]+v-planet_m[i]-planet_n[i]);
            x=cos(planet_i[i])*sin(u);
            y=cos(u);
            y=acos(y/sqrt(pow(x,2)+pow(y,2)));
            if (x<0) y=2*PI-y;

            hl=proper_ang_rad(y+planet_n[i]);
            ha=asin(sin(u)*sin(planet_i[i]));
            x=planet_r[i]*cos(ha)*sin(proper_ang_rad(hl-sl));
            y=planet_r[i]*cos(ha)*cos(proper_ang_rad(hl-sl))+R;
            y=acos(y/sqrt(pow(x,2)+pow(y,2)));
            if (x<0) y=2*PI-y;

            eg=proper_ang_rad(y+sl);
            dr=sqrt(pow(R,2)+pow(planet_r[i],2)+2*planet_r[i]*R*cos(ha)*cos(proper_ang_rad(hl-sl)));
            size=ang_at_1au[i]/dr;
            il=floor((pow((planet_r[i]+dr),2)-pow(R,2))/(4*planet_r[i]*dr)*100+.5);
            phase_angle=acos((pow(planet_r[i],2)+pow(dr,2)-pow(R,2))/(2*planet_r[i]*dr))*RAD;
            et=asin(planet_r[i]/dr*sin(ha));
            en=acos(cos(et)*cos(proper_ang_rad(eg-sl)));
            de=asin(sin(et)*cos(oe)+cos(et)*sin(oe)*sin(eg));
            y=cos(eg);
            x=sin(eg)*cos(oe)-tan(et)*sin(oe);
            y=acos(y/sqrt(pow(x,2)+pow(y,2)));
            if (x<0) y=2*PI-y;

            ra=y;
            ha=proper_ang_rad(gt-lo-ra);
            al=asin(sin(la)*sin(de)+cos(la)*cos(de)*cos(ha));
            az=acos((sin(de)-sin(la)*sin(al))/(cos(la)*cos(al)));
            if (sin(ha)>=0) az=2*PI-az;

            planet_decl[i] = de;
            planet_r_a[i] = ra;

            planet_mag[i]=5*log(planet_r[i]*dr)/log(10);
            if (i == 1) planet_mag[i] = -0.42 + planet_mag[i] + 0.038*phase_angle - 0.000273*pow(phase_angle,2) + 0.000002*pow(phase_angle,3);
            if (i == 2) planet_mag[i] = -4.4 + planet_mag[i] + 0.0009*phase_angle + 0.000239*pow(phase_angle,2) - 0.00000065*pow(phase_angle,3);
            if (i == 3) planet_mag[i] = -1.52 + planet_mag[i] + 0.016*phase_angle;
            if (i == 4) planet_mag[i] = -9.4 + planet_mag[i] + 0.005*phase_angle;
            if (i == 5)
            {
                sat_lat = asin(0.47063*cos(et)*sin(eg-2.9585)-0.88233*sin(et));
                planet_mag[i] = -8.88 + planet_mag[i] + 0.044*phase_angle - 2.6*sin(abs(sat_lat)) + 1.25*pow(sin(sat_lat),2);
            }
            planet_mag[i] = round_10(planet_mag[i]);
            if (planet_mag[i] == floor(planet_mag[i])) planet_mag[i] += ".0";
            if (planet_mag[i] >= 0) planet_mag[i] = "+" + planet_mag[i];

            planet_dec[i] = "";
            planet_ra[i] = "";
            planet_dist[i] = "";
            planet_size[i] = "";
            planet_phase[i] = "";
            planet_alt[i] = "";
            planet_az[i] = "";

            y = sin(-0.5667/RAD) - sin(la) * sin(de);
            x = cos(la) * cos(de);
            if (y/x >= 1)
            {
                rise_str = "No rise";
                set_str = "No rise";
                h0 = 0;
            }
            if (y/x <= -1)
            {
                rise_str = "No set";
                set_str = "No set";
                h0 = 0;
            }
            if (abs(y/x) < 1)
            {
                h0 = acos(y/x) * RAD;
                tr = range_1((ra * RAD + lo * RAD - g0 * RAD) / 360);
                rise_str = am_pm(range_1(tr - h0 / 360 + zone / 24 - dt) * 24);
                set_str = am_pm(range_1(tr + h0 / 360 + zone / 24 - dt) * 24);
                tr = am_pm(range_1(tr + zone / 24 - dt) * 24);
                if (i == 1) document.planets.tr1.value = tr;
                if (i == 2) document.planets.tr2.value = tr;
                if (i == 3) document.planets.tr3.value = tr;
                if (i == 4) document.planets.tr4.value = tr;
                if (i == 5) document.planets.tr5.value = tr;

                if (i == 1) document.planets.tr1_2.value = tr;
                if (i == 2) document.planets.tr2_2.value = tr;
                if (i == 3) document.planets.tr3_2.value = tr;
                if (i == 4) document.planets.tr4_2.value = tr;
                if (i == 5) document.planets.tr5_2.value = tr;
            }

            al = al * RAD;
            az = az * RAD;
            planet_altitude[i] = al;
            planet_azimuth[i] = az;

            de = de * RAD;
            ra = ra * RAD / 15;

            x = round_10(abs(de));
            if (de < 0)
            {
                planet_dec[i] += "-";
            }
            else
            {
                planet_dec[i] += "+";
            }
            if (x < 10) planet_dec[i] += "0";
            planet_dec[i] += x;
            if (x == floor(x)) planet_dec[i] += ".0";
            planet_dec[i] += "\u00B0";

            x = floor(ra);
            y = floor((ra - x) * 60 + 0.5);
            if (y == 60)
            {
                x += 1;
                y = 0;
            }
            if (x < 10) planet_ra[i] += "0";
            planet_ra[i] += x + "h ";
            if (y < 10) planet_ra[i] += "0";
            planet_ra[i] += y + "m";

            dr = round_100(dr);
            if (dr < 10)
                planet_dist[i] += "0";
            planet_dist[i] += dr;

            size = round_10(size);
            if (size < 10)
                planet_size[i] += "0";
            planet_size[i] += size;
            if (size == floor(size)) planet_size[i] += ".0";
            planet_size[i] += "\"";

            planet_phase[i] += il + "%";

            planet_alt[i] = rise_str;
            planet_az[i] = set_str;

        }

        dawn_planets = "";
        dusk_planets = "";
        phenomena = "";

        y = sin(-9/RAD) - sin(la) * sin(planet_decl[0]);
        x = cos(la) * cos(planet_decl[0]);
        if (y/x >= 1)
        {

            dawn_planets = "-----";
            dusk_planets = "-----";
        }
        if (y/x <= -1)
        {

            dawn_planets = "-----";
            dusk_planets = "-----";
        }
        if (abs(y/x) < 1)
        {
            h0 = acos(y/x) * RAD;
            tr = range_1((planet_r_a[0] * RAD + lo * RAD - g0 * RAD) / 360);
            dawn = range_1(tr - h0 / 360 - dt);
            dusk = range_1(tr + h0 / 360 - dt);
        }
        gt_dawn = proper_ang_rad(g0 + dawn * 6.30038808);
        gt_dusk = proper_ang_rad(g0 + dusk * 6.30038808);

        indx = 0;
        morn = 0;
        eve = 0;
        for (i=0; i<17; i++)
        {

            if (i < 6)
            {
                ha = proper_ang_rad(gt_dawn - lo - planet_r_a[i]);
                al = asin(sin(la) * sin(planet_decl[i]) + cos(la) * cos(planet_decl[i]) * cos(ha));

                if (al >= 0)
                {
                    if (morn > 0) dawn_planets += ", ";
                    dawn_planets += planet_name[i];
                    morn ++;
                }

                ha = proper_ang_rad(gt_dusk - lo - planet_r_a[i]);
                al = asin(sin(la) * sin(planet_decl[i]) + cos(la) * cos(planet_decl[i]) * cos(ha));

                if (al >= 0)
                {
                    if (eve > 0) dusk_planets += ", ";
                    dusk_planets += planet_name[i];
                    eve ++;
                }
            }

            x = round_10(acos(sin(planet_decl[6]) * sin(planet_decl[i]) + cos(planet_decl[6]) * cos(planet_decl[i]) * cos(planet_r_a[6] - planet_r_a[i])) * RAD);

            if (x <= 10 && i != 6)
            {
                if (indx > 0) phenomena += " and ";
                if (x < 1)
                {
                    phenomena += "less than 1";
                }
                else
                {
                    phenomena += floor(x + 0.5);
                }
                phenomena += "\u00B0" + " from " + planet_name[i];
                if (x < 0.5)
                {
                    if (i == 0)
                    {
                        phenomena += " (eclipse possible)";
                    }
                    else
                    {
                        phenomena += " (occultation possible)";
                    }
                }
                indx ++
            }
        }

        if (dawn_planets == "") dawn_planets = "-----";
        if (dusk_planets == "") dusk_planets = "-----";

        if (indx > 0)
        {
            phenomena = "The Moon is " + phenomena + ".  ";
        }
        indx = 0;
        for (i=1; i<16; i++)
        {
            for (count=i+1; count<17; count++)
            {
                if (i != 6 && count != 6)
                {
                    x = round_10(acos(sin(planet_decl[count]) * sin(planet_decl[i]) + cos(planet_decl[count]) * cos(planet_decl[i]) * cos(planet_r_a[count] - planet_r_a[i])) * RAD);
                    if (x <= 5)
                    {
                        if (indx > 0) phenomena += " and ";
                        phenomena += planet_name[count] + " is ";
                        if (x < 1)
                        {
                            phenomena += "less than 1";
                        }
                        else
                        {
                            phenomena += floor(x + 0.5);
                        }
                        phenomena += "\u00B0" + " from " + planet_name[i];
                        indx ++
                    }
                    x = 0;
                }
            }
        }
        if (indx > 0) phenomena += ".";

        if (jd > 2454048.3007 && jd < 2454048.5078)
        {
            phenomena += "\nMercury is transiting the face of the Sun. ";
            x = round_10((2454048.50704 - jd) * 24);
            if (x >= 0.1)
            {
                phenomena += "The spectacle continues for a further " + x + " hours."
            }
        }

        if (phenomena != "") { phenomena += "\n"; }
        if (planet_altitude[0] < -18) phenomena += "The sky is dark. ";
        if (planet_altitude[0] >= -18 && planet_altitude[0] < -12) phenomena += "The Sun is below the horizon (altitude " + floor(planet_altitude[0] + 0.5) + "\u00B0) and the sky is in deep twilight. ";
        if (planet_altitude[0] >= -12 && planet_altitude[0] < -6) phenomena += "The Sun is below the horizon (altitude " + floor(planet_altitude[0] + 0.5) + "\u00B0) and the sky is in twilight. ";
        if (planet_altitude[0] >= -6 && planet_altitude[0] < -0.3) phenomena += "The Sun is below the horizon (altitude " + floor(planet_altitude[0] + 0.5) + "\u00B0) and the sky is in bright twilight. ";
        if (planet_altitude[0] >= -0.3) phenomena += "The Sun is above the horizon (altitude " + floor(planet_altitude[0] + 0.5) + "\u00B0). ";
        if (planet_altitude[6] >= -0.3) phenomena += "The Moon is above the horizon (altitude " + floor(planet_altitude[6] + 0.5) + "\u00B0). ";
        if (planet_altitude[6] < -0.3) phenomena += "The Moon is below the horizon. ";
        phenomena += "\n";
        phenomena += moon_data;

        /* Commented out 2009-specific events 2010/01/27 - DAF
         phenomena += "-----\n2009 Phenomena\n";
         phenomena += "yyyy mm dd hh UT    All dates and times are UT\n"
         + "2009 01 02 04 UT    Mercury at greatest illuminated extent (evening)\n"
         + "2009 01 03 13 UT    Meteor shower peak -- Quadrantids\n"
         + "2009 01 04 14 UT    Mercury at greatest elongation, 19.3\u00B0 east of Sun (evening)\n"
         + "2009 01 04 16 UT    Earth at perihelion 91,400,939 miles (147,095,552 km)\n"
         + "2009 01 10 11 UT    Moon at perigee, 222,137 miles (357,495 km)\n"
         + "2009 01 14 21 UT    Venus at greatest elongation, 47.1\u00B0 east of Sun (evening)\n"
         + "2009 01 23 00 UT    Moon at apogee, 252,350 miles (406,118 km)\n"
         + "2009 01 26          Annular eclipse of the Sun (South Atlantic, Indonesia)\n"
         + "2009 02 07 20 UT    Moon at perigee, 224,619 miles (361,489 km)\n"
         + "2009 02 09 14:38 UT Penumbral lunar eclipse (midtime)\n"
         + "2009 02 11 01 UT    Mercury at greatest illuminated extent (morning)\n"
         + "2009 02 13 21 UT    Mercury at greatest elongation, 26.1\u00B0 west of Sun (morning)\n"
         + "2009 02 19 16 UT    Venus at greatest illuminated extent (evening)\n"
         + "2009 02 19 17 UT    Moon at apogee, 251,736 miles (405,129 km)\n"
         + "2009 03 07 15 UT    Moon at perigee, 228,053 miles (367,016 km)\n"
         + "2009 03 08 20 UT    Saturn at opposition\n"
         + "2009 03 19 13 UT    Moon at apogee, 251,220 miles (404,299 km)\n"
         + "2009 03 20 11:44 UT Spring (Northern Hemisphere) begins at the equinox\n"
         + "2009 03 27 19 UT    Venus at inferior conjunction with Sun\n"
         + "2009 04 02 02 UT    Moon at perigee, 229,916 miles (370,013 km)\n"
         + "2009 04 13 05 UT    Mercury at greatest illuminated extent (evening)\n"
         + "2009 04 16 09 UT    Moon at apogee, 251,178 miles (404,232 km)\n"
         + "2009 04 22 11 UT    Meteor shower peak -- Lyrids\n"
         + "2009 04 26 08 UT    Mercury at greatest elongation, 20.4\u00B0 east of Sun (evening)\n"
         + "2009 04 28 06 UT    Moon at perigee, 227,446 miles (366,039 km)\n"
         + "2009 05 02 14 UT    Venus at greatest illuminated extent (morning)\n"
         + "2009 05 06 00 UT    Meteor shower peak -- Eta Aquarids\n"
         + "2009 05 14 03 UT    Moon at apogee, 251,603 miles (404,915 km)\n"
         + "2009 05 26 04 UT    Moon at perigee, 224,410 miles (361,153 km)\n"
         + "2009 06 05 21 UT    Venus at greatest elongation, 45.8\u00B0 west of Sun (morning)\n"
         + "2009 06 10 16 UT    Moon at apogee, 252,144 miles (405,787 km)\n"
         + "2009 06 13 12 UT    Mercury at greatest elongation, 23.5\u00B0 west of Sun (morning)\n"
         + "2009 06 21 05:46 UT Summer (Northern Hemisphere) begins at the solstice\n"
         + "2009 06 23 11 UT    Moon at perigee, 222,459 miles (358,013 km)\n"
         + "2009 07 03 21 UT    Mercury at greatest illuminated extent (morning)\n"
         + "2009 07 04 02 UT    Earth at aphelion 94,505,048 miles (152,091,132 km)\n"
         + "2009 07 07 09:39 UT Penumbral lunar eclipse (too slight to see)\n"
         + "2009 07 07 22 UT    Moon at apogee, 252,421 miles (406,232 km)\n"
         + "2009 07 21 20 UT    Moon at perigee, 222,118 miles (357,464 km)\n"
         + "2009 07 22          Total solar eclipse (India, China, and a few Pacific islands)\n"
         + "2009 07 28 02 UT    Meteor shower peak -- Delta Aquarids\n"
         + "2009 08 04 01 UT    Moon at apogee, 252,294 miles (406,028 km)\n"
         + "2009 08 06 00:39 UT Weak penumbral lunar eclipse (midtime)\n"
         + "2009 08 12 17 UT    Meteor shower peak -- Perseids\n"
         + "2009 08 14 18 UT    Jupiter at opposition\n"
         + "2009 08 17 21 UT    Neptune at opposition\n"
         + "2009 08 19 05 UT    Moon at perigee, 223,469 miles (359,638 km)\n"
         + "2009 08 24 16 UT    Mercury at greatest elongation, 27.4\u00B0 east of Sun (evening)\n"
         + "2009 08 26 09 UT    Mercury at greatest illuminated extent (evening)\n"
         + "2009 08 31 11 UT    Moon at apogee, 251,823 miles (405,269 km)\n"
         + "2009 09 16 08 UT    Moon at perigee, 226,211 miles (364,052 km)\n"
         + "2009 09 17 09 UT    Uranus at opposition\n"
         + "2009 09 22 21:19 UT Fall (Northern Hemisphere) begins at the equinox\n"
         + "2009 09 28 04 UT    Moon at apogee, 251,302 miles (404,432 km)\n"
         + "2009 10 06 02 UT    Mercury at greatest elongation, 17.9\u00B0 west of Sun (morning)\n"
         + "2009 10 11 07 UT    Mercury at greatest illuminated extent (morning)\n"
         + "2009 10 13 12 UT    Moon at perigee, 229,327 miles (369,066 km)\n"
         + "2009 10 21 10 UT    Meteor shower peak -- Orionids\n"
         + "2009 10 25 23 UT    Moon at apogee, 251,137 miles (404,166 km)\n"
         + "2009 11 05 10 UT    Meteor shower peak -- Southern Taurids\n"
         + "2009 11 07 07 UT    Moon at perigee, 229,225 miles (368,902 km)\n"
         + "2009 11 12 10 UT    Meteor shower peak -- Northern Taurids\n"
         + "2009 11 17 15 UT    Meteor shower peak -- Leonids\n"
         + "2009 11 22 20 UT    Moon at apogee, 251,489 miles (404,733 km)\n"
         + "2009 12 04 14 UT    Moon at perigee, 225,856 miles (363,481 km)\n"
         + "2009 12 14 05 UT    Meteor shower peak -- Geminids\n"
         + "2009 12 17 13 UT    Mercury at greatest illuminated extent (evening)\n"
         + "2009 12 18 17 UT    Mercury at greatest elongation, 20.3\u00B0 east of Sun (evening)\n"
         + "2009 12 20 15 UT    Moon at apogee, 252,109 miles (405,731 km)\n"
         + "2009 12 21 17:47 UT Winter (Northern Hemisphere) begins at the solstice\n"
         + "2009 12 31          Partial lunar eclipse, 18:52 UT to 19:54 UT\n-----\n";
         END Comment DAF */

        document.planets.ra1.value = planet_ra[1];
        document.planets.dec1.value = planet_dec[1];
        document.planets.dist1.value = planet_mag[1];
        document.planets.size1.value = planet_size[1];
        document.planets.phase1.value = planet_phase[1];
        document.planets.alt1.value = planet_alt[1];
        document.planets.az1.value = planet_az[1];

        document.planets.ra1_2.value = planet_ra[1];
        document.planets.dec1_2.value = planet_dec[1];
        document.planets.dist1_2.value = planet_mag[1];
        document.planets.size1_2.value = planet_size[1];
        document.planets.phase1_2.value = planet_phase[1];
        document.planets.alt1_2.value = planet_alt[1];
        document.planets.az1_2.value = planet_az[1];

        document.planets.ra2.value = planet_ra[2];
        document.planets.dec2.value = planet_dec[2];
        document.planets.dist2.value = planet_mag[2];
        document.planets.size2.value = planet_size[2];
        document.planets.phase2.value = planet_phase[2];
        document.planets.alt2.value = planet_alt[2];
        document.planets.az2.value = planet_az[2];

        document.planets.ra2_2.value = planet_ra[2];
        document.planets.dec2_2.value = planet_dec[2];
        document.planets.dist2_2.value = planet_mag[2];
        document.planets.size2_2.value = planet_size[2];
        document.planets.phase2_2.value = planet_phase[2];
        document.planets.alt2_2.value = planet_alt[2];
        document.planets.az2_2.value = planet_az[2];

        document.planets.ra3.value = planet_ra[3];
        document.planets.dec3.value = planet_dec[3];
        document.planets.dist3.value = planet_mag[3];
        document.planets.size3.value = planet_size[3];
        document.planets.phase3.value = planet_phase[3];
        document.planets.alt3.value = planet_alt[3];
        document.planets.az3.value = planet_az[3];

        document.planets.ra3_2.value = planet_ra[3];
        document.planets.dec3_2.value = planet_dec[3];
        document.planets.dist3_2.value = planet_mag[3];
        document.planets.size3_2.value = planet_size[3];
        document.planets.phase3_2.value = planet_phase[3];
        document.planets.alt3_2.value = planet_alt[3];
        document.planets.az3_2.value = planet_az[3];

        document.planets.ra4.value = planet_ra[4];
        document.planets.dec4.value = planet_dec[4];
        document.planets.dist4.value = planet_mag[4];
        document.planets.size4.value = planet_size[4];
        // document.planets.phase4.value = planet_phase[4];
        document.planets.alt4.value = planet_alt[4];
        document.planets.az4.value = planet_az[4];

        document.planets.ra4_2.value = planet_ra[4];
        document.planets.dec4_2.value = planet_dec[4];
        document.planets.dist4_2.value = planet_mag[4];
        document.planets.size4_2.value = planet_size[4];
        // document.planets.phase4_2.value = planet_phase[4];
        document.planets.alt4_2.value = planet_alt[4];
        document.planets.az4_2.value = planet_az[4];

        document.planets.ra5.value = planet_ra[5];
        document.planets.dec5.value = planet_dec[5];
        document.planets.dist5.value = planet_mag[5];
        document.planets.size5.value = planet_size[5];
        // document.planets.phase5.value = planet_phase[5];
        document.planets.alt5.value = planet_alt[5];
        document.planets.az5.value = planet_az[5];

        document.planets.ra5_2.value = planet_ra[5];
        document.planets.dec5_2.value = planet_dec[5];
        document.planets.dist5_2.value = planet_mag[5];
        document.planets.size5_2.value = planet_size[5];
        // document.planets.phase5_2.value = planet_phase[5];
        document.planets.alt5_2.value = planet_alt[5];
        document.planets.az5_2.value = planet_az[5];

        document.planets.dist6.value = planet_dist[6];
        document.planets.size6.value = sunrise_set;
        document.planets.phase6.value = phenomena;
        document.planets.dawn.value = dawn_planets;
        document.planets.dusk.value = dusk_planets;
        document.planets.size7.value = moonrise_set;
        document.planets.phase7.value = twilight_begin_end;

        document.planets.ra8.value = planet_ra[8];
        document.planets.dec8.value = planet_dec[8];
        document.planets.dist8.value = planet_mag[0];
        document.planets.size8.value = planet_size[8];
        document.planets.alt8.value = planet_alt[8];
        document.planets.az8.value = planet_az[8];

        document.planets.ra8_2.value = planet_ra[8];
        document.planets.dec8_2.value = planet_dec[8];
        document.planets.dist8_2.value = planet_mag[0];
        document.planets.size8_2.value = planet_size[8];
        document.planets.alt8_2.value = planet_alt[8];
        document.planets.az8_2.value = planet_az[8];

        document.planets.ra9.value = planet_ra[9];
        document.planets.dec9.value = planet_dec[9];
        document.planets.dist9.value = planet_mag[6];
        document.planets.size9.value = planet_size[9];
        document.planets.phase9.value = planet_phase[9];
        document.planets.alt9.value = planet_alt[9];
        document.planets.az9.value = planet_az[9];

        document.planets.ra9_2.value = planet_ra[9];
        document.planets.dec9_2.value = planet_dec[9];
        document.planets.dist9_2.value = planet_mag[6];
        document.planets.size9_2.value = planet_size[9];
        document.planets.phase9_2.value = planet_phase[9];
        document.planets.alt9_2.value = planet_alt[9];
        document.planets.az9_2.value = planet_az[9];

    }
}


/*
 Calculate time for moonrise/moonset
 */
function moon_rise_set(latitude,longitude,tzone)
{
    with (Math) {

        var r_a = new Array(3);
        var dec = new Array(3);
        var dist = new Array(3);
        var i, c0, p1, p2, r1, RAD, k1, b5, l5, h, z0, t, t0;
        var s, l, m, f, d, n, g, v, u, w, a5, d5, r5, z1, c, z, m8, w8;
        var a0, d0, p, f0, f1, f2, a, b, a2, d2, l0, l2, h0, h2, d1;
        var v0=0, v1=0, v2=0, e, t3, rise_str = "", set_str = "";

        p1 = PI;
        p2 = 2 * p1;
        r1 = PI / 180;
        RAD = 1 / r1;
        k1 = 15 * r1 * 1.002738;

        b5 = latitude * RAD;
        l5 = longitude * RAD;
        h = tzone;
        l5 = -l5 / 360;
        z0 = -h / 24;

        t = (julian_date_zoned() - 2451545.0);

        t0 = t / 36525;
        s = (24110.5 + 8640184.813 * t0 + 86636.6 * z0 + 86400 * l5) / 86400;
        s = s - floor(s);
        t0 = s * 360 * r1;
        t = t + z0;

        for (i=1; i<4; i++)
        {
            l = 0.606434 + 0.036601 * t;
            m = 0.374897 + 0.036292 * t;
            f = 0.259091 + 0.036748 * t;
            d = 0.827362 + 0.033863 * t;
            n = 0.347343 - 1.470939e-04 * t;
            g = 0.993126 + 2.737778e-03 * t;

            l = (l - floor(l)) * p2;
            m = (m - floor(m)) * p2;
            f = (f - floor(f)) * p2;
            d = (d - floor(d)) * p2;
            n = (n - floor(n)) * p2;
            g = (g - floor(g)) * p2;

            v = 0.39558*sin(f+n) + 0.082*sin(f) + 0.03257*sin(m-f-n) + 0.01092*sin(m+f+n) + 6.66e-03*sin(m-f) - 6.44e-03*sin(m+f-2*d+n) - 3.31e-03*sin(f-2*d+n) - 3.04e-03*sin(f-2*d) - 2.4e-03*sin(m-f-2*d-n) + 2.26e-03*sin(m+f) - 1.08e-03*sin(m+f-2*d) - 7.9e-04*sin(f-n) + 7.8e-04*sin(f+2*d+n);

            u = 1 - 0.10828*cos(m) - 0.0188*cos(m-2*d) - 0.01479*cos(2*d) + 1.81e-03*cos(2*m-2*d) - 1.47e-03*cos(2*m) - 1.05e-03*cos(2*d-g) - 7.5e-04*cos(m-2*d+g);

            w = 0.10478*sin(m) - 0.04105*sin(2*f+2*n) - 0.0213*sin(m-2*d) - 0.01779*sin(2*f+n) + 0.01774*sin(n) + 9.87e-03*sin(2*d) - 3.38e-03*sin(m-2*f-2*n) - 3.09e-03*sin(g) - 1.9e-03*sin(2*f) - 1.44e-03*sin(m+n) - 1.44e-03*sin(m-2*f-n) - 1.13e-03*sin(m+2*f+2*n) - 9.4e-04*sin(m-2*d+g) - 9.2e-04*sin(2*m-2*d);

            s = w / sqrt(u-v*v);
            a5 = l + atan(s / sqrt(1-s*s));
            s = v / sqrt(u);
            d5 = atan(s / sqrt(1-s*s));
            r5 = 60.40974 * sqrt(u);
            r_a[i] = a5;
            dec[i] = d5;
            dist[i] = r5;

            t += 0.5;
        }

        if (r_a[2] < r_a[1]) r_a[2] += p2;
        if (r_a[3] < r_a[2]) r_a[3] += p2;

        z1 = r1 * (90.567 - 41.685 / dist[2]);
        s = sin(b5*r1);
        c = cos(b5*r1);
        z = cos(z1);
        m8 = 0;
        w8 = 0;
        a0 = r_a[1];
        d0 = dec[1];

        for (c0=0; c0<24; c0++)
        {
            p = (c0 + 1) / 24;
            f0 = r_a[1];
            f1 = r_a[2];
            f2 = r_a[3];
            a = f1 - f0;
            b = f2 - f1 - a;
            f = f0 + p * (2 * a + b * (2 * p - 1));
            a2 = f;

            f0 = dec[1];
            f1 = dec[2];
            f2 = dec[3];
            a = f1 - f0;
            b = f2 - f1 - a;
            f = f0 + p * (2 * a + b * (2 * p - 1));
            d2 = f;

            l0 = t0 + c0 * k1;
            l2 = l0 + k1;
            if (a2 < a0) a2 += p2;
            h0 = l0 - a0;
            h2 = l2 - a2;
            h1 = (h2 + h0) / 2;
            d1 = (d2 + d0) / 2;
            if (c0 < 1) v0 = s * sin(d0) + c * cos(d0) * cos(h0) - z;
            v2 = s * sin(d2) + c * cos(d2) * cos(h2) - z;
            if (sign_of(v0) != sign_of(v2))
            {
                v1 = s * sin(d1) + c * cos(d1) * cos(h1) - z;
                a = 2 * v2 - 4 * v1 + 2 * v0;
                b = 4 * v1 - 3 * v0 - v2;
                d = b * b - 4 * a * v0;
                if (d >= 0)
                {
                    d = sqrt(d);
                    e = (-b + d) / (2 * a);
                    if (e > 1 || e < 0) e = (-b - d) / (2 * a);
                    t3 = am_pm(c0 + e);
                    if (v0 < 0 && v2 > 0)
                    {
                        m8 = 1;
                        rise_str = t3;
                    }
                    if (v0 > 0 && v2 < 0)
                    {
                        w8 = 1;
                        set_str = t3;
                    }
                }
            }
            a0 = a2;
            d0 = d2;
            v0 = v2;
        }

        if (m8 == 0 && w8 == 0)
        {
            if (v2 < 0)
            {
                rise_str = "down 24h";
                set_str = "down 24h";
            }
            if (v2 > 0)
            {
                rise_str = " up 24h ";
                set_str = " up 24h ";
            }
        }
        else
        {
            if (m8 == 0) rise_str = " ------ ";
            if (w8 == 0) set_str = " ------ ";
        }

        result = rise_str + " / " + set_str;

        return result;
    }
}


/*
 Calculate time for sunrise/sunset
 */
function sun_rise_set(latitude,longitude,tzone)
{
    with (Math) {

        var r_a = new Array(2);
        var dec = new Array(2);
        var p1 = PI;
        var p2 = 2 * p1;
        var dr = p1 / 180;
        var RAD = 1 / dr;
        var k1 = 15 * dr * 1.0027379;

        var b5, l5, h, z0, t, tt, t0, s, l, g, v, u, w;
        var a5, d5,  z1, c, z, m8, w8, a0, d0, da, dd;
        var c0, p, a2, d2, l0, l2, h0, h1, h2, d1;
        var v0=0, v1=0, v2=0, a, b, d, e, t3;
        var rise_str = "", set_str = "";

        b5 = latitude * RAD;
        l5 = longitude * RAD;
        h = tzone;
        l5 = -l5 / 360;
        z0 = -h / 24;
        t = (julian_date_zoned() - 2451545.0);

        tt = t / 36525 + 1;
        t0 = t / 36525;
        s = (24110.5 + 8640184.813 * t0 + 86636.6 * z0 + 86400 * l5) / 86400;
        s = s - floor(s);
        t0 = s * 360 * dr;

        t = t + z0;
        l = .779072+.00273790931*t;
        g = .993126+.0027377785*t;
        l = (l-floor(l))*p2;
        g = (g-floor(g))*p2;
        v = .39785*sin(l)-.01000*sin(l-g)+.00333*sin(l+g)-.00021*tt*sin(l);
        u = 1-.03349*cos(g)-.00014*cos(2*l)+.00008*cos(l);
        w = -.00010-.04129*sin(2*l)+.03211*sin(g)+.00104*sin(2*l-g)-.00035*sin(2*l+g)-.00008*tt*sin(g);
        s = w/sqrt(u-v*v);
        a5 = l+atan(s/sqrt(1-s*s));
        s = v/sqrt(u);
        d5 = atan(s/sqrt(1-s*s));

        r_a[1] = a5;
        dec[1] = d5;

        t = t + 1;

        l = .779072+.00273790931*t;
        g = .993126+.0027377785*t;
        l = (l-floor(l))*p2;
        g = (g-floor(g))*p2;
        v = .39785*sin(l)-.01000*sin(l-g)+.00333*sin(l+g)-.00021*tt*sin(l);
        u = 1-.03349*cos(g)-.00014*cos(2*l)+.00008*cos(l);
        w = -.00010-.04129*sin(2*l)+.03211*sin(g)+.00104*sin(2*l-g)-.00035*sin(2*l+g)-.00008*tt*sin(g);
        s = w/sqrt(u-v*v);
        a5 = l+atan(s/sqrt(1-s*s));
        s = v/sqrt(u);
        d5 = atan(s/sqrt(1-s*s));

        r_a[2] = a5;
        dec[2] = d5;
        if (r_a[2] < r_a[1]) r_a[2] += p2;
        z1 = dr * 90.833;
        s = sin(b5 * dr);
        c = cos(b5 * dr);
        z = cos(z1);
        m8 = 0;
        w8 = 0;
        a0 = r_a[1];
        d0 = dec[1];
        da = r_a[2] - r_a[1];
        dd = dec[2] - dec[1];

        for (c0=0; c0<24; c0++)
        {
            p = (c0 + 1) / 24;
            a2 = r_a[1] + p * da;
            d2 = dec[1] + p * dd;

            l0 = t0 + c0 * k1;
            l2 = l0 + k1;
            h0 = l0 - a0;
            h2 = l2 - a2;
            h1 = (h2 + h0) / 2;
            d1 = (d2 + d0) / 2;
            if (c0 < 1) v0 = s * sin(d0) + c * cos(d0) * cos(h0) - z;
            v2 = s * sin(d2) + c * cos(d2) * cos(h2) - z;
            if (sign_of(v0) != sign_of(v2))
            {
                v1 = s * sin(d1) + c * cos(d1) * cos(h1) - z;
                a = 2 * v2 - 4 * v1 + 2 * v0;
                b = 4 * v1 - 3 * v0 - v2;
                d = b * b - 4 * a * v0;
                if (d >= 0)
                {
                    d = sqrt(d);
                    e = (-b + d) / (2 * a);
                    if (e > 1 || e < 0) e = (-b - d) / (2 * a);
                    t3 = am_pm(c0 + e);
                    if (v0 < 0 && v2 > 0)
                    {
                        m8 = 1;
                        rise_str = t3;
                    }
                    if (v0 > 0 && v2 < 0)
                    {
                        w8 = 1;
                        set_str = t3;
                    }
                }
            }
            a0 = a2;
            d0 = d2;
            v0 = v2;
        }

        if (m8 == 0 && w8 == 0)
        {
            if (v2 < 0)
            {
                rise_str = "down 24h";
                set_str = "down 24h";
            }
            if (v2 > 0)
            {
                rise_str = " up 24h ";
                set_str = " up 24h ";
            }
        }
        else
        {
            if (m8 == 0) rise_str = " ------ ";
            if (w8 == 0) set_str = " ------ ";
        }

        result = rise_str + " / " + set_str;

        return result;
    }
}


/*
 Get cookie data for loading location
 */
function getCookie( name )
{
    var start = document.cookie.indexOf( name + "=" );
    var len = start + name.length + 1;
    if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) ) {
        return null;
    }
    if ( start == -1 ) return null;
    var end = document.cookie.indexOf( ";", len );
    if ( end == -1 ) end = document.cookie.length;
    return decodeURI( document.cookie.substring( len, end ) );
}


/*
 Set cookie data for loading location
 */
function setCookie( name, value, expires, path, domain, secure )
{
    var today = new Date();
    today.setTime( today.getTime() );
    if ( expires ) {
        expires = expires * 1000 * 60 * 60 * 24;
    }
    var expires_date = new Date( today.getTime() + (expires) );
    document.cookie = name+"="+ encodeURI( value ) +
        ( ( expires ) ? ";expires="+expires_date.toGMTString() : "" ) + //expires.toGMTString()
        ( ( path ) ? ";path=" + path : "" ) +
        ( ( domain ) ? ";domain=" + domain : "" ) +
        ( ( secure ) ? ";secure" : "" );
}


/*
 Delete cookies for new location
 */
function deleteCookie( name, path, domain )
{
    if ( getCookie( name ) ) document.cookie = name + "=" +
        ( ( path ) ? ";path=" + path : "") +
        ( ( domain ) ? ";domain=" + domain : "" ) +
        ";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}


/*
 Save current location
 */
function save_loc()
{
    deleteCookie('almanac2latdeg','','');
    deleteCookie('almanac2longdeg','','');
    deleteCookie('almanac2tzonehours','','');
    setCookie('almanac2latdeg',round_1000(latitude),365,'','','');
    setCookie('almanac2longdeg',round_1000(longitude),365,'','','');
    setCookie('almanac2tzonehours',t_zone,365,'','','');
    alert("The current location has been saved.");
}

/*
 Load saved location and reload window
 */
function load_loc()
{
    var full_custom_URL = "/wp-content/plugins/observing-tools/almanac/almanac.html?";
    var display_saved_almanac;
    var lat_deg = getCookie('almanac2latdeg');
    var lng_deg = getCookie('almanac2longdeg');
    var time_zone_hours = getCookie('almanac2tzonehours');

    if (lat_deg == null && lng_deg == null)
    {
        alert ("Sorry, but you haven't saved a previous latitude and longitude. If you want the Almanac to remember the currently displayed location, please click the 'Save this?' link. Alternatively, enter a new latitude and longitude by clicking the 'New?' link.")
    }
    else
    {
        full_custom_URL += "latitude=" + lat_deg + "&";
        full_custom_URL += "longitude=" + lng_deg + "&";
        full_custom_URL += "tzone=" + time_zone_hours + "&";
        full_custom_URL += "UTdate=" + document.planets.date_txt.value + "&";
        full_custom_URL += "UTtime=" + document.planets.ut_h_m.value;
        display_saved_almanac = window.open(full_custom_URL,'chooser');
    }
}