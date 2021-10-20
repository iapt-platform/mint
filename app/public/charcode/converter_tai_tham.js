var char_roman_to_tai = [
	//{ id: "n’</w>ti<w>", value: "nti" },
	{ id: "bbho", value: "ᨻᩮ᩠ᨽᩣ" },
	{ id: "ccho", value: "ᨧᩮ᩠ᨨᩣ" },
	{ id: "ddho", value: "ᨴᩮ᩠ᨵᩣ" },
	{ id: "ḍḍho", value: "ᨯᩮ᩠ᨰᩣ" },
	{ id: "ggho", value: "ᨣᩮ᩠ᨥᩣ" },
	{ id: "jjho", value: "ᨩᩮ᩠ᨫᩣ" },
	{ id: "kkho", value: "ᨠᩮ᩠ᨡᩣ" },
	{ id: "mbho", value: "ᨾᩮ᩠ᨽᩣ" },
	{ id: "mpho", value: "ᨾᩮ᩠ᨹᩣ" },
	{ id: "ndho", value: "ᨶᩮᩣ᩠ᨵ" },
	{ id: "ntho", value: "ᨶᩮᩣ᩠ᨳ" },
	{ id: "ndhā", value: "ᨶᩣ᩠ᨵ" },
	{ id: "nthā", value: "ᨶᩣ᩠ᨳ" },
	{ id: "ṅgho", value: "ᩘᨥᩮᩣ" },//  "ᩘᩮ᩠ᨿᩣ
	{ id: "ṅkho", value: "ᩘᨡᩮᩣ" },// "ᩘᩮ᩠ᨡᩣ"
	{ id: "ñcho", value: "ᨬᩮ᩠ᨨᩣ" },
	{ id: "ñjho", value: "ᨬᩮ᩠ᨫᩣ" },
	{ id: "ṇḍho", value: "ᨱᩮ᩠ᨰᩣ" },
	{ id: "ṇṭho", value: "ᨱᩮ᩠ᨮᩣ" },
	{ id: "ppho", value: "ᨷᩮ᩠ᨹᩣ" },
	{ id: "ttho", value: "ᨲᩮ᩠ᨳᩣ" },
	{ id: "ṭṭho", value: "ᨭᩛᩮᩣ" },
	{ id: "bbhe", value: "ᨻᩮ᩠ᨽ" },
	{ id: "mbhe", value: "ᨾᩮ᩠ᨽ" },
	{ id: "cche", value: "ᨧᩮ᩠ᨨ" },
	{ id: "ñche", value: "ᨬᩮ᩠ᨨ" },
	{ id: "ddhe", value: "ᨴᩮ᩠ᨵ" },
	{ id: "ndhe", value: "ᨶᩮ᩠ᨵ" },
	{ id: "ḍḍhe", value: "ᨯᩮ᩠ᨰ" },
	{ id: "ṇḍhe", value: "ᨱᩮ᩠ᨰ" },
	{ id: "gghe", value: "ᨣᩮ᩠ᨥ" },
	{ id: "ṅghe", value: "ᩘᨥᩮ" },//  "ᩘᩮ᩠ᨿ
	{ id: "ṅkhe", value: "ᩘᨡᩮ" },// "ᩘᩮ᩠ᨡ
	{ id: "jjhe", value: "ᨩᩮ᩠ᨫ" },
	{ id: "ñjhe", value: "ᨬᩮ᩠ᨫ" },
	{ id: "kkhe", value: "ᨠᩮ᩠ᨡ" },
	{ id: "mphe", value: "ᨾᩮ᩠ᨹ" },
	{ id: "pphe", value: "ᨷᩮ᩠ᨹ" },
	{ id: "nthe", value: "ᨶᩮ᩠ᨳ" },
	{ id: "tthe", value: "ᨲᩮ᩠ᨳ" },
	{ id: "ṇṭhe", value: "ᨱᩮ᩠ᨮ" },
	{ id: "ṭṭhe", value: "ᨭᩛᩮ" },
	{ id: "bbo", value: "ᨻᩮ᩠ᨻᩣ" },
	{ id: "cco", value: "ᨧᩮ᩠ᨧᩣ" },
	{ id: "ddo", value: "ᨴᩮ᩠ᨴᩣ" },
	{ id: "dvo", value: "ᨴᩮ᩠ᩅᩣ" },
	{ id: "ḍḍo", value: "ᨯᩮ᩠ᨯᩣ" },
	{ id: "ggo", value: "ᨣᩮ᩠ᨣᩣ" },
	{ id: "hro", value: "ᩉᩮ᩠ᩕᩣ" },
	{ id: "hvo", value: "ᩉᩮ᩠ᩅᩣ" },
	{ id: "hyo", value: "ᩉᩮ᩠ᨿᩣ" },
	{ id: "jjo", value: "ᨩᩮ᩠ᨩᩣ" },
	{ id: "kko", value: "ᨠᩮ᩠ᨠᩣ" },
	{ id: "kro", value: "ᨠᩮ᩠ᩕᩣ" },
	{ id: "mbo", value: "ᨾᩮ᩠ᨻᩣ" },
	{ id: "mmo", value: "ᨾᩮ᩠ᨾᩣ" },
	{ id: "mpo", value: "ᨾᩮ᩠ᨷᩣ" },
	{ id: "ndo", value: "ᨶᩮᩣ᩠ᨴ" },
	{ id: "nno", value: "ᨶᩮᩣ᩠ᨶ" },
	{ id: "nto", value: "ᨶᩮᩣ᩠ᨲ" },
	{ id: "ndā", value: "ᨶᩣ᩠ᨴ" },
	{ id: "nnā", value: "ᨶᩣ᩠ᨶ" },
	{ id: "ntā", value: "ᨶᩣ᩠ᨲ" },
	{ id: "ṅgo", value: "ᩘ ᨣᩮᩤ" },//  ᩘᩮ᩠ᨣᩣ
	{ id: "ṅko", value: "ᩘᨠᩮᩣ" },//  ᩘᩮ᩠ᨠᩣ
	{ id: "ñco", value: "ᨬᩮ᩠ᨧᩣ" },
	{ id: "ñjo", value: "ᨬᩮ᩠ᨩᩣ" },
	{ id: "ñño", value: "ᨬᩮ᩠ᨬᩣ" },
	{ id: "ṇḍo", value: "ᨱᩮ᩠ᨯᩣ" },
	{ id: "ṇṇo", value: "ᨱᩮ᩠ᨱᩣ" },
	{ id: "ṇṭo", value: "ᨱᩮ᩠ᨭᩣ" },
	{ id: "ppo", value: "ᨷᩮ᩠ᨷᩣ" },
	{ id: "rho", value: "ᩁᩮ᩠ᩉᩣ" },
	{ id: "rvo", value: "ᩁᩮ᩠ᩅᩣ" },
	{ id: "ryo", value: "ᩁᩮ᩠ᨿᩣ" },
	{ id: "tto", value: "ᨲᩮ᩠ᨲᩣ" },
	{ id: "tvo", value: "ᨲᩮ᩠ᩅᩣ" },
	{ id: "ṭṭo", value: "ᨭᩮ᩠ᨭᩣ" },
	{ id: "vho", value: "ᩅᩮ᩠ᩉᩣ" },
	{ id: "vro", value: "ᩅᩮ᩠ᩕᩣ" },
	{ id: "vyo", value: "ᩅᩮ᩠ᨿᩣ" },
	{ id: "yho", value: "ᨿᩮ᩠ᩉᩣ" },
	{ id: "yro", value: "ᨿᩮ᩠ᩕᩣ" },
	{ id: "yvo", value: "ᨿᩮ᩠ᩅᩣ" },
	{ id: "yyo", value: "ᨿᩮ᩠ᨿᩣ" },
	{ id: "bbe", value: "ᨻᩮ᩠ᨻ" },
	{ id: "mbe", value: "ᨾᩮ᩠ᨻ" },
	{ id: "cce", value: "ᨧᩮ᩠ᨧ" },
	{ id: "ñce", value: "ᨬᩮ᩠ᨧ" },
	{ id: "dde", value: "ᨴᩮ᩠ᨴ" },
	{ id: "nde", value: "ᨶᩮ᩠ᨴ" },
	{ id: "ḍḍe", value: "ᨯᩮ᩠ᨯ" },
	{ id: "ṇḍe", value: "ᨱᩮ᩠ᨯ" },
	{ id: "gge", value: "ᨣᩮ᩠ᨣ" },
	{ id: "ṅge", value: "ᩘᨣᩮ" }, // "ᩘᩮ᩠ᨣ
	{ id: "rhe", value: "ᩁᩮ᩠ᩉ" },
	{ id: "vhe", value: "ᩅᩮ᩠ᩉ" },
	{ id: "yhe", value: "ᨿᩮ᩠ᩉ" },
	{ id: "jje", value: "ᨩᩮ᩠ᨩ" },
	{ id: "ñje", value: "ᨬᩮ᩠ᨩ" },
	{ id: "kke", value: "ᨠᩮ᩠ᨠ" },
	{ id: "ṅke", value: "ᩘᨠᩮ" },//  ᩘᩮ᩠ᨠ
	{ id: "mme", value: "ᨾᩮ᩠ᨾ" },
	{ id: "nne", value: "ᨶᩮ᩠ᨶ" },
	{ id: "ññe", value: "ᨬᩮ᩠ᨬ" },
	{ id: "ṇṇe", value: "ᨱᩮ᩠ᨱ" },
	{ id: "mpe", value: "ᨾᩮ᩠ᨷ" },
	{ id: "ppe", value: "ᨷᩮ᩠ᨷ" },
	{ id: "hre", value: "ᩉᩮ᩠ᩕ" },
	{ id: "kre", value: "ᨠᩮ᩠ᩕ" },
	{ id: "vre", value: "ᩅᩮ᩠ᩕ" },
	{ id: "yre", value: "ᨿᩮ᩠ᩕ" },
	{ id: "nte", value: "ᨶᩮ᩠ᨲ" },
	{ id: "tte", value: "ᨲᩮ᩠ᨲ" },
	{ id: "ṇṭe", value: "ᨱᩮ᩠ᨭ" },
	{ id: "ṭṭe", value: "ᨭᩮ᩠ᨭ" },
	{ id: "dve", value: "ᨴᩮ᩠ᩅ" },
	{ id: "hve", value: "ᩉᩮ᩠ᩅ" },
	{ id: "rve", value: "ᩁᩮ᩠ᩅ" },
	{ id: "tve", value: "ᨲᩮ᩠ᩅ" },
	{ id: "yve", value: "ᨿᩮ᩠ᩅ" },
	{ id: "hye", value: "ᩉᩮ᩠ᨿ" },
	{ id: "rye", value: "ᩁᩮ᩠ᨿ" },
	{ id: "vye", value: "ᩅᩮ᩠ᨿ" },
	{ id: "yye", value: "ᨿᩮ᩠ᨿ" },
	//{ id: "mmā", value: "ᨾᩜᩣ" },
	//{ id: "mma", value: "ᨾᩜ" },
	{ id: "ṭṭh", value: "ᨭᩛ᩠" },
		
	{ id: "ss", value: "ᩔ᩠" },
	{ id: "vh", value: "ᩅ᩠ᩉ᩠" },
	{ id: "vy", value: "ᩅ᩠ᨿ᩠" },
	{ id: "vr", value: "ᩅᩕ᩠" },
	{ id: "yh", value: "ᨿ᩠ᩉ᩠" },
	{ id: "yy", value: "ᨿ᩠ᨿ᩠" },
	{ id: "yr", value: "ᨿᩕ᩠" },
	{ id: "yv", value: "ᨿ᩠ᩅ᩠" },
	{ id: "hy", value: "ᩉ᩠ᨿ᩠" },
	{ id: "hr", value: "ᩉᩕ᩠" },
	{ id: "hv", value: "ᩉ᩠ᩅ᩠" },
	{ id: "rv", value: "ᩁ᩠ᩅ᩠" },
	{ id: "rh", value: "ᩁ᩠ᩉ᩠" },
	{ id: "ry", value: "ᩁ᩠ᨿ᩠" },
	{ id: "kh", value: "ᨡ᩠" },
	{ id: "gh", value: "ᨥ᩠" },
	{ id: "ch", value: "ᨨ᩠" },
	{ id: "jh", value: "ᨫ᩠" },
	{ id: "ññ", value: "ᨬ᩠ᨬ᩠" },
	{ id: "ṭh", value: "ᨮ᩠" },
	{ id: "ḍh", value: "ᨰ᩠" },
	{ id: "th", value: "ᨳ᩠" },
	{ id: "dh", value: "ᨵ᩠" },
	{ id: "ph", value: "ᨹ᩠" },
	{ id: "bh", value: "ᨽ᩠" },
	{ id: "k", value: "ᨠ᩠" },
	{ id: "g", value: "ᨣ᩠" },
	{ id: "c", value: "ᨧ᩠" },
	{ id: "j", value: "ᨩ᩠" },
	{ id: "ñ", value: "ᨬ᩠" },
	{ id: "ḷ", value: "ᩊ᩠" },
	{ id: "ṭ", value: "ᨭ᩠" },
	{ id: "ḍ", value: "ᨯ᩠" },
	{ id: "ṇ", value: "ᨱ᩠" },
	{ id: "t", value: "ᨲ᩠" },
	{ id: "d", value: "ᨴ᩠" },
	{ id: "n", value: "ᨶ᩠" },
	{ id: "p", value: "ᨷ᩠" },
	{ id: "b", value: "ᨻ᩠" },
	{ id: "m", value: "ᨾ᩠" },
	{ id: "l", value: "ᩃ᩠" },
	{ id: "s", value: "ᩈ᩠" },
	{ id: "ṅ", value: "ᩘ" },
	{ id: "᩠h", value: "᩠ᩉ᩠" },
	{ id: "h", value: "ᩉ᩠" },
	{ id: "᩠y", value: "᩠ᨿ" },
	{ id: "y", value: "ᨿ᩠" },
	{ id: "᩠r", value: "ᩕ᩠" },
	{ id: "r", value: "ᩁ᩠" },
	{ id: "᩠v", value: "᩠ᩅ᩠" },
	{ id: "v", value: "ᩅ᩠" },
	{ id: "᩠aṃ", value: "ᩴ" },
	{ id: "᩠iṃ", value: "ᩥᩴ" },
	{ id: "᩠uṃ", value: "ᩩᩴ" },
	{ id: "᩠ā", value: "ᩣ" },
	{ id: "᩠i", value: "ᩥ" },
	{ id: "᩠ī", value: "ᩦ" },
	{ id: "᩠u", value: "ᩩ" },
	{ id: "᩠ū", value: "ᩪ" },
	{ id: "᩠e", value: "ᩮ" },
	{ id: "᩠o", value: "ᩮᩣ" },
	{ id: "aṃ", value: "ᩋᩴ" },
	{ id: "iṃ", value: "ᨠ᩠ᨠᩴ" },
	{ id: "uṃ", value: "ᩏᩴ" },
	{ id: "a", value: "ᩋ" },
	{ id: "ā", value: "ᩋᩣ" },
	{ id: "i", value: "ᩍ" },
	{ id: "ī", value: "ᩎ" },
	{ id: "u", value: "ᩏ" },
	{ id: "ū", value: "ᩐ" },
	{ id: "e", value: "ᩑ" },
	{ id: "o", value: "ᩒ" },
	{ id: "᩠᩼ᩋ", value: "" },
	{ id: "᩠ᩋ", value: "" },
//{ id: "ᨡᩮᩣ", value: "ᨡᩮᩤ" },
//{ id: "ᨡᩣ", value: "ᨡᩤ" },
{ id: "ᨠ᩠ᨡᩮᩤ", value: "ᨠᩮ᩠ᨡᩣ" },
{ id: "က᩠ခါ", value: "ᨠ᩠ᨡᩣ" },
{ id: "ဂော", value: "ᨣᩮᩤ" },
//{ id: "ᨦᩮᩣ", value: "ᨦᩮᩤ" },
{ id: "ᨴᩮᩣ", value: "ᨴᩮᩤ" },
{ id: "ᨷᩮᩣ", value: "ᨷᩮᩤ" },
{ id: "ᩅᩮᩣ", value: "ᩅᩮᩤ" },
{ id: "ᨣᩣ", value: "ᨣᩤ" },
//{ id: "ᨦᩣ", value: "ᨦᩤ" },
{ id: "ᨴᩣ", value: "ᨴᩤ" },
{ id: "ᨵᩣ", value: "ᨵᩤ" },
{ id: "ᨷᩣ", value: "ᨷᩤ" },
{ id: "ᩅᩣ", value: "ᩅᩤ" },
{ id: "ᨴ᩠ᩅᩣ", value: "ᨴ᩠ᩅᩤ" },
{ id: "ᩘ ", value: "ᩘ" },
{ id: "ᨷ᩠ᨷᩤ", value: "ᨷ᩠ᨷᩣ" },
{ id: "ᨲ᩠ᩅᩤ", value: "ᨲ᩠ᩅᩣ" },
{ id: "ᩮ᩠ᨷᩤ", value: "ᩮ᩠ᨷᩣ" },
		
];




