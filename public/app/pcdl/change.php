<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Change DB</h2>
<?php

#废弃代码

include "./_pdo.php";
include "../app/public.inc";

$from = $_GET["from"];
$to = $_GET["to"];

$book[0] = "";
$book[1] = "Namakkārapāḷi";
$book[2] = "Mahāpaṇāmapāṭha(Buddhavandanā)";
$book[3] = "Lakkhaṇāto";
$book[4] = "Suttavandanā";
$book[5] = "Jinālaṅkāra";
$book[6] = "Kamalāñjali";
$book[7] = "Pajjamadhu";
$book[8] = "Buddhaguṇagāthāvalī";
$book[9] = "Abhidhānappadīpikāṭīkā";
$book[10] = "Subodhālaṅkāro";
$book[11] = "Subodhālaṅkāraṭīkā";
$book[12] = "Bālāvatāra";
$book[13] = "Moggallānasuttapāṭho";
$book[14] = "Kaccāyanabyākaraṇaṃ";
$book[15] = "Saddanītippakaraṇaṃ (padamālā)";
$book[16] = "Saddanītippakaraṇaṃ";
$book[17] = "Padarūpasiddhi";
$book[18] = "Moggallāna pañcikā ṭīkā";
$book[19] = "Payogasiddhipāḷi";
$book[20] = "Vuttodayaṃ";
$book[21] = "Abhidhānappadīpikā";
$book[22] = "Niruttidīpanīpāṭha";
$book[23] = "Paramatthadīpanī";
$book[24] = "Anudīpanīpāṭha";
$book[25] = "Paṭṭhānuddesa dīpanīpāṭha";
$book[26] = "Caturārakkhadīpanī";
$book[27] = "Kavidappaṇanīti";
$book[28] = "Nītimañjarī";
$book[29] = "Dhammanīti";
$book[30] = "Mahārahanīti";
$book[31] = "Lokanīti";
$book[32] = "Suttantanīti";
$book[33] = "Sūrassatīnīti";
$book[34] = "Cāṇakyanītipāḷi";
$book[35] = "Naradakkhadīpanī";
$book[36] = "Rasavāhinī";
$book[37] = "Sīmavisodhanī";
$book[38] = "Vessantarāgīti";
$book[39] = "Dīghanikāye";
$book[40] = "Majjhimanikāya";
$book[41] = "Saṃyuttanikāye";
$book[42] = "Aṅguttaranikāye";
$book[43] = "Vinayapiṭaka";
$book[44] = "Abhidhammapiṭaka";
$book[45] = "Aṭṭhakathā";
$book[46] = "Milidaṭīkā";
$book[47] = "Padamañjarī";
$book[48] = "Padasādhanaṃ";
$book[49] = "Saddabindu pakaraṇaṃ";
$book[50] = "Kaccāyana dhātu mañjūsā";
$book[51] = "Samantakūṭavaṇṇanā";
$book[52] = "Vuttisametā";
$book[53] = "Thupavaṃso";
$book[54] = "Dāṭhāvaṃso";
$book[55] = "Dhātupāṭha vilāsiniyā";
$book[56] = "Dhātuvaṃso";
$book[57] = "Hatthavanagallavihāra vaṃso";
$book[58] = "Jinacaritaya";
$book[59] = "Jinavaṃsadīpaṃ";
$book[60] = "Telakaṭāhagāthā";
$book[61] = "Cūḷaganthavaṃsapāḷi";
$book[62] = "Sāsanavaṃsappadīpikā";
$book[63] = "Mahāvaṃsapāḷi";
$book[64] = "Visuddhimaggo(Paṭhamo bhāgo)";
$book[65] = "Visuddhimaggo(Dutiyo bhāgo)";
$book[66] = "Visuddhimagga-mahāṭīkā(Paṭhamo bhāgo)";
$book[67] = "Visuddhimagga-mahāṭīkā(Dutiyo bhāgo)";
$book[68] = "Visuddhimagga nidānakathā";
$book[69] = "Paṭṭhānapāḷi(Dutiyo bhāgo)";
$book[70] = "Paṭṭhānapāḷi(Tatiyo bhāgo)";
$book[71] = "Paṭṭhānapāḷi(Catuttho bhāgo)";
$book[72] = "Paṭṭhānapāḷi(Pañcamo bhāgo)";
$book[73] = "Dhammasaṅgaṇīpāḷi";
$book[74] = "Vibhaṅgapāḷi";
$book[75] = "Dhātukathāpāḷi";
$book[76] = "Puggalapaññattipāḷi";
$book[77] = "Kathāvatthupāḷi";
$book[78] = "Yamakapāḷi (paṭhamo bhāgo)";
$book[79] = "Yamakapāḷi (dutiyo bhāgo)";
$book[80] = "Yamakapāḷi (tatiyo bhāgo)";
$book[81] = "Paṭṭhānapāḷi(Paṭhamo bhāgo)";
$book[82] = "Dasakanipātapāḷi";
$book[83] = "Ekādasakanipātapāḷi";
$book[84] = "Ekakanipātapāḷi";
$book[85] = "Dukanipātapāḷi";
$book[86] = "Tikanipātapāḷi";
$book[87] = "Catukkanipātapāḷi";
$book[88] = "Pañcakanipātapāḷi";
$book[89] = "Chakkanipātapāḷi";
$book[90] = "Sattakanipātapāḷi";
$book[91] = "Aṭṭhakanipātapāḷi";
$book[92] = "Navakanipātapāḷi";
$book[93] = "Sīlakkhandhavaggapāḷi";
$book[94] = "Mahāvaggapāḷi";
$book[95] = "Pāthikavaggapāḷi";
$book[96] = "Dhammasaṅgaṇī-aṭṭhakathā";
$book[97] = "Vibhaṅga-aṭṭhakathā";
$book[98] = "Pañcapakaraṇa-aṭṭhakathā";
$book[99] = "Ekakanipāta-aṭṭhakathā";
$book[100] = "Dukanipāta-aṭṭhakathā";
$book[101] = "Pañcakanipāta-aṭṭhakathā";
$book[102] = "Aṭṭhakanipāta-aṭṭhakathā";
$book[103] = "Sīlakkhandhavaggaṭṭhakathā";
$book[104] = "Mahāvaggaṭṭhakathā";
$book[105] = "Pāthikavaggaṭṭhakathā";
$book[106] = "Therīgāthā-aṭṭhakathā";
$book[107] = "Apadāna-aṭṭhakathā(Paṭhamo bhāgo)";
$book[108] = "Buddhavaṃsa-aṭṭhakathā";
$book[109] = "Cariyāpiṭaka-aṭṭhakathā";
$book[110] = "Jātaka-aṭṭhakathā(Paṭhamo bhāgo)";
$book[111] = "Jātaka-aṭṭhakathā(Dutiyo bhāgo)";
$book[112] = "Jātaka-aṭṭhakathā(Tatiyo bhāgo)";
$book[113] = "Jātaka-aṭṭhakathā(Catuttho bhāgo)";
$book[114] = "Jātaka-aṭṭhakathā(Pañcamo bhāgo)";
$book[115] = "Jātaka-aṭṭhakathā(Chaṭṭho bhāgo)";
$book[116] = "Khuddakapāṭha-aṭṭhakathā";
$book[117] = "Jātaka-aṭṭhakathā(Sattamo bhāgo)";
$book[118] = "Mahāniddesa-aṭṭhakathā";
$book[119] = "Cūḷaniddesa-aṭṭhakathā";
$book[120] = "Paṭisambhidāmagga-aṭṭhakathā(Paṭhamo bhāgo)";
$book[121] = "Nettippakaraṇa-aṭṭhakathā";
$book[122] = "Dhammapada-aṭṭhakathā";
$book[123] = "Udāna-aṭṭhakathā";
$book[124] = "Itivuttaka-aṭṭhakathā";
$book[125] = "Suttanipāta-aṭṭhakathā";
$book[126] = "Vimānavatthu-aṭṭhakathā";
$book[127] = "Petavatthu-aṭṭhakathā";
$book[128] = "Theragāthā-aṭṭhakathā(Paṭhamo bhāgo)";
$book[129] = "Theragāthā-aṭṭhakathā(Dutiyo bhāgo)";
$book[130] = "Mūlapaṇṇāsa-aṭṭhakathā";
$book[131] = "Majjhimapaṇṇāsa-aṭṭhakathā";
$book[132] = "Uparipaṇṇāsa-aṭṭhakathā";
$book[133] = "Sagāthāvagga-aṭṭhakathā";
$book[134] = "Nidānavagga-aṭṭhakathā";
$book[135] = "Khandhavagga-aṭṭhakathā";
$book[136] = "Saḷāyatanavagga-aṭṭhakathā";
$book[137] = "Mahāvagga-aṭṭhakathā";
$book[138] = "Pārājikakaṇḍa-aṭṭhakathā (paṭhamo bhāgo)";
$book[139] = "Pācittiya-aṭṭhakathā";
$book[140] = "Mahāvagga-aṭṭhakathā";
$book[141] = "Cūḷavagga-aṭṭhakathā";
$book[142] = "Parivāra-aṭṭhakathā";
$book[143] = "Therāpadānapāḷi(Paṭhamo bhāgo)";
$book[144] = "Therāpadānapāḷi(Dutiyo bhāgo)";
$book[145] = "Buddhavaṃsapāḷi";
$book[146] = "Cariyāpiṭakapāḷi";
$book[147] = "Jātakapāḷi(Dutiyo bhāgo)";
$book[148] = "Jātakapāḷi(Paṭhamo bhāgo)";
$book[149] = "Mahāniddesapāḷi";
$book[150] = "Cūḷaniddesapāḷi";
$book[151] = "Paṭisambhidāmaggapāḷi";
$book[152] = "Milindapañhapāḷi";
$book[153] = "Nettippakaraṇapāḷi";
$book[154] = "Khuddakapāṭhapāḷi";
$book[155] = "Peṭakopadesapāḷi";
$book[156] = "Dhammapadapāḷi";
$book[157] = "Udānapāḷi";
$book[158] = "Itivuttakapāḷi";
$book[159] = "Suttanipātapāḷi";
$book[160] = "Vimānavatthupāḷi";
$book[161] = "Petavatthupāḷi";
$book[162] = "Theragāthāpāḷi";
$book[163] = "Therīgāthāpāḷi";
$book[164] = "Mūlapaṇṇāsapāḷi";
$book[165] = "Majjhimapaṇṇāsapāḷi";
$book[166] = "Uparipaṇṇāsapāḷi";
$book[167] = "Sagāthāvaggo";
$book[168] = "Nidānavaggo";
$book[169] = "Khandhavaggo";
$book[170] = "Saḷāyatanavaggo";
$book[171] = "Mahāvaggo";
$book[172] = "Dhammasaṅgaṇī-mūlaṭīkā";
$book[173] = "Vibhaṅga-mūlaṭīkā";
$book[174] = "Pañcapakaraṇa-mūlaṭīkā";
$book[175] = "Dhammasaṅgaṇī-anuṭīkā";
$book[176] = "Pañcapakaraṇa-anuṭīkā";
$book[177] = "Ganthārambhakathā";
$book[178] = "Abhidhammatthasaṅgaho";
$book[179] = "Paṭhamo paricchedo";
$book[180] = "Abhidhammamātikāpāḷi";
$book[181] = "Ekakanipāta-ṭīkā";
$book[182] = "Dukanipāta-ṭīkā";
$book[183] = "Pañcakanipāta-ṭīkā";
$book[184] = "Aṭṭhakanipāta-ṭīkā";
$book[185] = "Sīlakkhandhavaggaṭīkā";
$book[186] = "Mahāvaggaṭīkā";
$book[187] = "Pāthikavaggaṭīkā";
$book[188] = "Sīlakkhandhavaggaabhinavaṭīkā";
$book[189] = "Sīlakkhandhavaggaabhinavaṭīkā";
$book[190] = "Nettippakaraṇa-ṭīkā";
$book[191] = "Nettivibhāvinī";
$book[192] = "Mūlapaṇṇāsa-ṭīkā";
$book[193] = "Majjhimapaṇṇāsaṭīkā";
$book[194] = "Uparipaṇṇāsa-ṭīkā";
$book[195] = "Sagāthāvaggaṭīkā";
$book[196] = "Nidānavaggaṭīkā";
$book[197] = "Khandhavaggaṭīkā";
$book[198] = "Saḷāyatanavaggaṭīkā";
$book[199] = "Mahāvaggaṭīkā";
$book[200] = "Vinayavinicchayo";
$book[201] = "Vinayavinicchayaṭīkā(Paṭhamo bhāgo)";
$book[202] = "Pācityādiyojanā";
$book[203] = "Khuddasikkhā-mūlasikkhā";
$book[204] = "Sāratthadīpanī-ṭīkā (paṭhamo bhāgo)";
$book[205] = "Sāratthadīpanī-ṭīkā (dutiyo bhāgo)";
$book[206] = "Sāratthadīpanī-ṭīkā (tatiyo bhāgo)";
$book[207] = "Bhikkhupātimokkhapāḷi";
$book[208] = "Vinayasaṅgaha-aṭṭhakathā";
$book[209] = "Vajirabuddhi-ṭīkā";
$book[210] = "Vimativinodanī-ṭīkā (paṭhamo bhāgo)";
$book[211] = "Vinayālaṅkāra-ṭīkā (paṭhamo bhāgo)";
$book[212] = "Kaṅkhāvitaraṇīpurāṇa-ṭīkā";
$book[213] = "Pārājikapāḷi";
$book[214] = "Pācittiyapāḷi";
$book[215] = "Mahāvaggapāḷi";
$book[216] = "Cūḷavaggapāḷi";
$book[217] = "Parivārapāḷi";

