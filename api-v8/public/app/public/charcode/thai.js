var char_roman_to_thai = [
	{ id: "khe", value: "เข" },
	{ id: "ghe", value: "เฃ" },
	{ id: "che", value: "เฉ" },
	{ id: "jhe", value: "เณ" },
	{ id: "ṭhe", value: "เฐ" },
	{ id: "ḍhe", value: "เฒ" },
	{ id: "the", value: "เถ" },
	{ id: "dhe", value: "เธ" },
	{ id: "phe", value: "เผ" },
	{ id: "bhe", value: "เภ" },
	{ id: "kho", value: "โข" },
	{ id: "gho", value: "โฃ" },
	{ id: "cho", value: "โฉ" },
	{ id: "jho", value: "โณ" },
	{ id: "ṭho", value: "โฐ" },
	{ id: "ḍho", value: "โฒ" },
	{ id: "tho", value: "โถ" },
	{ id: "dho", value: "โธ" },
	{ id: "pho", value: "โผ" },
	{ id: "bho", value: "โภ" },
	{ id: "ke", value: "เก" },
	{ id: "ge", value: "เค" },
	{ id: "ce", value: "เจ" },
	{ id: "je", value: "เช" },
	{ id: "ñe", value: "เญ" },
	{ id: "ḷe", value: "เฬ" },
	{ id: "ṭe", value: "เฏ" },
	{ id: "ḍe", value: "เฑ" },
	{ id: "ṇe", value: "เฌ" },
	{ id: "te", value: "เต" },
	{ id: "de", value: "เท" },
	{ id: "ne", value: "เน" },
	{ id: "pe", value: "เบ" },
	{ id: "be", value: "เพ" },
	{ id: "me", value: "เม" },
	{ id: "le", value: "เล" },
	{ id: "se", value: "เส" },
	{ id: "ṅe", value: "เง" },
	{ id: "he", value: "เห" },
	{ id: "ye", value: "เย" },
	{ id: "re", value: "เร" },
	{ id: "ve", value: "เว" },
	{ id: "ko", value: "โก" },
	{ id: "go", value: "โค" },
	{ id: "co", value: "โจ" },
	{ id: "jo", value: "โช" },
	{ id: "ño", value: "โญ" },
	{ id: "ḷo", value: "โฬ" },
	{ id: "ṭo", value: "โฏ" },
	{ id: "ḍo", value: "โฑ" },
	{ id: "ṇo", value: "โฌ" },
	{ id: "to", value: "โต" },
	{ id: "do", value: "โท" },
	{ id: "no", value: "โน" },
	{ id: "po", value: "โบ" },
	{ id: "bo", value: "โพ" },
	{ id: "mo", value: "โม" },
	{ id: "lo", value: "โล" },
	{ id: "so", value: "โส" },
	{ id: "ṅo", value: "โง" },
	{ id: "ho", value: "โห" },
	{ id: "yo", value: "โย" },
	{ id: "ro", value: "โร" },
	{ id: "vo", value: "โว" },
	{ id: "kh", value: "ขฺ" },
	{ id: "gh", value: "ฃฺ" },
	{ id: "ch", value: "ฉฺ" },
	{ id: "jh", value: "ณฺ" },
	{ id: "ṭh", value: "ฐฺ" },
	{ id: "ḍh", value: "ฒฺ" },
	{ id: "th", value: "ถฺ" },
	{ id: "dh", value: "ธฺ" },
	{ id: "ph", value: "ผฺ" },
	{ id: "bh", value: "ภฺ" },
	{ id: "k", value: "กฺ" },
	{ id: "g", value: "คฺ" },
	{ id: "c", value: "จฺ" },
	{ id: "j", value: "ชฺ" },
	{ id: "ñ", value: "ญฺ" },
	{ id: "ḷ", value: "ฬฺ" },
	{ id: "ṭ", value: "ฏฺ" },
	{ id: "ḍ", value: "ฑฺ" },
	{ id: "ṇ", value: "ฌฺ" },
	{ id: "t", value: "ตฺ" },
	{ id: "d", value: "ทฺ" },
	{ id: "n", value: "นฺ" },
	{ id: "p", value: "บฺ" },
	{ id: "b", value: "พฺ" },
	{ id: "m", value: "มฺ" },
	{ id: "l", value: "ลฺ" },
	{ id: "s", value: "สฺ" },
	{ id: "ṅ", value: "งฺ" },
	{ id: "h", value: "หฺ" },
	{ id: "y", value: "ยฺ" },
	{ id: "r", value: "รฺ" },
	{ id: "v", value: "วฺ" },
	{ id: "ฺaṃ", value: "ํ" },
	{ id: "ฺiṃ", value: "ิํ" },
	{ id: "ฺuṃ", value: "ุํ" },
	{ id: "ฺā", value: "า" },
	{ id: "ฺi", value: "ิ" },
	{ id: "ฺī", value: "ี" },
	{ id: "ฺu", value: "ุ" },
	{ id: "ฺū", value: "ู" },
	{ id: "aṃ", value: "อํ" },
	{ id: "iṃ", value: "อิํ" },
	{ id: "uṃ", value: "อุํ" },
	{ id: "a", value: "อ" },
	{ id: "ā", value: "อา" },
	{ id: "i", value: "อิ" },
	{ id: "ī", value: "อี" },
	{ id: "u", value: "อุ" },
	{ id: "ū", value: "อู" },
	{ id: "e", value: "เอ" },
	{ id: "o", value: "โอ" },
	{ id: "ฺอ", value: "" },

];