var char_tai_to_roman_1 = [
{ id: "ႁႏၵ", value: "ndra" }, //後加

{ id: "ခ္", value: "kh" },
{ id: "ဃ္", value: "gh" },
{ id: "ဆ္", value: "ch" },
{ id: "ဈ္", value: "jh" },
{ id: "ည္", value: "ññ" },
{ id: "ဌ္", value: "ṭh" },
{ id: "ဎ္", value: "ḍh" },
{ id: "ထ္", value: "th" },
{ id: "ဓ္", value: "dh" },
{ id: "ဖ္", value: "ph" },
{ id: "ဘ္", value: "bh" },
{ id: "က္", value: "k" },
{ id: "ဂ္", value: "g" },
{ id: "စ္", value: "c" },
{ id: "ဇ္", value: "j" },
{ id: "ဉ္", value: "ñ" },
{ id: "ဠ္", value: "ḷ" },
{ id: "ဋ္", value: "ṭ" },
{ id: "ဍ္", value: "ḍ" },
{ id: "ဏ္", value: "ṇ" },
{ id: "တ္", value: "t" },
{ id: "ဒ္", value: "d" },
{ id: "န္", value: "n" },
{ id: "ဟ္", value: "h" },
{ id: "ပ္", value: "p" },
{ id: "ဗ္", value: "b" },
{ id: "မ္", value: "m" },
{ id: "ယ္", value: "y" },
{ id: "ရ္", value: "r" },
{ id: "လ္", value: "l" },
{ id: "ဝ္", value: "v" },
{ id: "သ္", value: "s" },
{ id: "င္", value: "ṅ" },
{ id: "င်္", value: "ṅ" },
{ id: "ဿ", value: "ssa" },
{ id: "ခ", value: "kha" },
{ id: "ဃ", value: "gha" },
{ id: "ဆ", value: "cha" },
{ id: "ဈ", value: "jha" },
{ id: "ည", value: "ñña" },
{ id: "ဌ", value: "ṭha" },
{ id: "ဎ", value: "ḍha" },
{ id: "ထ", value: "tha" },
{ id: "ဓ", value: "dha" },
{ id: "ဖ", value: "pha" },
{ id: "ဘ", value: "bha" },
{ id: "က", value: "ka" },
{ id: "ဂ", value: "ga" },
{ id: "စ", value: "ca" },
{ id: "ဇ", value: "ja" },
{ id: "ဉ", value: "ña" },
{ id: "ဠ", value: "ḷa" },
{ id: "ဋ", value: "ṭa" },
{ id: "ဍ", value: "ḍa" },
{ id: "ဏ", value: "ṇa" },
{ id: "တ", value: "ta" },
{ id: "ဒ", value: "da" },
{ id: "န", value: "na" },
{ id: "ဟ", value: "ha" },
{ id: "ပ", value: "pa" },
{ id: "ဗ", value: "ba" },
{ id: "မ", value: "ma" },
{ id: "ယ", value: "ya" },
{ id: "ရ", value: "ra" },
{ id: "႐", value: "ra" }, //后加
{ id: "လ", value: "la" },
{ id: "ဝ", value: "va" },
{ id: "သ", value: "sa" },
{ id: "aျ္", value: "ya" },
{ id: "aွ္", value: "va" },
{ id: "aြ္", value: "ra" },

{ id: "ၱ", value: "္ta" }, //后加
{ id: "ၳ", value: "္tha" }, //后加
{ id: "ၵ", value: "္da" }, //后加
{ id: "ၶ", value: "္dha" }, //后加

{ id: "ၬ", value: "္ṭa" }, //后加
{ id: "ၭ", value: "္ṭha" }, //后加

{ id: "ၠ", value: "္ka" }, //后加
{ id: "ၡ", value: "္kha" }, //后加
{ id: "ၢ", value: "္ga" }, //后加
{ id: "ၣ", value: "္gha" }, //后加

{ id: "ၸ", value: "္pa" }, //后加
{ id: "ၹ", value: "္pha" }, //后加
{ id: "ၺ", value: "္ba" }, //后加
{ id: "႓", value: "္bha" }, //后加

{ id: "ၥ", value: "္ca" }, //后加
{ id: "ၧ", value: "္cha" }, //后加
{ id: "ၨ", value: "္ja" }, //后加
{ id: "ၩ", value: "္jha" }, //后加

{ id: "်", value: "္ya" }, //后加
{ id: "ႅ", value: "္la" }, //后加
{ id: "ၼ", value: "္ma" }, //后加
{ id: "ွ", value: "္ha" }, //后加
{ id: "ႆ", value: "ssa" }, //后加
{ id: "ၷ", value: "na" }, //后加
{ id: "ၲ", value: "ta" }, //后加

{ id: "႒", value: "ṭṭha" }, //后加
{ id: "႗", value: "ṭṭa" }, //后加
{ id: "ၯ", value: "ḍḍha" }, //后加
{ id: "ၮ", value: "ḍḍa" }, //后加
{ id: "႑", value: "ṇḍa" }, //后加

{ id: "kaၤ", value: "ṅka" }, //后加
{ id: "gaၤ", value: "ṅga" }, //后加
{ id: "khaၤ", value: "ṅkha" }, //后加
{ id: "ghaၤ", value: "ṅgha" }, //后加

{ id: "aှ္", value: "ha" },
{ id: "aိံ", value: "iṃ" },
{ id: "aုံ", value: "uṃ" },
{ id: "aော", value: "o" },
{ id: "aေါ", value: "o" },
{ id: "aအံ", value: "aṃ" },
{ id: "aဣံ", value: "iṃ" },
{ id: "aဥံ", value: "uṃ" },
{ id: "aံ", value: "aṃ" },
{ id: "aာ", value: "ā" },
{ id: "aါ", value: "ā" },
{ id: "aိ", value: "i" },
{ id: "aီ", value: "ī" },
{ id: "aု", value: "u" },
{ id: "aဳ", value: "u" }, //後加
{ id: "aူ", value: "ū" },
{ id: "aေ", value: "e" },
{ id: "အါ", value: "ā" },
{ id: "အာ", value: "ā" },
{ id: "အ", value: "a" },
{ id: "ဣ", value: "i" },
{ id: "ဤ", value: "ī" },
{ id: "ဥ", value: "u" },
{ id: "ဦ", value: "ū" },
{ id: "ဧ", value: "e" },
{ id: "ဩ", value: "o" },
{ id: "ႏ", value: "n" }, //後加
{ id: "ၪ", value: "ñ" }, //後加
{ id: "a္", value: "" }, //後加
{ id: "္", value: "" }, //後加

{ id: "ေss", value: "sse" }, //后加
{ id: "ေkh", value: "khe" }, //后加
{ id: "ေgh", value: "ghe" }, //后加
{ id: "ေch", value: "che" }, //后加
{ id: "ေjh", value: "jhe" }, //后加
{ id: "ေññ", value: "ññe" }, //后加
{ id: "ေṭh", value: "ṭhe" }, //后加
{ id: "ေḍh", value: "ḍhe" }, //后加
{ id: "ေth", value: "the" }, //后加
{ id: "ေdh", value: "dhe" }, //后加
{ id: "ေph", value: "phe" }, //后加
{ id: "ေbh", value: "bhe" }, //后加
{ id: "ေk", value: "ke" }, //后加
{ id: "ေg", value: "ge" }, //后加
{ id: "ေc", value: "ce" }, //后加
{ id: "ေj", value: "je" }, //后加
{ id: "ေñ", value: "ñe" }, //后加
{ id: "ေḷ", value: "ḷe" }, //后加
{ id: "ေṭ", value: "ṭe" }, //后加
{ id: "ေḍ", value: "ḍe" }, //后加
{ id: "ေṇ", value: "ṇe" }, //后加
{ id: "ေt", value: "te" }, //后加
{ id: "ေd", value: "de" }, //后加
{ id: "ေn", value: "ne" }, //后加
{ id: "ေh", value: "he" }, //后加
{ id: "ေp", value: "pe" }, //后加
{ id: "ေb", value: "be" }, //后加
{ id: "ေm", value: "me" }, //后加
{ id: "ေy", value: "ye" }, //后加
{ id: "ေr", value: "re" }, //后加
{ id: "ေl", value: "le" }, //后加
{ id: "ေv", value: "ve" }, //后加
{ id: "ေs", value: "se" }, //后加
{ id: "ေy", value: "ye" }, //后加
{ id: "ေv", value: "ve" }, //后加
{ id: "ေr", value: "re" }, //后加

{ id: "ea", value: "e" }, //后加
{ id: "eā", value: "o" }, //后加

{ id: "၁", value: "1" },
{ id: "၂", value: "2" },
{ id: "၃", value: "3" },
{ id: "၄", value: "4" },
{ id: "၅", value: "5" },
{ id: "၆", value: "6" },
{ id: "၇", value: "7" },
{ id: "၈", value: "8" },
{ id: "၉", value: "9" },
{ id: "၀", value: "0" },
{ id: "း", value: "”" },
{ id: "့", value: "’" },
{ id: "။", value: "．" },
{ id: "၊", value: "，" },
];