//for($i=2;$i<218;$i++)
$i = $from;
{
    $db_file = "../appdata/palicanon/pali_text/p" . $i . "_pali.db3";
    $filename = "appdata/palicanon/pali_text/p" . $i . "_pali.db3";
    PDO_Connect("$db_file");
    $query = "CREATE TABLE album (
    id           INTEGER PRIMARY KEY ASC AUTOINCREMENT,
	guid TEXT,
    title         TEXT,
    file         TEXT,
    cover        TEXT,
    language     INTEGER,
    author       TEXT,
    target       TEXT,
    summary      TEXT,
    publish_time INTEGER,
    update_time  INTEGER,
    edition      INTEGER,
    edition1     TEXT,
    type         INTEGER
);";
    $stmt = @PDO_Execute($query);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        print_r($error[2]);
        exit;
    }

    echo "create $i ok<br>";

    $guid = GUIDv4();
    $query = "INSERT INTO album (id, guid,title,file,cover,language,author,target,summary,publish_time,update_time,edition1,type) VALUES ('1','$guid','" . $book[$i] . "', '$filename','','0','VRI','','','1','1','CSCD4','1')";

    $stmt = @PDO_Execute($query);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        print_r($error[2]);
        exit;
    }

    echo "insert $i ok<br>";

    $query = "ALTER TABLE data ADD album INTEGER";
    $stmt = @PDO_Execute($query);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        print_r($error[2]);
        exit;
    }

    $query = "UPDATE data SET album = '1' WHERE 1 ";
    $stmt = @PDO_Execute($query);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        print_r($error[2]);
        exit;
    }
}
if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"change.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $book[$from + 1];
}
?>