var char_thai_to_roman = [
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

var char_tai_to_roman_2 = [
{ id: "ိၭ", value: "ၭိ" }, //後加
{ id: "ီၭ", value: "ၭီ" }, //後加
{ id: "ိၼ", value: "ၼိ" }, //後加
{ id: "ုၤ", value: "ၤု" }, //後加
{ id: "ၤ်", value: "်ၤ" }, //後加
{ id: "ိ်", value: "်ိ" }, //後加
{ id: "ိၬ", value: "ၬိ" }, //後加
{ id: "ိၧ", value: "ၧိ" }, //後加
{ id: "ိၰ", value: "ၰိ" }, //後加
{ id: "ိၱ", value: "ၱိ" }, //後加
{ id: "ိြ", value: "ြိ" }, //後加
{ id: "ိႇ", value: "ႇိ" }, //後加

{ id: "ာာ", value: "ာ" }, //後加
{ id: "ဳဳ", value: "ဳ" }, //後加
{ id: "ဳဴ", value: "ဳ" }, //後加
{ id: "ဴူ", value: "ူ" }, //後加
{ id: "ိီ", value: "ိ" }, //後加
{ id: "ူု", value: "ူ" }, //後加
{ id: "ုဳ", value: "ု" }, //後加
{ id: "ုု", value: "ု" }, //後加
{ id: "႓႓", value: "႓" }, //後加
{ id: "ီီ", value: "ီ" }, //後加
{ id: "ၡၡ", value: "ၡ" }, //後加
{ id: "ွွ", value: "ွ" }, //後加

{ id: "ႁႏၵ", value: "ndra" }, //後加
{ id: "ၿႏၵ", value: "ndra" }, //後加

{ id: "ၾတ", value: "tra" }, //後加
{ id: "ၾဒ", value: "dra" }, //後加
{ id: "ၾက", value: "kra" }, //後加
{ id: "ၾဂ", value: "gra" }, //後加
{ id: "ၾပ", value: "pra" }, //後加
{ id: "ၾဗ", value: "bra" }, //後加

{ id: "ႀတ", value: "tra" }, //後加
{ id: "ႀက", value: "kra" }, //後加
{ id: "ႀပ", value: "pra" }, //後加
{ id: "ႀဗ", value: "bra" }, //後加
{ id: "ႀဒ", value: "dra" }, //後加
{ id: "ႀဂ", value: "gra" }, //後加

{ id: "ျတ", value: "tra" }, //後加
{ id: "ျက", value: "kra" }, //後加
{ id: "ျပ", value: "pra" }, //後加
{ id: "ျဗ", value: "bra" }, //後加
{ id: "ျဒ", value: "dra" }, //後加
{ id: "ျဂ", value: "gra" }, //後加

{ id: "ၿတ", value: "tra" }, //後加
{ id: "ၿက", value: "kra" }, //後加
{ id: "ၿပ", value: "pra" }, //後加
{ id: "ၿဗ", value: "bra" }, //後加
{ id: "ၿဒ", value: "dra" }, //後加
{ id: "ၿဂ", value: "gra" }, //後加

{ id: "႖", value: "tva" }, //後加
{ id: "ဗ်", value: "bya" }, //後加
{ id: "ဝ်", value: "vya" }, //後加

{ id: "ဥႇ", value: "ñha" }, //後加
{ id: "သၷ", value: "sna" }, //後加
{ id: "ၥ်", value: "jha" }, //後加

{ id: "ဒြ", value: "dva" }, //後加

{ id: "ခ᩠", value: "kh" },
{ id: "ဃ᩠", value: "gh" },
{ id: "ဆ᩠", value: "ch" },
{ id: "ဈ᩠", value: "jh" },
{ id: "ည᩠", value: "ññ" },
{ id: "ဌ᩠", value: "ṭh" },
{ id: "ဎ᩠", value: "ḍh" },
{ id: "ထ᩠", value: "th" },
{ id: "ဓ᩠", value: "dh" },
{ id: "ဖ᩠", value: "ph" },
{ id: "ဘ᩠", value: "bh" },
{ id: "က᩠", value: "k" },
{ id: "ဂ᩠", value: "g" },
{ id: "စ᩠", value: "c" },
{ id: "ဇ᩠", value: "j" },
{ id: "ဉ᩠", value: "ñ" },
{ id: "ဠ᩠", value: "ḷ" },
{ id: "ဋ᩠", value: "ṭ" },
{ id: "ဍ᩠", value: "ḍ" },
{ id: "ဏ᩠", value: "ṇ" },
{ id: "တ᩠", value: "t" },
{ id: "ဒ᩠", value: "d" },
{ id: "န᩠", value: "n" },
{ id: "ဟ᩠", value: "h" },
{ id: "ပ᩠", value: "p" },
{ id: "ဗ᩠", value: "b" },
{ id: "မ᩠", value: "m" },
{ id: "ယ᩠", value: "y" },
{ id: "ရ᩠", value: "r" },
{ id: "လ᩠", value: "l" },
{ id: "ဝ᩠", value: "v" },
{ id: "သ᩠", value: "s" },
{ id: "င᩠", value: "ṅ" },
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

{ id: "ီႇ", value: "ႇီ" }, //后加
{ id: "ိႇ", value: "ႇိ" }, //后加

{ id: "ျ", value: "᩠ra" }, //后加
{ id: "ွ", value: "᩠ha" }, //后加
{ id: "ှ", value: "᩠ha" }, //后加
{ id: "ႇ", value: "᩠ha" }, //后加
{ id: "ြ", value: "᩠va" }, //后加

{ id: "ၱ", value: "᩠ta" }, //后加
{ id: "ၳ", value: "᩠tha" }, //后加
{ id: "ၵ", value: "᩠da" }, //后加
{ id: "ၶ", value: "᩠dha" }, //后加

{ id: "ၬ", value: "᩠ṭa" }, //后加
{ id: "ၭ", value: "᩠ṭha" }, //后加

{ id: "ၠ", value: "᩠ka" }, //后加
{ id: "ၡ", value: "᩠kha" }, //后加
{ id: "ၢ", value: "᩠ga" }, //后加
{ id: "ၣ", value: "᩠gha" }, //后加

{ id: "ၸ", value: "᩠pa" }, //后加
{ id: "ၹ", value: "᩠pha" }, //后加
{ id: "ၺ", value: "᩠ba" }, //后加
{ id: "႓", value: "᩠bha" }, //后加

{ id: "ၥ", value: "᩠ca" }, //后加
{ id: "ၧ", value: "᩠cha" }, //后加
{ id: "ၨ", value: "᩠ja" }, //后加
{ id: "ၩ", value: "᩠jha" }, //后加

{ id: "်", value: "᩠ya" }, //后加
{ id: "ႅ", value: "᩠la" }, //后加
{ id: "ၼ", value: "᩠ma" }, //后加
{ id: "ွ", value: "᩠ha" }, //后加
{ id: "ၲ", value: "ta" }, //后加

{ id: "ႆ", value: "ssa" }, //后加
{ id: "ၫ", value: "ñña" }, //后加
{ id: "ၷ", value: "na" }, //后加

{ id: "႒", value: "ṭṭha" }, //后加
{ id: "႗", value: "ṭṭa" }, //后加
{ id: "ၯ", value: "ḍḍha" }, //后加
{ id: "ၮ", value: "ḍḍa" }, //后加
{ id: "႑", value: "ṇḍa" }, //后加
{ id: "ၴ", value: "tha" }, //后加

{ id: "ႍ", value: "ၤṃ" }, //後加

{ id: "kaၤ", value: "ṅka" }, //后加
{ id: "gaၤ", value: "ṅga" }, //后加
{ id: "khaၤ", value: "ṅkha" }, //后加
{ id: "ghaၤ", value: "ṅgha" }, //后加

{ id: "ိံ", value: "᩠iṃ" },
{ id: "ုံ", value: "᩠uṃ" },
{ id: "ံု", value: "᩠uṃ" }, //後加
{ id: "ံဳ", value: "᩠uṃ" }, //後加
{ id: "ော", value: "᩠o" },
{ id: "ေါ", value: "᩠o" },
{ id: "aအံ", value: "aṃ" },
{ id: "aဣံ", value: "iṃ" },
{ id: "ႎ", value: "᩠iṃ" }, //後加
{ id: "aဥံ", value: "uṃ" },
{ id: "ံ", value: "ṃ" },
{ id: "ိ", value: "᩠i" },
{ id: "ီ", value: "᩠ī" },
{ id: "ု", value: "᩠u" },
{ id: "ဳ", value: "᩠u" }, //後加
{ id: "ူ", value: "᩠ū" },
{ id: "ဴ", value: "᩠ū" },
//{ "id":"aေ" , "value":"E" },
{ id: "အါ", value: "ā" },
{ id: "အာ", value: "ā" },
{ id: "အ", value: "a" },
{ id: "ဣ", value: "i" },
{ id: "ဤ", value: "ī" },
{ id: "ဧ", value: "E" },
{ id: "ဥ", value: "u" },
{ id: "ဦ", value: "ū" },
{ id: "ဩ", value: "o" },
{ id: "ႏ", value: "n" }, //後加
{ id: "ၪ", value: "ñ" }, //後加
{ id: "ၰ", value: "᩠ṇa" }, //後加
{ id: "a᩠", value: "" }, //後加
{ id: "᩠", value: "" }, //後加

{ id: "kuၤ", value: "ṅku" }, //后加
{ id: "guၤ", value: "ṅgu" }, //后加
{ id: "khuၤ", value: "ṅkhu" }, //后加
{ id: "ghuၤ", value: "ṅghu" }, //后加

{ id: "kyaၤ", value: "ṅkya" }, //后加
{ id: "gyaၤ", value: "ṅgya" }, //后加
{ id: "khyaၤ", value: "ṅkhya" }, //后加
{ id: "ghyaၤ", value: "ṅghya" }, //后加

{ id: "kaႌ", value: "ṅkī" }, //后加
{ id: "gaႌ", value: "ṅgī" }, //后加
{ id: "khaႌ", value: "ṅkhī" }, //后加
{ id: "ghaႌ", value: "ṅghī" }, //后加

{ id: "kaႋ", value: "ṅki" }, //后加
{ id: "gaႋ", value: "ṅgi" }, //后加
{ id: "khaႋ", value: "ṅkhi" }, //后加
{ id: "ghaႋ", value: "ṅghi" }, //后加

{ id: "ာ", value: "᩠ā" },
{ id: "ါ", value: "᩠ā" },
{ id: "a᩠", value: "" }, //後加

{ id: "ေṅkhy", value: "ṅkhyE" }, //后加
{ id: "ေṅghy", value: "ṅghyE" }, //后加
{ id: "ေṅky", value: "ṅkyE" }, //后加
{ id: "ေṅgy", value: "ṅgyE" }, //后加

{ id: "ေndr", value: "ndrE" }, //後加
{ id: "ေntr", value: "ntrE" }, //後加
{ id: "ေdr", value: "drE" }, //後加
{ id: "ေtr", value: "trE" }, //後加
{ id: "ေkr", value: "krE" }, //後加
{ id: "ေgr", value: "grE" }, //後加
{ id: "ေbr", value: "brE" }, //後加

{ id: "ေky", value: "kyE" }, //後加
{ id: "ေby", value: "byE" }, //後加
{ id: "ေṇy", value: "ṇyE" }, //後加
{ id: "ေby", value: "byE" }, //後加

{ id: "ေtv", value: "tvE" }, //後加
{ id: "ေdv", value: "dvE" }, //後加
{ id: "ေsv", value: "svE" }, //後加
{ id: "ေnv", value: "nvE" }, //後加

{ id: "ေḷh", value: "ḷhE" }, //後加
{ id: "ေlh", value: "lhE" }, //後加
{ id: "ေṇh", value: "ṇhE" }, //後加
{ id: "ေñh", value: "ñhE" }, //後加
{ id: "ေmh", value: "mhE" }, //後加

{ id: "ေsn", value: "snE" }, //後加
{ id: "ေvh", value: "vhE" }, //後加
{ id: "ေpl", value: "plE" }, //後加
{ id: "ေkl", value: "klE" }, //後加

{ id: "ေṅkh", value: "ṅkhE" }, //后加
{ id: "ေṅgh", value: "ṅghE" }, //后加
{ id: "ေṅk", value: "ṅkE" }, //后加
{ id: "ေṅg", value: "ṅgE" }, //后加

{ id: "ေmph", value: "mphE" }, //后加
{ id: "ေmbh", value: "mbhE" }, //后加
{ id: "ေmp", value: "mpE" }, //后加
{ id: "ေmb", value: "mbE" }, //后加

{ id: "ေnth", value: "nthE" }, //后加
{ id: "ေndh", value: "ndhE" }, //后加
{ id: "ေnt", value: "ntE" }, //后加
{ id: "ေnd", value: "ndE" }, //后加

{ id: "ေṇṭh", value: "ṇṭhE" }, //后加
{ id: "ေṇḍh", value: "ṇḍhE" }, //后加
{ id: "ေṇṭ", value: "ṇṭE" }, //后加
{ id: "ေṇḍ", value: "ṇḍE" }, //后加

{ id: "ေñch", value: "ñchE" }, //后加
{ id: "ေñjh", value: "ñjhE" }, //后加
{ id: "ေñc", value: "ñcE" }, //后加
{ id: "ေñj", value: "ñjE" }, //后加

{ id: "ေss", value: "ssE" }, //后加
{ id: "ေkkh", value: "kkhE" }, //后加
{ id: "ေggh", value: "gghE" }, //后加
{ id: "ေcch", value: "cchE" }, //后加
{ id: "ေjjh", value: "jjhE" }, //后加
{ id: "ေññ", value: "ññE" }, //后加
{ id: "ေṭṭh", value: "ṭṭhE" }, //后加
{ id: "ေḍḍh", value: "ḍḍhE" }, //后加
{ id: "ေtth", value: "tthE" }, //后加
{ id: "ေddh", value: "ddhE" }, //后加
{ id: "ေpph", value: "pphE" }, //后加
{ id: "ေbbh", value: "bbhE" }, //后加
{ id: "ေkk", value: "kkE" }, //后加
{ id: "ေgg", value: "ggE" }, //后加
{ id: "ေcc", value: "ccE" }, //后加
{ id: "ေjj", value: "jjE" }, //后加
{ id: "ေḷḷ", value: "ḷḷE" }, //后加
{ id: "ေṭṭ", value: "ṭṭE" }, //后加
{ id: "ေḍḍ", value: "ḍḍE" }, //后加
{ id: "ေṇṇ", value: "ṇṇE" }, //后加
{ id: "ေtt", value: "ttE" }, //后加
{ id: "ေdd", value: "ddE" }, //后加
{ id: "ေnn", value: "nnE" }, //后加
{ id: "ေpp", value: "ppE" }, //后加
{ id: "ေbb", value: "bbE" }, //后加
{ id: "ေmm", value: "mmE" }, //后加
{ id: "ေyy", value: "yyE" }, //后加
{ id: "ေll", value: "llE" }, //后加

{ id: "ေkh", value: "khE" }, //后加
{ id: "ေgh", value: "ghE" }, //后加
{ id: "ေch", value: "chE" }, //后加
{ id: "ေjh", value: "jhE" }, //后加
{ id: "ေṭh", value: "ṭhE" }, //后加
{ id: "ေḍh", value: "ḍhE" }, //后加
{ id: "ေth", value: "thE" }, //后加
{ id: "ေdh", value: "dhE" }, //后加
{ id: "ေph", value: "phE" }, //后加
{ id: "ေbh", value: "bhE" }, //后加
{ id: "ေk", value: "kE" }, //后加
{ id: "ေg", value: "gE" }, //后加
{ id: "ေc", value: "cE" }, //后加
{ id: "ေj", value: "jE" }, //后加
{ id: "ေñ", value: "ñE" }, //后加
{ id: "ေḷ", value: "ḷE" }, //后加
{ id: "ေṭ", value: "ṭE" }, //后加
{ id: "ေḍ", value: "ḍE" }, //后加
{ id: "ေṇ", value: "ṇE" }, //后加
{ id: "ေt", value: "tE" }, //后加
{ id: "ေd", value: "dE" }, //后加
{ id: "ေn", value: "nE" }, //后加
{ id: "ေh", value: "hE" }, //后加
{ id: "ေp", value: "pE" }, //后加
{ id: "ေb", value: "bE" }, //后加
{ id: "ေm", value: "mE" }, //后加
{ id: "ေy", value: "yE" }, //后加
{ id: "ေr", value: "rE" }, //后加
{ id: "ေl", value: "lE" }, //后加
{ id: "ေv", value: "vE" }, //后加
{ id: "ေs", value: "sE" }, //后加
{ id: "ေy", value: "yE" }, //后加
{ id: "ေv", value: "vE" }, //后加
{ id: "ေr", value: "rE" }, //后加

{ id: "Ea", value: "e" }, //后加
{ id: "Eā", value: "o" }, //后加
{ id: "E", value: "e" }, //后加
//{ "id":"ūu" , "value":"ū" },//后加
//{ "id":"uū" , "value":"ū" },//后加

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

function roman_to_tai(input) {
let txt = input;

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