var char_tai_old_to_r = [
	{ id: "ํ", value: "ฺaṃ" },
	{ id: "ิํ", value: "ฺiṃ" },
	{ id: "ุํ", value: "ฺuṃ" },
	{ id: "า", value: "ฺā" },
	{ id: "ิ", value: "ฺi" },
	{ id: "ี", value: "ฺī" },
	{ id: "ุ", value: "ฺu" },
	{ id: "ู", value: "ฺū" },

	{ id: "เข", value: "khe" },
	{ id: "เฃ", value: "ghe" },
	{ id: "เฉ", value: "che" },
	{ id: "เณ", value: "jhe" },
	{ id: "เฐ", value: "ṭhe" },
	{ id: "เฒ", value: "ḍhe" },
	{ id: "เถ", value: "the" },
	{ id: "เธ", value: "dhe" },
	{ id: "เผ", value: "phe" },
	{ id: "เภ", value: "bhe" },
	{ id: "โข", value: "kho" },
	{ id: "โฃ", value: "gho" },
	{ id: "โฉ", value: "cho" },
	{ id: "โณ", value: "jho" },
	{ id: "โฐ", value: "ṭho" },
	{ id: "โฒ", value: "ḍho" },
	{ id: "โถ", value: "tho" },
	{ id: "โธ", value: "dho" },
	{ id: "โผ", value: "pho" },
	{ id: "โภ", value: "bho" },
	{ id: "เก", value: "ke" },
	{ id: "เค", value: "ge" },
	{ id: "เจ", value: "ce" },
	{ id: "เช", value: "je" },
	{ id: "เญ", value: "ñe" },
	{ id: "เฬ", value: "ḷe" },
	{ id: "เฏ", value: "ṭe" },
	{ id: "เฑ", value: "ḍe" },
	{ id: "เฌ", value: "ṇe" },
	{ id: "เต", value: "te" },
	{ id: "เท", value: "de" },
	{ id: "เน", value: "ne" },
	{ id: "เบ", value: "pe" },
	{ id: "เพ", value: "be" },
	{ id: "เม", value: "me" },
	{ id: "เล", value: "le" },
	{ id: "เส", value: "se" },
	{ id: "เง", value: "ṅe" },
	{ id: "เห", value: "he" },
	{ id: "เย", value: "ye" },
	{ id: "เร", value: "re" },
	{ id: "เว", value: "ve" },
	{ id: "โก", value: "ko" },
	{ id: "โค", value: "go" },
	{ id: "โจ", value: "co" },
	{ id: "โช", value: "jo" },
	{ id: "โญ", value: "ño" },
	{ id: "โฬ", value: "ḷo" },
	{ id: "โฏ", value: "ṭo" },
	{ id: "โฑ", value: "ḍo" },
	{ id: "โฌ", value: "ṇo" },
	{ id: "โต", value: "to" },
	{ id: "โท", value: "do" },
	{ id: "โน", value: "no" },
	{ id: "โบ", value: "po" },
	{ id: "โพ", value: "bo" },
	{ id: "โม", value: "mo" },
	{ id: "โล", value: "lo" },
	{ id: "โส", value: "so" },
	{ id: "โง", value: "ṅo" },
	{ id: "โห", value: "ho" },
	{ id: "โย", value: "yo" },
	{ id: "โร", value: "ro" },
	{ id: "โว", value: "vo" },
	{ id: "ขฺ", value: "kh" },
	{ id: "ฃฺ", value: "gh" },
	{ id: "ฉฺ", value: "ch" },
	{ id: "ณฺ", value: "jh" },
	{ id: "ฐฺ", value: "ṭh" },
	{ id: "ฒฺ", value: "ḍh" },
	{ id: "ถฺ", value: "th" },
	{ id: "ธฺ", value: "dh" },
	{ id: "ผฺ", value: "ph" },
	{ id: "ภฺ", value: "bh" },
	{ id: "กฺ", value: "k" },
	{ id: "คฺ", value: "g" },
	{ id: "จฺ", value: "c" },
	{ id: "ชฺ", value: "j" },
	{ id: "ญฺ", value: "ñ" },
	{ id: "ฬฺ", value: "ḷ" },
	{ id: "ฏฺ", value: "ṭ" },
	{ id: "ฑฺ", value: "ḍ" },
	{ id: "ฌฺ", value: "ṇ" },
	{ id: "ตฺ", value: "t" },
	{ id: "ทฺ", value: "d" },
	{ id: "นฺ", value: "n" },
	{ id: "บฺ", value: "p" },
	{ id: "พฺ", value: "b" },
	{ id: "มฺ", value: "m" },
	{ id: "ลฺ", value: "l" },
	{ id: "สฺ", value: "s" },
	{ id: "งฺ", value: "ṅ" },
	{ id: "หฺ", value: "h" },
	{ id: "ยฺ", value: "y" },
	{ id: "รฺ", value: "r" },
	{ id: "วฺ", value: "v" },

	{ id: "ข", value: "kha" },
	{ id: "ฃ", value: "gha" },
	{ id: "ฉ", value: "cha" },
	{ id: "ณ", value: "jha" },
	{ id: "ฐ", value: "ṭha" },
	{ id: "ฒ", value: "ḍha" },
	{ id: "ถ", value: "tha" },
	{ id: "ธ", value: "dha" },
	{ id: "ผ", value: "pha" },
	{ id: "ภ", value: "bha" },
	{ id: "ก", value: "ka" },
	{ id: "ค", value: "ga" },
	{ id: "จ", value: "ca" },
	{ id: "ช", value: "ja" },
	{ id: "ญ", value: "ña" },
	{ id: "ฬ", value: "ḷa" },
	{ id: "ฏ", value: "ṭa" },
	{ id: "ฑ", value: "ḍa" },
	{ id: "ฌ", value: "ṇa" },
	{ id: "ต", value: "ta" },
	{ id: "ท", value: "da" },
	{ id: "น", value: "na" },
	{ id: "บ", value: "pa" },
	{ id: "พ", value: "ba" },
	{ id: "ม", value: "ma" },
	{ id: "ล", value: "la" },
	{ id: "ส", value: "sa" },
	{ id: "ง", value: "ṅa" },
	{ id: "ห", value: "ha" },
	{ id: "ย", value: "ya" },
	{ id: "ร", value: "ra" },
	{ id: "ว", value: "va" },

	{ id: "อํ", value: "aṃ" },
	{ id: "อิํ", value: "iṃ" },
	{ id: "อุํ", value: "uṃ" },
	{ id: "อ", value: "a" },
	{ id: "อา", value: "ā" },
	{ id: "อิ", value: "i" },
	{ id: "อี", value: "ī" },
	{ id: "อุ", value: "u" },
	{ id: "อู", value: "ū" },
	{ id: "เอ", value: "e" },
	{ id: "โอ", value: "o" },
	{ id: "eฺā", value: "o" },
	{ id: "aฺ", value: "" },
];

function roman_to_tai(input) {

let txt = input.toLowerCase();

try {
for (r_to_t_i in char_roman_to_tai) {
eval("txt=txt.replace(/" + char_roman_to_tai[r_to_t_i].id + "/g,char_roman_to_tai[r_to_t_i].value);");
}
} catch (err) {
//error
alert(err.message);
}
return txt;
}