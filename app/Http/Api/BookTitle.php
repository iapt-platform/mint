<?php
namespace App\Http\Api;

class BookTitle{
    //缅文书名缩写映射表
    public static function my(){
        return [
  [
    "title1" => "abhi，dha",
    "title2" => "အဘိ၊ဓ",
    "bookname" => "dhammasaṅgaṇīpāḷi",
    "volume" => 0
  ],
  [
    "title1" => "abhi，ka",
    "title2" => "အဘိ၊က",
    "bookname" => "kathāvatthupāḷi",
    "volume" => 0
  ],
  [
    "title1" => "abhi，pu",
    "title2" => "အဘိ၊ပု",
    "bookname" => "puggalapaññattipāḷi",
    "volume" => 0
  ],
  [
    "title1" => "abhi，ṭṭha，1",
    "title2" => "အဘိ၊ဋ္ဌ၊၁",
    "bookname" => "dhammasaṅgaṇī-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "abhi，ṭṭha，2",
    "title2" => "အဘိ၊ဋ္ဌ၊၂",
    "bookname" => "vibhaṅga-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "abhi，ṭṭha，3",
    "title2" => "အဘိ၊ဋ္ဌ၊၃",
    "bookname" => "pañcapakaraṇa-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "abhi，vi",
    "title2" => "အဘိ၊ဝိ",
    "bookname" => "vibhaṅgapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "aṃ，1",
    "title2" => "အံ၊၁",
    "bookname" => "aṅguttaranikāya",
    "volume" => 1
  ],
  [
    "title1" => "aṃ，2",
    "title2" => "အံ၊၂",
    "bookname" => "aṅguttaranikāya",
    "volume" => 2
  ],
  [
    "title1" => "aṃ，3",
    "title2" => "အံ၊၃",
    "bookname" => "aṅguttaranikāya",
    "volume" => 3
  ],
  [
    "title1" => "aṃ，ṭī，1",
    "title2" => "အံ၊ဋီ၊၁",
    "bookname" => "aṅguttaranikāya-ṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "aṃ，ṭī，2",
    "title2" => "အံ၊ဋီ၊၂",
    "bookname" => "aṅguttaranikāya-ṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "aṃ，ṭī，3",
    "title2" => "အံ၊ဋီ၊၃",
    "bookname" => "aṅguttaranikāya-ṭīkā",
    "volume" => 3
  ],
  [
    "title1" => "aṃ，ṭṭha，1",
    "title2" => "အံ၊ဋ္ဌ၊၁",
    "bookname" => "aṅguttaranikāya-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "aṃ，ṭṭha，2",
    "title2" => "အံ၊ဋ္ဌ၊၂",
    "bookname" => "aṅguttaranikāya-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "aṃ，ṭṭha，3",
    "title2" => "အံ၊ဋ္ဌ၊၃",
    "bookname" => "aṅguttaranikāya-aṭṭhakathā",
    "volume" => 3
  ],
  [
    "title1" => "anuṭī，2",
    "title2" => "အနုဋီ၊၂",
    "bookname" => "vibhaṅga-anuṭīkā",
    "volume" => 0
  ],
  [
    "title1" => "anuṭī，3",
    "title2" => "အနုဋီ၊၃",
    "bookname" => "pañcapakaraṇa-anuṭīkā",
    "volume" => 0
  ],
  [
    "title1" => "apa，1",
    "title2" => "အပ၊၁",
    "bookname" => "therāpadānapāḷi",
    "volume" => 1
  ],
  [
    "title1" => "apa，2",
    "title2" => "အပ၊၂",
    "bookname" => "therāpadānapāḷi",
    "volume" => 2
  ],
  [
    "title1" => "apa，ṭṭha，1",
    "title2" => "အပ၊ဋ္ဌ၊၁",
    "bookname" => "apadāna-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "apa，ṭṭha，2",
    "title2" => "အပ၊ဋ္ဌ၊၂",
    "bookname" => "apadāna-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "buddhavaṃ，ṭṭha",
    "title2" => "ဗုဒ္ဓဝံ၊ဋ္ဌ",
    "bookname" => "buddhavaṃsa-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "cariyā，ṭṭha",
    "title2" => "စရိယာ၊ဋ္ဌ",
    "bookname" => "cariyāpiṭaka-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "cūḷani",
    "title2" => "စူဠနိ",
    "bookname" => "cūḷaniddesapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "cūḷani，ṭṭha",
    "title2" => "စူဠနိ၊ဋ္ဌ",
    "bookname" => "cūḷaniddesa-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "dhamma",
    "title2" => "ဓမ္မ",
    "bookname" => "dhammapadapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "dhamma，ṭṭha，1",
    "title2" => "ဓမ္မ၊ဋ္ဌ၊၁",
    "bookname" => "dhammapada-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "dhamma，ṭṭha，2",
    "title2" => "ဓမ္မ၊ဋ္ဌ၊၂",
    "bookname" => "dhammapada-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "dī，1",
    "title2" => "ဒီ၊၁",
    "bookname" => "dīghanikāya",
    "volume" => 1
  ],
  [
    "title1" => "dī，2",
    "title2" => "ဒီ၊၂",
    "bookname" => "dīghanikāya",
    "volume" => 2
  ],
  [
    "title1" => "dī，3",
    "title2" => "ဒီ၊၃",
    "bookname" => "dīghanikāya",
    "volume" => 3
  ],
  [
    "title1" => "dī，ṭī，1",
    "title2" => "ဒီ၊ဋီ၊၁",
    "bookname" => "dīghanikāya-ṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "dī，ṭī，2",
    "title2" => "ဒီ၊ဋီ၊၂",
    "bookname" => "dīghanikāya-ṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "dī，ṭī，3",
    "title2" => "ဒီ၊ဋီ၊၃",
    "bookname" => "dīghanikāya-ṭīkā",
    "volume" => 3
  ],
  [
    "title1" => "dī，ṭṭha，1",
    "title2" => "ဒီ၊ဋ္ဌ၊၁",
    "bookname" => "dīghanikāya-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "dī，ṭṭha，2",
    "title2" => "ဒီ၊ဋ္ဌ၊၂",
    "bookname" => "dīghanikāya-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "dī，ṭṭha，3",
    "title2" => "ဒီ၊ဋ္ဌ၊၃",
    "bookname" => "dīghanikāya-aṭṭhakathā",
    "volume" => 3
  ],
  [
    "title1" => "itivuta，ṭṭha",
    "title2" => "ဣတိဝုတ၊ဋ္ဌ",
    "bookname" => "itivuttaka-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "jā，1",
    "title2" => "ဇာ၊၁",
    "bookname" => "jātakapāḷi",
    "volume" => 1
  ],
  [
    "title1" => "jā，2",
    "title2" => "ဇာ၊၂",
    "bookname" => "jātakapāḷi",
    "volume" => 2
  ],
  [
    "title1" => "jā，ṭṭha，1",
    "title2" => "ဇာ၊ဋ္ဌ၊၁",
    "bookname" => "jātaka-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "jā，ṭṭha，2",
    "title2" => "ဇာ၊ဋ္ဌ၊၂",
    "bookname" => "jātaka-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "jā，ṭṭha，3",
    "title2" => "ဇာ၊ဋ္ဌ၊၃",
    "bookname" => "jātaka-aṭṭhakathā",
    "volume" => 3
  ],
  [
    "title1" => "jā，ṭṭha，4",
    "title2" => "ဇာ၊ဋ္ဌ၊၄",
    "bookname" => "jātaka-aṭṭhakathā",
    "volume" => 4
  ],
  [
    "title1" => "jā，ṭṭha，5",
    "title2" => "ဇာ၊ဋ္ဌ၊၅",
    "bookname" => "jātaka-aṭṭhakathā",
    "volume" => 5
  ],
  [
    "title1" => "jā，ṭṭha，6",
    "title2" => "ဇာ၊ဋ္ဌ၊၆",
    "bookname" => "jātaka-aṭṭhakathā",
    "volume" => 6
  ],
  [
    "title1" => "jā，ṭṭha，7",
    "title2" => "ဇာ၊ဋ္ဌ၊၇",
    "bookname" => "jātaka-aṭṭhakathā",
    "volume" => 7
  ],
  [
    "title1" => "jātaka，1",
    "title2" => "ဇာတက၊၁",
    "bookname" => "jātakapāḷi",
    "volume" => 1
  ],
  [
    "title1" => "jātaka，2",
    "title2" => "ဇာတက၊၂",
    "bookname" => "jātakapāḷi",
    "volume" => 2
  ],
  [
    "title1" => "khuddaka",
    "title2" => "ခုဒ္ဒက",
    "bookname" => "khuddakapāṭhapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "khuddaka，ṭṭha",
    "title2" => "ခုဒ္ဒက၊ဋ္ဌ",
    "bookname" => "khuddakapāṭha-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "ma，1",
    "title2" => "မ၊၁",
    "bookname" => "majjhimanikaya",
    "volume" => 1
  ],
  [
    "title1" => "ma，2",
    "title2" => "မ၊၂",
    "bookname" => "majjhimanikaya",
    "volume" => 2
  ],
  [
    "title1" => "ma，3",
    "title2" => "မ၊၃",
    "bookname" => "majjhimanikaya",
    "volume" => 3
  ],
  [
    "title1" => "ma，ṭī，1",
    "title2" => "မ၊ဋီ၊၁",
    "bookname" => "majjhimanikaya-ṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "ma，ṭī，2",
    "title2" => "မ၊ဋီ၊၂",
    "bookname" => "majjhimanikaya-ṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "ma，ṭī，3",
    "title2" => "မ၊ဋီ၊၃",
    "bookname" => "majjhimanikaya-ṭīkā",
    "volume" => 3
  ],
  [
    "title1" => "ma，ṭṭha，1",
    "title2" => "မ၊ဋ္ဌ၊၁",
    "bookname" => "majimanikaya-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "ma，ṭṭha，2",
    "title2" => "မ၊ဋ္ဌ၊၂",
    "bookname" => "majimanikaya-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "ma，ṭṭha，3",
    "title2" => "မ၊ဋ္ဌ၊၃",
    "bookname" => "majimanikaya-aṭṭhakathā",
    "volume" => 3
  ],
  [
    "title1" => "ma，ṭṭha，4",
    "title2" => "မ၊ဋ္ဌ၊၄",
    "bookname" => "majimanikaya-aṭṭhakathā",
    "volume" => 4
  ],
  [
    "title1" => "mahāni",
    "title2" => "မဟာနိ",
    "bookname" => "mahāniddesapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "mahāni，ṭṭha",
    "title2" => "မဟာနိ၊ဋ္ဌ",
    "bookname" => "mahāniddesa-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "milinda",
    "title2" => "မိလိန္ဒ",
    "bookname" => "milindapañhapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "mūlaṭī，1",
    "title2" => "မူလဋီ၊၁",
    "bookname" => "dhammasaṅgaṇī-mūlaṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "mūlaṭī，2",
    "title2" => "မူလဋီ၊၂",
    "bookname" => "vibhaṅga-mūlaṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "netti",
    "title2" => "နေတ္တိ",
    "bookname" => "nettippakaraṇapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "netti，ṭṭha",
    "title2" => "နေတ္တိ၊ဋ္ဌ",
    "bookname" => "nettippakaraṇa-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "netti，vibhā",
    "title2" => "နေတ္တိ၊ဝိဘာ",
    "bookname" => "nettivibhāvinī",
    "volume" => 0
  ],
  [
    "title1" => "paṭisaṃ",
    "title2" => "ပဋိသံ",
    "bookname" => "paṭisambhidāmaggapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "paṭisaṃ，ṭṭha，1",
    "title2" => "ပဋိသံ၊ဋ္ဌ၊၁",
    "bookname" => "paṭisambhidāmagga-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "paṭisaṃ，ṭṭha，2",
    "title2" => "ပဋိသံ၊ဋ္ဌ၊၂",
    "bookname" => "paṭisambhidāmagga-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "paṭisambhidāmaga，pāḷi",
    "title2" => "ပဋိသမ္ဘိဒါမဂ်၊ပါဠိ",
    "bookname" => "paṭisambhidāmaggapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "paṭṭhāna，1",
    "title2" => "ပဋ္ဌာန၊၁",
    "bookname" => "paṭṭhānapāḷi",
    "volume" => 1
  ],
  [
    "title1" => "paṭṭhāna，2",
    "title2" => "ပဋ္ဌာန၊၂",
    "bookname" => "paṭṭhānapāḷi",
    "volume" => 2
  ],
  [
    "title1" => "paṭṭhāna，3",
    "title2" => "ပဋ္ဌာန၊၃",
    "bookname" => "paṭṭhānapāḷi",
    "volume" => 3
  ],
  [
    "title1" => "paṭṭhāna，4",
    "title2" => "ပဋ္ဌာန၊၄",
    "bookname" => "paṭṭhānapāḷi",
    "volume" => 4
  ],
  [
    "title1" => "peta，ṭṭha",
    "title2" => "ပေတ၊ဋ္ဌ",
    "bookname" => "petavatthupāḷi",
    "volume" => 0
  ],
  [
    "title1" => "peṭako",
    "title2" => "ပေဋကော",
    "bookname" => "peṭakopadesapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "saṃ，1",
    "title2" => "သံ၊၁",
    "bookname" => "saṃyuttanikāya",
    "volume" => 1
  ],
  [
    "title1" => "saṃ，2",
    "title2" => "သံ၊၂",
    "bookname" => "saṃyuttanikāya",
    "volume" => 2
  ],
  [
    "title1" => "saṃ，3",
    "title2" => "သံ၊၃",
    "bookname" => "saṃyuttanikāya",
    "volume" => 3
  ],
  [
    "title1" => "saṃ，ṭī，1",
    "title2" => "သံ၊ဋီ၊၁",
    "bookname" => "saṃyuttanikāya-ṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "saṃ，ṭī，2",
    "title2" => "သံ၊ဋီ၊၂",
    "bookname" => "saṃyuttanikāya-ṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "saṃ，ṭṭha，1",
    "title2" => "သံ၊ဋ္ဌ၊၁",
    "bookname" => "saṃyuttanikāya-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "saṃ，ṭṭha，2",
    "title2" => "သံ၊ဋ္ဌ၊၂",
    "bookname" => "saṃyuttanikāya-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "saṃ，ṭṭha，3",
    "title2" => "သံ၊ဋ္ဌ၊၃",
    "bookname" => "saṃyuttanikāya-aṭṭhakathā",
    "volume" => 3
  ],
  [
    "title1" => "sārattha，ṭī，1",
    "title2" => "သာရတ္ထ၊ဋီ၊၁",
    "bookname" => "sāratthadīpanī-ṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "sārattha，ṭī，2",
    "title2" => "သာရတ္ထ၊ဋီ၊၂",
    "bookname" => "sāratthadīpanī-ṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "sārattha，ṭī，3",
    "title2" => "သာရတ္ထ၊ဋီ၊၃",
    "bookname" => "sāratthadīpanī-ṭīkā",
    "volume" => 3
  ],
  [
    "title1" => "suttani，ṭṭha，1",
    "title2" => "သုတ္တနိ၊ဋ္ဌ၊၁",
    "bookname" => "suttanipāta-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "suttani，ṭṭha，2",
    "title2" => "သုတ္တနိ၊ဋ္ဌ၊၂",
    "bookname" => "suttanipāta-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "suttaniṭṭha，2",
    "title2" => "သုတ္တနိဋ္ဌ၊၂",
    "bookname" => "suttanipāta-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "thera，ṭṭha，1",
    "title2" => "ထေရ၊ဋ္ဌ၊၁",
    "bookname" => "theragāthā-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "thera，ṭṭha，2",
    "title2" => "ထေရ၊ဋ္ဌ၊၂",
    "bookname" => "theragāthā-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "theragāthā",
    "title2" => "ထေရဂါထာ",
    "bookname" => "theragāthāpāḷi",
    "volume" => 0
  ],
  [
    "title1" => "therī",
    "title2" => "ထေရီ",
    "bookname" => "therīgāthāpāḷi",
    "volume" => 0
  ],
  [
    "title1" => "therī，ṭṭha",
    "title2" => "ထေရီ၊ဋ္ဌ",
    "bookname" => "therīgāthā-aṭṭhakathā",
    "volume" => 0
  ],
  [ "title1" => "udāna", "title2" => "ဥဒါန", "bookname" => "udānapāḷi", "volume" => 0 ],
  [
    "title1" => "udāna，ṭṭha",
    "title2" => "ဥဒါန၊ဋ္ဌ",
    "bookname" => "udāna-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "vajira",
    "title2" => "ဝဇိရ",
    "bookname" => "vajirabuddhi-ṭīkā",
    "volume" => 0
  ],
  [
    "title1" => "vajira，ṭī",
    "title2" => "ဝဇိရ၊ဋီ",
    "bookname" => "vajirabuddhi-ṭīkā",
    "volume" => 0
  ],
  [
    "title1" => "vi，1",
    "title2" => "ဝိ၊၁",
    "bookname" => "pārājikapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "vi，2",
    "title2" => "ဝိ၊၂",
    "bookname" => "pācittiyapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "vi，3",
    "title2" => "ဝိ၊၃",
    "bookname" => "mahāvaggapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "vi，4",
    "title2" => "ဝိ၊၄",
    "bookname" => "cūḷavaggapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "vi，5",
    "title2" => "ဝိ၊၅",
    "bookname" => "parivārapāḷi",
    "volume" => 0
  ],
  [
    "title1" => "vi，ṭṭha，1",
    "title2" => "ဝိ၊ဋ္ဌ၊၁",
    "bookname" => "pārājikakaṇḍa-aṭṭhakathā",
    "volume" => 1
  ],
  [
    "title1" => "vi，ṭṭha，2",
    "title2" => "ဝိ၊ဋ္ဌ၊၂",
    "bookname" => "pārājikakaṇḍa-aṭṭhakathā",
    "volume" => 2
  ],
  [
    "title1" => "vi，ṭṭha，3",
    "title2" => "ဝိ၊ဋ္ဌ၊၃",
    "bookname" => "pācittiyādi-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "vi，ṭṭha，4",
    "title2" => "ဝိ၊ဋ္ဌ၊၄",
    "bookname" => "cūḷavaggādi-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "vimāna，ṭṭha",
    "title2" => "ဝိမာန၊ဋ္ဌ",
    "bookname" => "vimānavatthu-aṭṭhakathā",
    "volume" => 0
  ],
  [
    "title1" => "vimati，1",
    "title2" => "ဝိမတိ၊၁",
    "bookname" => "vimativinodanī-ṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "vimati，2",
    "title2" => "ဝိမတိ၊၂",
    "bookname" => "vimativinodanī-ṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "visuddhi，1",
    "title2" => "ဝိသုဒ္ဓိ၊၁",
    "bookname" => "visuddhimagga",
    "volume" => 1
  ],
  [
    "title1" => "visuddhi，2",
    "title2" => "ဝိသုဒ္ဓိ၊၂",
    "bookname" => "visuddhimagga",
    "volume" => 2
  ],
  [
    "title1" => "visuddhi，ṭī，1",
    "title2" => "ဝိသုဒ္ဓိ၊ဋီ၊၁",
    "bookname" => "visuddhimagga-mahāṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "visuddhi，ṭī，2",
    "title2" => "ဝိသုဒ္ဓိ၊ဋီ၊၂",
    "bookname" => "visuddhimagga-mahāṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "visuddhi，ṭī，1",
    "title2" => "မဟာဋီ၊၁",
    "bookname" => "visuddhimagga-mahāṭīkā",
    "volume" => 1
  ],
  [
    "title1" => "visuddhi，ṭī，2",
    "title2" => "မဟာဋီ၊၂",
    "bookname" => "visuddhimagga-mahāṭīkā",
    "volume" => 2
  ],
  [
    "title1" => "yamaka，1",
    "title2" => "ယမက၊၁",
    "bookname" => "yamakapāḷi",
    "volume" => 1
  ],
  [
    "title1" => "yamaka，2",
    "title2" => "ယမက၊၂",
    "bookname" => "yamakapāḷi",
    "volume" => 2
  ],
  [
    "title1" => "yamaka，3",
    "title2" => "ယမက၊၃",
    "bookname" => "yamakapāḷi",
    "volume" => 3
  ]
  ];

    }
    public static function get(){
        return [
  [
    "id" => 1,
    "book" => 1,
    "name" => "Namakkārapāḷi",
    "term" => "namakkārapāḷi",
    "v_title" => "namakkārapāḷi",
    "m_title" => "namakkārapāḷi",
    "p_title" => "namakkārapāḷi",
    "abbr" => "namakkārapāḷi"
  ],
  [
    "id" => 2,
    "book" => 1,
    "name" => "namakkāraṭīkā",
    "term" => "namakkāraṭīkā",
    "v_title" => "namakkāraṭīkā",
    "m_title" => "namakkāraṭīkā",
    "p_title" => "namakkāraṭīkā",
    "abbr" => "namakkāraṭīkā"
  ],
  [
    "id" => 3,
    "book" => 2,
    "name" => "Mahāpaṇāmapāṭha(Buddhavandanā)",
    "term" => "mahāpaṇāmapāṭha",
    "v_title" => "mahāpaṇāmapāṭha",
    "m_title" => "mahāpaṇāmapāṭha",
    "p_title" => "mahāpaṇāmapāṭha",
    "abbr" => "mahāpaṇāmapāṭha"
  ],
  [
    "id" => 4,
    "book" => 2,
    "name" => "tigumbacetiya thomanā",
    "term" => "tigumbacetiya thomanā",
    "v_title" => "tigumbacetiya thomanā",
    "m_title" => "tigumbacetiya thomanā",
    "p_title" => "tigumbacetiya thomanā",
    "abbr" => "tigumbacetiya thomanā"
  ],
  [
    "id" => 5,
    "book" => 2,
    "name" => "vāsamālinīkya",
    "term" => "vāsamālinīkya",
    "v_title" => "vāsamālinīkya",
    "m_title" => "vāsamālinīkya",
    "p_title" => "vāsamālinīkya",
    "abbr" => "vāsamālinīkya"
  ],
  [
    "id" => 6,
    "book" => 3,
    "name" => "Lakkhaṇāto",
    "term" => "lakkhaṇāto",
    "v_title" => "lakkhaṇāto",
    "m_title" => "lakkhaṇāto",
    "p_title" => "lakkhaṇāto",
    "abbr" => "lakkhaṇāto"
  ],
  [
    "id" => 7,
    "book" => 4,
    "name" => "Suttavandanā",
    "term" => "suttavandanā",
    "v_title" => "suttavandanā",
    "m_title" => "suttavandanā",
    "p_title" => "suttavandanā",
    "abbr" => "suttavandanā"
  ],
  [
    "id" => 12,
    "book" => 9,
    "name" => "Abhidhānappadīpikāṭīkā",
    "term" => "abhidhānappadīpikāṭīkā",
    "v_title" => "abhidhānappadīpikāṭīkā",
    "m_title" => "abhidhānappadīpikāṭīkā",
    "p_title" => "abhidhānappadīpikāṭīkā",
    "abbr" => "abhidhānappadīpikāṭīkā"
  ],
  [
    "id" => 13,
    "book" => 10,
    "name" => "Subodhālaṅkāro",
    "term" => "subodhālaṅkāro",
    "v_title" => "subodhālaṅkāro",
    "m_title" => "subodhālaṅkāro",
    "p_title" => "subodhālaṅkāro",
    "abbr" => "subodhālaṅkāro"
  ],
  [
    "id" => 14,
    "book" => 11,
    "name" => "Subodhālaṅkāraṭīkā",
    "term" => "subodhālaṅkāraṭīkā",
    "v_title" => "subodhālaṅkāraṭīkā",
    "m_title" => "subodhālaṅkāraṭīkā",
    "p_title" => "subodhālaṅkāraṭīkā",
    "abbr" => "subodhālaṅkāraṭīkā"
  ],
  [
    "id" => 15,
    "book" => 12,
    "name" => "Bālāvatāra",
    "term" => "bālāvatāra",
    "v_title" => "bālāvatāra",
    "m_title" => "bālāvatāra",
    "p_title" => "bālāvatāra",
    "abbr" => "bālāvatāra"
  ],
  [
    "id" => 16,
    "book" => 13,
    "name" => "Moggallānasuttapāṭho",
    "term" => "moggallānasuttapāṭho",
    "v_title" => "moggallānasuttapāṭho",
    "m_title" => "moggallānasuttapāṭho",
    "p_title" => "moggallānasuttapāṭho",
    "abbr" => "moggallānasuttapāṭho"
  ],
  [
    "id" => 17,
    "book" => 13,
    "name" => "moggallānabyākaraṇaṃ",
    "term" => "moggallānabyākaraṇaṃ",
    "v_title" => "moggallānabyākaraṇaṃ",
    "m_title" => "moggallānabyākaraṇaṃ",
    "p_title" => "moggallānabyākaraṇaṃ",
    "abbr" => "moggallānabyākaraṇaṃ"
  ],
  [
    "id" => 18,
    "book" => 14,
    "name" => "Kaccāyanabyākaraṇaṃ",
    "term" => "kaccāyanabyākaraṇaṃ",
    "v_title" => "kaccāyanabyākaraṇaṃ",
    "m_title" => "kaccāyanabyākaraṇaṃ",
    "p_title" => "kaccāyanabyākaraṇaṃ",
    "abbr" => "kaccāyanabyākaraṇaṃ"
  ],
  [
    "id" => 19,
    "book" => 14,
    "name" => "mahākaccāyanasaddāpāṭha",
    "term" => "mahākaccāyanasaddāpāṭha",
    "v_title" => "mahākaccāyanasaddāpāṭha",
    "m_title" => "mahākaccāyanasaddāpāṭha",
    "p_title" => "mahākaccāyanasaddāpāṭha",
    "abbr" => "mahākaccāyanasaddāpāṭha"
  ],
  [
    "id" => 20,
    "book" => 15,
    "name" => "Saddanītippakaraṇaṃ (padamālā)",
    "term" => "saddanītippakaraṇaṃ (padamālā)",
    "v_title" => "saddanītippakaraṇaṃ (padamālā)",
    "m_title" => "saddanītippakaraṇaṃ (padamālā)",
    "p_title" => "saddanītippakaraṇaṃ (padamālā)",
    "abbr" => "saddanītippakaraṇaṃ (padamālā)"
  ],
  [
    "id" => 21,
    "book" => 16,
    "name" => "Saddanītippakaraṇaṃ",
    "term" => "saddanītippakaraṇaṃ (dhātumālā)",
    "v_title" => "saddanītippakaraṇaṃ (dhātumālā)",
    "m_title" => "saddanītippakaraṇaṃ (dhātumālā)",
    "p_title" => "saddanītippakaraṇaṃ (dhātumālā)",
    "abbr" => "saddanītippakaraṇaṃ (dhātumālā)"
  ],
  [
    "id" => 22,
    "book" => 17,
    "name" => "Padarūpasiddhi",
    "term" => "padarūpasiddhi",
    "v_title" => "padarūpasiddhi",
    "m_title" => "padarūpasiddhi",
    "p_title" => "padarūpasiddhi",
    "abbr" => "padarūpasiddhi"
  ],
  [
    "id" => 23,
    "book" => 18,
    "name" => "Moggallāna pañcikā ṭīkā",
    "term" => "moggallāna pañcikā ṭīkā",
    "v_title" => "moggallāna pañcikā ṭīkā",
    "m_title" => "moggallāna pañcikā ṭīkā",
    "p_title" => "moggallāna pañcikā ṭīkā",
    "abbr" => "moggallāna pañcikā ṭīkā"
  ],
  [
    "id" => 24,
    "book" => 19,
    "name" => "Payogasiddhipāḷi",
    "term" => "payogasiddhipāḷi",
    "v_title" => "payogasiddhipāḷi",
    "m_title" => "payogasiddhipāḷi",
    "p_title" => "payogasiddhipāḷi",
    "abbr" => "payogasiddhipāḷi"
  ],
  [
    "id" => 25,
    "book" => 20,
    "name" => "Vuttodayaṃ",
    "term" => "vuttodayaṃ",
    "v_title" => "vuttodayaṃ",
    "m_title" => "vuttodayaṃ",
    "p_title" => "vuttodayaṃ",
    "abbr" => "vuttodayaṃ"
  ],
  [
    "id" => 26,
    "book" => 21,
    "name" => "Abhidhānappadīpikā",
    "term" => "abhidhānappadīpikā",
    "v_title" => "abhidhānappadīpikā",
    "m_title" => "abhidhānappadīpikā",
    "p_title" => "abhidhānappadīpikā",
    "abbr" => "abhidhānappadīpikā"
  ],
  [
    "id" => 27,
    "book" => 22,
    "name" => "Niruttidīpanīpāṭha",
    "term" => "niruttidīpanīpāṭha",
    "v_title" => "niruttidīpanīpāṭha",
    "m_title" => "niruttidīpanīpāṭha",
    "p_title" => "niruttidīpanīpāṭha",
    "abbr" => "niruttidīpanīpāṭha"
  ],
  [
    "id" => 28,
    "book" => 23,
    "name" => "Paramatthadīpanī",
    "term" => "paramatthadīpanī",
    "v_title" => "paramatthadīpanī",
    "m_title" => "paramatthadīpanī",
    "p_title" => "paramatthadīpanī",
    "abbr" => "paramatthadīpanī"
  ],
  [
    "id" => 29,
    "book" => 24,
    "name" => "Anudīpanīpāṭha",
    "term" => "anudīpanīpāṭha",
    "v_title" => "anudīpanīpāṭha",
    "m_title" => "anudīpanīpāṭha",
    "p_title" => "anudīpanīpāṭha",
    "abbr" => "anudīpanīpāṭha"
  ],
  [
    "id" => 30,
    "book" => 25,
    "name" => "Paṭṭhānuddesa dīpanīpāṭha",
    "term" => "paṭṭhānuddesa dīpanīpāṭha",
    "v_title" => "paṭṭhānuddesa dīpanīpāṭha",
    "m_title" => "paṭṭhānuddesa dīpanīpāṭha",
    "p_title" => "paṭṭhānuddesa dīpanīpāṭha",
    "abbr" => "paṭṭhānuddesa dīpanīpāṭha"
  ],
  [
    "id" => 31,
    "book" => 26,
    "name" => "Caturārakkhadīpanī",
    "term" => "caturārakkhadīpanī",
    "v_title" => "caturārakkhadīpanī",
    "m_title" => "caturārakkhadīpanī",
    "p_title" => "caturārakkhadīpanī",
    "abbr" => "caturārakkhadīpanī"
  ],
  [
    "id" => 32,
    "book" => 27,
    "name" => "Kavidappaṇanīti",
    "term" => "kavidappaṇanīti",
    "v_title" => "kavidappaṇanīti",
    "m_title" => "kavidappaṇanīti",
    "p_title" => "kavidappaṇanīti",
    "abbr" => "kavidappaṇanīti"
  ],
  [
    "id" => 33,
    "book" => 28,
    "name" => "Nītimañjarī",
    "term" => "nītimañjarī",
    "v_title" => "nītimañjarī",
    "m_title" => "nītimañjarī",
    "p_title" => "nītimañjarī",
    "abbr" => "nītimañjarī"
  ],
  [
    "id" => 34,
    "book" => 29,
    "name" => "Dhammanīti",
    "term" => "dhammanīti",
    "v_title" => "dhammanīti",
    "m_title" => "dhammanīti",
    "p_title" => "dhammanīti",
    "abbr" => "dhammanīti"
  ],
  [
    "id" => 35,
    "book" => 30,
    "name" => "Mahārahanīti",
    "term" => "mahārahanīti",
    "v_title" => "mahārahanīti",
    "m_title" => "mahārahanīti",
    "p_title" => "mahārahanīti",
    "abbr" => "mahārahanīti"
  ],
  [
    "id" => 36,
    "book" => 31,
    "name" => "Lokanīti",
    "term" => "lokanīti",
    "v_title" => "lokanīti",
    "m_title" => "lokanīti",
    "p_title" => "lokanīti",
    "abbr" => "lokanīti"
  ],
  [
    "id" => 37,
    "book" => 32,
    "name" => "Suttantanīti",
    "term" => "suttantanīti",
    "v_title" => "suttantanīti",
    "m_title" => "suttantanīti",
    "p_title" => "suttantanīti",
    "abbr" => "suttantanīti"
  ],
  [
    "id" => 38,
    "book" => 32,
    "name" => "vasalasutta",
    "term" => "vasalasutta",
    "v_title" => "vasalasutta",
    "m_title" => "vasalasutta",
    "p_title" => "vasalasutta",
    "abbr" => "vasalasutta"
  ],
  [
    "id" => 39,
    "book" => 33,
    "name" => "Sūrassatīnīti",
    "term" => "sūrassatīnīti",
    "v_title" => "sūrassatīnīti",
    "m_title" => "sūrassatīnīti",
    "p_title" => "sūrassatīnīti",
    "abbr" => "sūrassatīnīti"
  ],
  [
    "id" => 40,
    "book" => 34,
    "name" => "Cāṇakyanītipāḷi",
    "term" => "cāṇakyanītipāḷi",
    "v_title" => "cāṇakyanītipāḷi",
    "m_title" => "cāṇakyanītipāḷi",
    "p_title" => "cāṇakyanītipāḷi",
    "abbr" => "cāṇakyanītipāḷi"
  ],
  [
    "id" => 41,
    "book" => 35,
    "name" => "Naradakkhadīpanī",
    "term" => "naradakkhadīpanī",
    "v_title" => "naradakkhadīpanī",
    "m_title" => "naradakkhadīpanī",
    "p_title" => "naradakkhadīpanī",
    "abbr" => "naradakkhadīpanī"
  ],
  [
    "id" => 42,
    "book" => 36,
    "name" => "Rasavāhinī",
    "term" => "rasavāhinī",
    "v_title" => "rasavāhinī",
    "m_title" => "rasavāhinī",
    "p_title" => "rasavāhinī",
    "abbr" => "rasavāhinī"
  ],
  [
    "id" => 43,
    "book" => 37,
    "name" => "Sīmavisodhanī",
    "term" => "sīmavisodhanī",
    "v_title" => "sīmavisodhanī",
    "m_title" => "sīmavisodhanī",
    "p_title" => "sīmavisodhanī",
    "abbr" => "sīmavisodhanī"
  ],
  [
    "id" => 44,
    "book" => 38,
    "name" => "Vessantarāgīti",
    "term" => "vessantarāgīti",
    "v_title" => "vessantarāgīti",
    "m_title" => "vessantarāgīti",
    "p_title" => "vessantarāgīti",
    "abbr" => "vessantarāgīti"
  ],
  [
    "id" => 45,
    "book" => 39,
    "name" => "Dīghanikāye",
    "term" => "saṅgayana-puccha vissajjanā dīghanikāye",
    "v_title" => "saṅgayana-puccha vissajjanā dīghanikāye",
    "m_title" => "saṅgayana-puccha vissajjanā dīghanikāye",
    "p_title" => "saṅgayana-puccha vissajjanā dīghanikāye",
    "abbr" => "(saṅgayana-puccha vissajjanā) dīghanikāye"
  ],
  [
    "id" => 46,
    "book" => 40,
    "name" => "Majjhimanikāya",
    "term" => "saṅgayana-puccha vissajjanā majjhimanikāya",
    "v_title" => "saṅgayana-puccha vissajjanā majjhimanikāya",
    "m_title" => "saṅgayana-puccha vissajjanā majjhimanikāya",
    "p_title" => "saṅgayana-puccha vissajjanā majjhimanikāya",
    "abbr" => "(saṅgayana-puccha vissajjanā) majjhimanikāya"
  ],
  [
    "id" => 47,
    "book" => 41,
    "name" => "Saṃyuttanikāye",
    "term" => "saṅgayana-puccha vissajjanā saṃyuttanikāye",
    "v_title" => "saṅgayana-puccha vissajjanā saṃyuttanikāye",
    "m_title" => "saṅgayana-puccha vissajjanā saṃyuttanikāye",
    "p_title" => "saṅgayana-puccha vissajjanā saṃyuttanikāye",
    "abbr" => "(saṅgayana-puccha vissajjanā) saṃyuttanikāye"
  ],
  [
    "id" => 48,
    "book" => 42,
    "name" => "Aṅguttaranikāye",
    "term" => "saṅgayana-puccha vissajjanā aṅguttaranikāye",
    "v_title" => "saṅgayana-puccha vissajjanā aṅguttaranikāye",
    "m_title" => "saṅgayana-puccha vissajjanā aṅguttaranikāye",
    "p_title" => "saṅgayana-puccha vissajjanā aṅguttaranikāye",
    "abbr" => "(saṅgayana-puccha vissajjanā) aṅguttaranikāye"
  ],
  [
    "id" => 49,
    "book" => 43,
    "name" => "Vinayapiṭaka",
    "term" => "saṅgayana-puccha vissajjanā vinayapiṭaka",
    "v_title" => "saṅgayana-puccha vissajjanā vinayapiṭaka",
    "m_title" => "saṅgayana-puccha vissajjanā vinayapiṭaka",
    "p_title" => "saṅgayana-puccha vissajjanā vinayapiṭaka",
    "abbr" => "(saṅgayana-puccha vissajjanā) vinayapiṭaka"
  ],
  [
    "id" => 50,
    "book" => 44,
    "name" => "Abhidhammapiṭaka",
    "term" => "saṅgayana-puccha vissajjanā abhidhammapiṭaka",
    "v_title" => "saṅgayana-puccha vissajjanā abhidhammapiṭaka",
    "m_title" => "saṅgayana-puccha vissajjanā abhidhammapiṭaka",
    "p_title" => "saṅgayana-puccha vissajjanā abhidhammapiṭaka",
    "abbr" => "(saṅgayana-puccha vissajjanā) abhidhammapiṭaka"
  ],
  [
    "id" => 51,
    "book" => 45,
    "name" => "Aṭṭhakathā",
    "term" => "saṅgayana-puccha vissajjanā aṭṭhakathā",
    "v_title" => "saṅgayana-puccha vissajjanā aṭṭhakathā",
    "m_title" => "saṅgayana-puccha vissajjanā aṭṭhakathā",
    "p_title" => "saṅgayana-puccha vissajjanā aṭṭhakathā",
    "abbr" => "(saṅgayana-puccha vissajjanā) aṭṭhakathā"
  ],
  [
    "id" => 52,
    "book" => 46,
    "name" => "Milidaṭīkā",
    "term" => "milidaṭīkā",
    "v_title" => "milidaṭīkā",
    "m_title" => "milidaṭīkā",
    "p_title" => "milidaṭīkā",
    "abbr" => "milidaṭīkā"
  ],
  [
    "id" => 53,
    "book" => 47,
    "name" => "Padamañjarī",
    "term" => "padamañjarī",
    "v_title" => "padamañjarī",
    "m_title" => "padamañjarī",
    "p_title" => "padamañjarī",
    "abbr" => "padamañjarī"
  ],
  [
    "id" => 54,
    "book" => 48,
    "name" => "Padasādhanaṃ",
    "term" => "padasādhanaṃ",
    "v_title" => "padasādhanaṃ",
    "m_title" => "padasādhanaṃ",
    "p_title" => "padasādhanaṃ",
    "abbr" => "padasādhanaṃ"
  ],
  [
    "id" => 55,
    "book" => 49,
    "name" => "Saddabindu pakaraṇaṃ",
    "term" => "saddabindu pakaraṇaṃ",
    "v_title" => "saddabindu pakaraṇaṃ",
    "m_title" => "saddabindu pakaraṇaṃ",
    "p_title" => "saddabindu pakaraṇaṃ",
    "abbr" => "saddabindu pakaraṇaṃ"
  ],
  [
    "id" => 56,
    "book" => 50,
    "name" => "Kaccāyana dhātu mañjūsā",
    "term" => "kaccāyana  dhātu mañjūsā",
    "v_title" => "kaccāyana  dhātu mañjūsā",
    "m_title" => "kaccāyana  dhātu mañjūsā",
    "p_title" => "kaccāyana  dhātu mañjūsā",
    "abbr" => "kaccāyana  dhātu mañjūsā"
  ],
  [
    "id" => 57,
    "book" => 51,
    "name" => "Samantakūṭavaṇṇanā",
    "term" => "samantakūṭavaṇṇanā",
    "v_title" => "samantakūṭavaṇṇanā",
    "m_title" => "samantakūṭavaṇṇanā",
    "p_title" => "samantakūṭavaṇṇanā",
    "abbr" => "samantakūṭavaṇṇanā"
  ],
  [
    "id" => 58,
    "book" => 52,
    "name" => "Vuttisametā",
    "term" => "moggallāna vuttivivaraṇapañcikā.",
    "v_title" => "moggallāna vuttivivaraṇapañcikā.",
    "m_title" => "moggallāna vuttivivaraṇapañcikā.",
    "p_title" => "moggallāna vuttivivaraṇapañcikā.",
    "abbr" => "moggallāna vuttivivaraṇapañcikā."
  ],
  [
    "id" => 59,
    "book" => 53,
    "name" => "Thupavaṃsa",
    "term" => "thupavaṃsa",
    "v_title" => "thupavaṃsa",
    "m_title" => "thupavaṃsa",
    "p_title" => "thupavaṃsa",
    "abbr" => "thupavaṃsa"
  ],
  [
    "id" => 60,
    "book" => 54,
    "name" => "Dāṭhāvaṃsa",
    "term" => "dāṭhāvaṃsa",
    "v_title" => "dāṭhāvaṃsa",
    "m_title" => "dāṭhāvaṃsa",
    "p_title" => "dāṭhāvaṃsa",
    "abbr" => "dāṭhāvaṃsa"
  ],
  [
    "id" => 61,
    "book" => 55,
    "name" => "Dhātupāṭha vilāsiniyā",
    "term" => "dhātupāṭha  vilāsiniyā",
    "v_title" => "dhātupāṭha  vilāsiniyā",
    "m_title" => "dhātupāṭha  vilāsiniyā",
    "p_title" => "dhātupāṭha  vilāsiniyā",
    "abbr" => "dhātupāṭha  vilāsiniyā"
  ],
  [
    "id" => 62,
    "book" => 56,
    "name" => "Dhātuvaṃsa",
    "term" => "dhātuvaṃsa",
    "v_title" => "dhātuvaṃsa",
    "m_title" => "dhātuvaṃsa",
    "p_title" => "dhātuvaṃsa",
    "abbr" => "dhātuvaṃsa"
  ],
  [
    "id" => 63,
    "book" => 57,
    "name" => "Hatthavanagallavihāra vaṃso",
    "term" => "hatthavanagallavihāra  vaṃso",
    "v_title" => "hatthavanagallavihāra  vaṃso",
    "m_title" => "hatthavanagallavihāra  vaṃso",
    "p_title" => "hatthavanagallavihāra  vaṃso",
    "abbr" => "hatthavanagallavihāra  vaṃso"
  ],
  [
    "id" => 64,
    "book" => 58,
    "name" => "Jinacaritaya",
    "term" => "jinacaritaya",
    "v_title" => "jinacaritaya",
    "m_title" => "jinacaritaya",
    "p_title" => "jinacaritaya",
    "abbr" => "jinacaritaya"
  ],
  [
    "id" => 65,
    "book" => 59,
    "name" => "Jinavaṃsadīpaṃ",
    "term" => "jinavaṃsadīpaṃ",
    "v_title" => "jinavaṃsadīpaṃ",
    "m_title" => "jinavaṃsadīpaṃ",
    "p_title" => "jinavaṃsadīpaṃ",
    "abbr" => "jinavaṃsadīpaṃ"
  ],
  [
    "id" => 66,
    "book" => 60,
    "name" => "Telakaṭāhagāthā",
    "term" => "telakaṭāhagāthā",
    "v_title" => "telakaṭāhagāthā",
    "m_title" => "telakaṭāhagāthā",
    "p_title" => "telakaṭāhagāthā",
    "abbr" => "telakaṭāhagāthā"
  ],
  [
    "id" => 67,
    "book" => 61,
    "name" => "Cūḷaganthavaṃsapāḷi",
    "term" => "cūḷaganthavaṃsapāḷi",
    "v_title" => "cūḷaganthavaṃsapāḷi",
    "m_title" => "cūḷaganthavaṃsapāḷi",
    "p_title" => "cūḷaganthavaṃsapāḷi",
    "abbr" => "cūḷaganthavaṃsapāḷi"
  ],
  [
    "id" => 68,
    "book" => 62,
    "name" => "Sāsanavaṃsappadīpikā",
    "term" => "sāsanavaṃsappadīpikā",
    "v_title" => "sāsanavaṃsappadīpikā",
    "m_title" => "sāsanavaṃsappadīpikā",
    "p_title" => "sāsanavaṃsappadīpikā",
    "abbr" => "sāsanavaṃsappadīpikā"
  ],
  [
    "id" => 69,
    "book" => 63,
    "name" => "Mahāvaṃsapāḷi",
    "term" => "mahāvaṃsapāḷi",
    "v_title" => "mahāvaṃsapāḷi",
    "m_title" => "mahāvaṃsapāḷi",
    "p_title" => "mahāvaṃsapāḷi",
    "abbr" => "mahāvaṃsapāḷi"
  ],
  [
    "id" => 70,
    "book" => 64,
    "name" => "Visuddhimaggo(Paṭhamo bhāgo)",
    "term" => "visuddhimagga",
    "v_title" => "visuddhimagga",
    "m_title" => "visuddhimagga",
    "p_title" => "visuddhimagga",
    "abbr" => "visuddhi."
  ],
  [
    "id" => 71,
    "book" => 65,
    "name" => "Visuddhimaggo(Dutiyo bhāgo)",
    "term" => "visuddhimagga",
    "v_title" => "visuddhimagga",
    "m_title" => "visuddhimagga",
    "p_title" => "visuddhimagga",
    "abbr" => "visuddhi."
  ],
  [
    "id" => 72,
    "book" => 66,
    "name" => "Visuddhimagga-mahāṭīkā(Paṭhamo bhāgo)",
    "term" => "Visuddhimagga-mahāṭīkā",
    "v_title" => "visuddhimagga-mahāṭīkā",
    "m_title" => "visuddhimagga-mahāṭīkā",
    "p_title" => "Visuddhimagga-mahāṭīkā",
    "abbr" => "visuddhi. ṭī."
  ],
  [
    "id" => 73,
    "book" => 67,
    "name" => "Visuddhimagga-mahāṭīkā(Dutiyo bhāgo)",
    "term" => "Visuddhimagga-mahāṭīkā",
    "v_title" => "visuddhimagga-mahāṭīkā",
    "m_title" => "visuddhimagga-mahāṭīkā",
    "p_title" => "Visuddhimagga-mahāṭīkā",
    "abbr" => "visuddhi. ṭī."
  ],
  [
    "id" => 74,
    "book" => 68,
    "name" => "Visuddhimagga nidānakathā",
    "term" => "visuddhimagga-nidānakathā",
    "v_title" => "visuddhimagga-nidānakathā",
    "m_title" => "visuddhimagga-nidānakathā",
    "p_title" => "visuddhimagga-nidānakathā",
    "abbr" => "visuddhimagga-nidānakathā"
  ],
  [
    "id" => 75,
    "book" => 69,
    "name" => "Paṭṭhānapāḷi(Dutiyo bhāgo)",
    "term" => "paṭṭhānapāḷi",
    "v_title" => "paṭṭhānapāḷi",
    "m_title" => "paṭṭhānapāḷi",
    "p_title" => "paṭṭhānapāḷi",
    "abbr" => "paṭṭhāna."
  ],
  [
    "id" => 76,
    "book" => 70,
    "name" => "Paṭṭhānapāḷi(Tatiyo bhāgo)",
    "term" => "paṭṭhānapāḷi",
    "v_title" => "paṭṭhānapāḷi",
    "m_title" => "paṭṭhānapāḷi",
    "p_title" => "paṭṭhānapāḷi",
    "abbr" => "paṭṭhāna."
  ],
  [
    "id" => 77,
    "book" => 71,
    "name" => "Paṭṭhānapāḷi(Catuttho bhāgo)",
    "term" => "paṭṭhānapāḷi",
    "v_title" => "paṭṭhānapāḷi",
    "m_title" => "paṭṭhānapāḷi",
    "p_title" => "paṭṭhānapāḷi",
    "abbr" => "paṭṭhāna."
  ],
  [
    "id" => 78,
    "book" => 72,
    "name" => "Paṭṭhānapāḷi(Pañcamo bhāgo)",
    "term" => "paṭṭhānapāḷi",
    "v_title" => "paṭṭhānapāḷi",
    "m_title" => "paṭṭhānapāḷi",
    "p_title" => "paṭṭhānapāḷi",
    "abbr" => "paṭṭhāna."
  ],
  [
    "id" => 79,
    "book" => 73,
    "name" => "Dhammasaṅgaṇīpāḷi",
    "term" => "dhammasaṅgaṇīpāḷi",
    "v_title" => "dhammasaṅgaṇīpāḷi",
    "m_title" => "dhammasaṅgaṇīpāḷi",
    "p_title" => "dhammasaṅgaṇīpāḷi",
    "abbr" => "abhi. dha."
  ],
  [
    "id" => 80,
    "book" => 74,
    "name" => "Vibhaṅgapāḷi",
    "term" => "vibhaṅgapāḷi",
    "v_title" => "vibhaṅgapāḷi",
    "m_title" => "vibhaṅgapāḷi",
    "p_title" => "vibhaṅgapāḷi",
    "abbr" => "abhi. vi."
  ],
  [
    "id" => 81,
    "book" => 75,
    "name" => "Dhātukathāpāḷi",
    "term" => "dhātukathāpāḷi",
    "v_title" => "dhātukathāpāḷi",
    "m_title" => "dhātukathāpāḷi",
    "p_title" => "dhātukathāpāḷi",
    "abbr" => "abhi. dhā."
  ],
  [
    "id" => 82,
    "book" => 76,
    "name" => "Puggalapaññattipāḷi",
    "term" => "puggalapaññattipāḷi",
    "v_title" => "puggalapaññattipāḷi",
    "m_title" => "puggalapaññattipāḷi",
    "p_title" => "puggalapaññattipāḷi",
    "abbr" => "abhi. pu."
  ],
  [
    "id" => 83,
    "book" => 77,
    "name" => "Kathāvatthupāḷi",
    "term" => "kathāvatthupāḷi",
    "v_title" => "kathāvatthupāḷi",
    "m_title" => "kathāvatthupāḷi",
    "p_title" => "kathāvatthupāḷi",
    "abbr" => "abhi. ka."
  ],
  [
    "id" => 84,
    "book" => 78,
    "name" => "Yamakapāḷi (paṭhamo bhāgo)",
    "term" => "yamakapāḷi",
    "v_title" => "yamakapāḷi",
    "m_title" => "yamakapāḷi",
    "p_title" => "yamakapāḷi",
    "abbr" => "yamaka."
  ],
  [
    "id" => 85,
    "book" => 79,
    "name" => "Yamakapāḷi (dutiyo bhāgo)",
    "term" => "yamakapāḷi",
    "v_title" => "yamakapāḷi",
    "m_title" => "yamakapāḷi",
    "p_title" => "yamakapāḷi",
    "abbr" => "yamaka."
  ],
  [
    "id" => 86,
    "book" => 80,
    "name" => "Yamakapāḷi (tatiyo bhāgo)",
    "term" => "yamakapāḷi",
    "v_title" => "yamakapāḷi",
    "m_title" => "yamakapāḷi",
    "p_title" => "yamakapāḷi",
    "abbr" => "yamaka."
  ],
  [
    "id" => 87,
    "book" => 81,
    "name" => "Paṭṭhānapāḷi(Paṭhamo bhāgo)",
    "term" => "paṭṭhānapāḷi",
    "v_title" => "paṭṭhānapāḷi",
    "m_title" => "paṭṭhānapāḷi",
    "p_title" => "paṭṭhānapāḷi",
    "abbr" => "paṭṭhāna."
  ],
  [
    "id" => 88,
    "book" => 82,
    "name" => "Dasakanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-2",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 89,
    "book" => 83,
    "name" => "Ekādasakanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-2",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 90,
    "book" => 84,
    "name" => "Ekakanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-1",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 91,
    "book" => 85,
    "name" => "Dukanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-1",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 92,
    "book" => 86,
    "name" => "Tikanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-1",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 93,
    "book" => 87,
    "name" => "Catukkanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-2",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 94,
    "book" => 88,
    "name" => "Pañcakanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-1",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 95,
    "book" => 89,
    "name" => "Chakkanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-2",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 96,
    "book" => 90,
    "name" => "Sattakanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-2",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 97,
    "book" => 91,
    "name" => "Aṭṭhakanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-1",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 98,
    "book" => 92,
    "name" => "Navakanipātapāḷi",
    "term" => "aṅguttaranikāya",
    "v_title" => "aṅguttaranikāya-1",
    "m_title" => "aṅguttaranikāya",
    "p_title" => "aṅguttaranikāya",
    "abbr" => "aṃ."
  ],
  [
    "id" => 99,
    "book" => 93,
    "name" => "Sīlakkhandhavaggapāḷi",
    "term" => "dīghanikāya",
    "v_title" => "dīghanikāya",
    "m_title" => "dīghanikāya",
    "p_title" => "dīghanikāya",
    "abbr" => "dī."
  ],
  [
    "id" => 100,
    "book" => 94,
    "name" => "Mahāvaggapāḷi",
    "term" => "dīghanikāya",
    "v_title" => "dīghanikāya",
    "m_title" => "dīghanikāya",
    "p_title" => "dīghanikāya",
    "abbr" => "dī."
  ],
  [
    "id" => 101,
    "book" => 95,
    "name" => "Pāthikavaggapāḷi",
    "term" => "dīghanikāya",
    "v_title" => "dīghanikāya",
    "m_title" => "dīghanikāya",
    "p_title" => "dīghanikāya",
    "abbr" => "dī."
  ],
  [
    "id" => 102,
    "book" => 96,
    "name" => "Dhammasaṅgaṇī-aṭṭhakathā",
    "term" => "dhammasaṅgaṇī-aṭṭhakathā",
    "v_title" => "dhammasaṅgaṇī-aṭṭhakathā",
    "m_title" => "dhammasaṅgaṇī-aṭṭhakathā",
    "p_title" => "dhammasaṅgaṇī-aṭṭhakathā",
    "abbr" => "abhi. ṭṭha. 1"
  ],
  [
    "id" => 103,
    "book" => 97,
    "name" => "Vibhaṅga-aṭṭhakathā",
    "term" => "vibhaṅga-aṭṭhakathā",
    "v_title" => "vibhaṅga-aṭṭhakathā",
    "m_title" => "vibhaṅga-aṭṭhakathā",
    "p_title" => "vibhaṅga-aṭṭhakathā",
    "abbr" => "abhi. ṭṭha. 2"
  ],
  [
    "id" => 104,
    "book" => 98,
    "name" => "Pañcapakaraṇa-aṭṭhakathā",
    "term" => "pañcapakaraṇa-aṭṭhakathā",
    "v_title" => "pañcapakaraṇa-aṭṭhakathā",
    "m_title" => "pañcapakaraṇa-aṭṭhakathā",
    "p_title" => "pañcapakaraṇa-aṭṭhakathā",
    "abbr" => "abhi. ṭṭha. 3"
  ],
  [
    "id" => 105,
    "book" => 98,
    "name" => "puggalapaññatti-aṭṭhakathā",
    "term" => "pañcapakaraṇa-aṭṭhakathā",
    "v_title" => "pañcapakaraṇa-aṭṭhakathā",
    "m_title" => "pañcapakaraṇa-aṭṭhakathā",
    "p_title" => "pañcapakaraṇa-aṭṭhakathā",
    "abbr" => "abhi. ṭṭha. 3"
  ],
  [
    "id" => 106,
    "book" => 98,
    "name" => "kathāvatthu-aṭṭhakathā",
    "term" => "pañcapakaraṇa-aṭṭhakathā",
    "v_title" => "pañcapakaraṇa-aṭṭhakathā",
    "m_title" => "pañcapakaraṇa-aṭṭhakathā",
    "p_title" => "pañcapakaraṇa-aṭṭhakathā",
    "abbr" => "abhi. ṭṭha. 3"
  ],
  [
    "id" => 107,
    "book" => 98,
    "name" => "yamakappakaraṇa-aṭṭhakathā",
    "term" => "pañcapakaraṇa-aṭṭhakathā",
    "v_title" => "pañcapakaraṇa-aṭṭhakathā",
    "m_title" => "pañcapakaraṇa-aṭṭhakathā",
    "p_title" => "pañcapakaraṇa-aṭṭhakathā",
    "abbr" => "abhi. ṭṭha. 3"
  ],
  [
    "id" => 108,
    "book" => 98,
    "name" => "paṭṭhānappakaraṇa-aṭṭhakathā",
    "term" => "pañcapakaraṇa-aṭṭhakathā",
    "v_title" => "pañcapakaraṇa-aṭṭhakathā",
    "m_title" => "pañcapakaraṇa-aṭṭhakathā",
    "p_title" => "pañcapakaraṇa-aṭṭhakathā",
    "abbr" => "abhi. ṭṭha. 3"
  ],
  [
    "id" => 109,
    "book" => 99,
    "name" => "Ekakanipāta-aṭṭhakathā",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 110,
    "book" => 100,
    "name" => "Dukanipāta-aṭṭhakathā",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 111,
    "book" => 100,
    "name" => "manorathapūraṇī",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 112,
    "book" => 100,
    "name" => "manorathapūraṇī",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 113,
    "book" => 101,
    "name" => "Pañcakanipāta-aṭṭhakathā",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 114,
    "book" => 101,
    "name" => "manorathapūraṇī",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 115,
    "book" => 101,
    "name" => "manorathapūraṇī",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 116,
    "book" => 102,
    "name" => "Aṭṭhakanipāta-aṭṭhakathā",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 117,
    "book" => 102,
    "name" => "manorathapūraṇī",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 118,
    "book" => 102,
    "name" => "manorathapūraṇī",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 119,
    "book" => 102,
    "name" => "manorathapūraṇī",
    "term" => "aṅguttaranikāya-aṭṭhakathā",
    "v_title" => "aṅguttaranikāya-aṭṭhakathā",
    "m_title" => "aṅguttaranikāya-aṭṭhakathā",
    "p_title" => "aṅguttaranikāya-aṭṭhakathā",
    "abbr" => "aṃ. ṭṭha."
  ],
  [
    "id" => 120,
    "book" => 103,
    "name" => "Sīlakkhandhavaggaṭṭhakathā",
    "term" => "dīghanikāya-aṭṭhakathā",
    "v_title" => "dīghanikāya-aṭṭhakathā",
    "m_title" => "dīghanikāya-aṭṭhakathā",
    "p_title" => "dīghanikāya-aṭṭhakathā",
    "abbr" => "dī. ṭṭha."
  ],
  [
    "id" => 121,
    "book" => 104,
    "name" => "Mahāvaggaṭṭhakathā",
    "term" => "dīghanikāya-aṭṭhakathā",
    "v_title" => "dīghanikāya-aṭṭhakathā",
    "m_title" => "dīghanikāya-aṭṭhakathā",
    "p_title" => "dīghanikāya-aṭṭhakathā",
    "abbr" => "dī. ṭṭha."
  ],
  [
    "id" => 122,
    "book" => 105,
    "name" => "Pāthikavaggaṭṭhakathā",
    "term" => "dīghanikāya-aṭṭhakathā",
    "v_title" => "dīghanikāya-aṭṭhakathā",
    "m_title" => "dīghanikāya-aṭṭhakathā",
    "p_title" => "dīghanikāya-aṭṭhakathā",
    "abbr" => "dī. ṭṭha."
  ],
  [
    "id" => 123,
    "book" => 106,
    "name" => "Therīgāthā-aṭṭhakathā",
    "term" => "therīgāthā-aṭṭhakathā",
    "v_title" => "therīgāthā-aṭṭhakathā",
    "m_title" => "therīgāthā-aṭṭhakathā",
    "p_title" => "therīgāthā-aṭṭhakathā",
    "abbr" => "therī. ṭṭha."
  ],
  [
    "id" => 124,
    "book" => 107,
    "name" => "Apadāna-aṭṭhakathā",
    "term" => "apadāna-aṭṭhakathā",
    "v_title" => "apadāna-aṭṭhakathā",
    "m_title" => "apadāna-aṭṭhakathā",
    "p_title" => "apadāna-aṭṭhakathā",
    "abbr" => "apa. ṭṭha."
  ],
  [
    "id" => 125,
    "book" => 108,
    "name" => "Buddhavaṃsa-aṭṭhakathā",
    "term" => "buddhavaṃsa-aṭṭhakathā",
    "v_title" => "buddhavaṃsa-aṭṭhakathā",
    "m_title" => "buddhavaṃsa-aṭṭhakathā",
    "p_title" => "buddhavaṃsa-aṭṭhakathā",
    "abbr" => "buddhavaṃ. ṭṭha."
  ],
  [
    "id" => 126,
    "book" => 109,
    "name" => "Cariyāpiṭaka-aṭṭhakathā",
    "term" => "cariyāpiṭaka-aṭṭhakathā",
    "v_title" => "cariyāpiṭaka-aṭṭhakathā",
    "m_title" => "cariyāpiṭaka-aṭṭhakathā",
    "p_title" => "cariyāpiṭaka-aṭṭhakathā",
    "abbr" => "cariyā. ṭṭha."
  ],
  [
    "id" => 127,
    "book" => 110,
    "name" => "Jātaka-aṭṭhakathā(Paṭhamo bhāgo)",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 128,
    "book" => 111,
    "name" => "Jātaka-aṭṭhakathā(Dutiyo bhāgo)",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 129,
    "book" => 112,
    "name" => "Jātaka-aṭṭhakathā(Tatiyo bhāgo)",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 130,
    "book" => 113,
    "name" => "Jātaka-aṭṭhakathā(Catuttho bhāgo)",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 131,
    "book" => 113,
    "name" => "jātaka-aṭṭhakathā",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 132,
    "book" => 114,
    "name" => "Jātaka-aṭṭhakathā(Pañcamo bhāgo)",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 133,
    "book" => 115,
    "name" => "Jātaka-aṭṭhakathā(Chaṭṭho bhāgo)",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 134,
    "book" => 116,
    "name" => "Khuddakapāṭha-aṭṭhakathā",
    "term" => "khuddakapāṭha-aṭṭhakathā",
    "v_title" => "khuddakapāṭha-aṭṭhakathā",
    "m_title" => "khuddakapāṭha-aṭṭhakathā",
    "p_title" => "khuddakapāṭha-aṭṭhakathā",
    "abbr" => "khuddaka. ṭṭha."
  ],
  [
    "id" => 135,
    "book" => 117,
    "name" => "Jātaka-aṭṭhakathā(Sattamo bhāgo)",
    "term" => "jātaka-aṭṭhakathā",
    "v_title" => "jātaka-aṭṭhakathā",
    "m_title" => "jātaka-aṭṭhakathā",
    "p_title" => "jātaka-aṭṭhakathā",
    "abbr" => "jā. ṭṭha."
  ],
  [
    "id" => 136,
    "book" => 118,
    "name" => "Mahāniddesa-aṭṭhakathā",
    "term" => "mahāniddesa-aṭṭhakathā",
    "v_title" => "mahāniddesa-aṭṭhakathā",
    "m_title" => "mahāniddesa-aṭṭhakathā",
    "p_title" => "mahāniddesa-aṭṭhakathā",
    "abbr" => "mahāni. ṭṭha."
  ],
  [
    "id" => 137,
    "book" => 119,
    "name" => "Cūḷaniddesa-aṭṭhakathā",
    "term" => "cūḷaniddesa-aṭṭhakathā",
    "v_title" => "cūḷaniddesa-aṭṭhakathā",
    "m_title" => "cūḷaniddesa-aṭṭhakathā",
    "p_title" => "cūḷaniddesa-aṭṭhakathā",
    "abbr" => "cūḷani. ṭṭha."
  ],
  [
    "id" => 138,
    "book" => 120,
    "name" => "Paṭisambhidāmagga-aṭṭhakathā",
    "term" => "paṭisambhidāmagga-aṭṭhakathā",
    "v_title" => "paṭisambhidāmagga-aṭṭhakathā",
    "m_title" => "paṭisambhidāmagga-aṭṭhakathā",
    "p_title" => "paṭisambhidāmagga-aṭṭhakathā",
    "abbr" => "paṭisaṃ. ṭṭha."
  ],
  [
    "id" => 139,
    "book" => 121,
    "name" => "Nettippakaraṇa-aṭṭhakathā",
    "term" => "nettippakaraṇa-aṭṭhakathā",
    "v_title" => "nettippakaraṇa-aṭṭhakathā",
    "m_title" => "nettippakaraṇa-aṭṭhakathā",
    "p_title" => "nettippakaraṇa-aṭṭhakathā",
    "abbr" => "netti. ṭṭha."
  ],
  [
    "id" => 140,
    "book" => 122,
    "name" => "Dhammapada-aṭṭhakathā",
    "term" => "dhammapada-aṭṭhakathā",
    "v_title" => "dhammapada-aṭṭhakathā",
    "m_title" => "dhammapada-aṭṭhakathā",
    "p_title" => "dhammapada-aṭṭhakathā",
    "abbr" => "dhamma. ṭṭha."
  ],
  [
    "id" => 141,
    "book" => 123,
    "name" => "Udāna-aṭṭhakathā",
    "term" => "udāna-aṭṭhakathā",
    "v_title" => "udāna-aṭṭhakathā",
    "m_title" => "udāna-aṭṭhakathā",
    "p_title" => "udāna-aṭṭhakathā",
    "abbr" => "udāna. ṭṭha."
  ],
  [
    "id" => 142,
    "book" => 124,
    "name" => "Itivuttaka-aṭṭhakathā",
    "term" => "itivuttaka-aṭṭhakathā",
    "v_title" => "itivuttaka-aṭṭhakathā",
    "m_title" => "itivuttaka-aṭṭhakathā",
    "p_title" => "itivuttaka-aṭṭhakathā",
    "abbr" => "itivutta. ṭṭha."
  ],
  [
    "id" => 143,
    "book" => 125,
    "name" => "Suttanipāta-aṭṭhakathā",
    "term" => "suttanipāta-aṭṭhakathā",
    "v_title" => "suttanipāta-aṭṭhakathā",
    "m_title" => "suttanipāta-aṭṭhakathā",
    "p_title" => "suttanipāta-aṭṭhakathā",
    "abbr" => "suttani. ṭṭha."
  ],
  [
    "id" => 144,
    "book" => 126,
    "name" => "Vimānavatthu-aṭṭhakathā",
    "term" => "vimānavatthu-aṭṭhakathā",
    "v_title" => "vimānavatthu-aṭṭhakathā",
    "m_title" => "vimānavatthu-aṭṭhakathā",
    "p_title" => "vimānavatthu-aṭṭhakathā",
    "abbr" => "vimāna. ṭṭha."
  ],
  [
    "id" => 145,
    "book" => 127,
    "name" => "Petavatthu-aṭṭhakathā",
    "term" => "petavatthu-aṭṭhakathā",
    "v_title" => "petavatthu-aṭṭhakathā",
    "m_title" => "petavatthu-aṭṭhakathā",
    "p_title" => "petavatthu-aṭṭhakathā",
    "abbr" => "peta. ṭṭha."
  ],
  [
    "id" => 146,
    "book" => 128,
    "name" => "Theragāthā-aṭṭhakathā(Paṭhamo bhāgo)",
    "term" => "Theragāthā-aṭṭhakathā",
    "v_title" => "Theragāthā-aṭṭhakathā",
    "m_title" => "theragāthā-aṭṭhakathā",
    "p_title" => "Theragāthā-aṭṭhakathā",
    "abbr" => "thera. ṭṭha."
  ],
  [
    "id" => 147,
    "book" => 129,
    "name" => "Theragāthā-aṭṭhakathā(Dutiyo bhāgo)",
    "term" => "Theragāthā-aṭṭhakathā",
    "v_title" => "Theragāthā-aṭṭhakathā",
    "m_title" => "theragāthā-aṭṭhakathā",
    "p_title" => "Theragāthā-aṭṭhakathā",
    "abbr" => "thera. ṭṭha."
  ],
  [
    "id" => 148,
    "book" => 130,
    "name" => "Mūlapaṇṇāsa-aṭṭhakathā",
    "term" => "majimanikaya-aṭṭhakathā",
    "v_title" => "majimanikaya-aṭṭhakathā",
    "m_title" => "majimanikaya-aṭṭhakathā",
    "p_title" => "majimanikaya-aṭṭhakathā",
    "abbr" => "ma. ṭṭha."
  ],
  [
    "id" => 149,
    "book" => 131,
    "name" => "Majjhimapaṇṇāsa-aṭṭhakathā",
    "term" => "majimanikaya-aṭṭhakathā",
    "v_title" => "majimanikaya-aṭṭhakathā",
    "m_title" => "majimanikaya-aṭṭhakathā",
    "p_title" => "majimanikaya-aṭṭhakathā",
    "abbr" => "ma. ṭṭha."
  ],
  [
    "id" => 150,
    "book" => 132,
    "name" => "Uparipaṇṇāsa-aṭṭhakathā",
    "term" => "majimanikaya-aṭṭhakathā",
    "v_title" => "majimanikaya-aṭṭhakathā",
    "m_title" => "majimanikaya-aṭṭhakathā",
    "p_title" => "majimanikaya-aṭṭhakathā",
    "abbr" => "ma. ṭṭha."
  ],
  [
    "id" => 151,
    "book" => 133,
    "name" => "Sagāthāvagga-aṭṭhakathā",
    "term" => "saṃyuttanikāya-aṭṭhakathā",
    "v_title" => "saṃyuttanikāya-aṭṭhakathā",
    "m_title" => "saṃyuttanikāya-aṭṭhakathā",
    "p_title" => "saṃyuttanikāya-aṭṭhakathā",
    "abbr" => "saṃ. ṭṭha."
  ],
  [
    "id" => 152,
    "book" => 134,
    "name" => "Nidānavagga-aṭṭhakathā",
    "term" => "saṃyuttanikāya-aṭṭhakathā",
    "v_title" => "saṃyuttanikāya-aṭṭhakathā",
    "m_title" => "saṃyuttanikāya-aṭṭhakathā",
    "p_title" => "saṃyuttanikāya-aṭṭhakathā",
    "abbr" => "saṃ. ṭṭha."
  ],
  [
    "id" => 153,
    "book" => 135,
    "name" => "Khandhavagga-aṭṭhakathā",
    "term" => "saṃyuttanikāya-aṭṭhakathā",
    "v_title" => "saṃyuttanikāya-aṭṭhakathā",
    "m_title" => "saṃyuttanikāya-aṭṭhakathā",
    "p_title" => "saṃyuttanikāya-aṭṭhakathā",
    "abbr" => "saṃ. ṭṭha."
  ],
  [
    "id" => 154,
    "book" => 136,
    "name" => "Saḷāyatanavagga-aṭṭhakathā",
    "term" => "saṃyuttanikāya-aṭṭhakathā",
    "v_title" => "saṃyuttanikāya-aṭṭhakathā",
    "m_title" => "saṃyuttanikāya-aṭṭhakathā",
    "p_title" => "saṃyuttanikāya-aṭṭhakathā",
    "abbr" => "saṃ. ṭṭha."
  ],
  [
    "id" => 155,
    "book" => 137,
    "name" => "Mahāvagga-aṭṭhakathā",
    "term" => "saṃyuttanikāya-aṭṭhakathā",
    "v_title" => "saṃyuttanikāya-aṭṭhakathā",
    "m_title" => "saṃyuttanikāya-aṭṭhakathā",
    "p_title" => "saṃyuttanikāya-aṭṭhakathā",
    "abbr" => "saṃ. ṭṭha."
  ],
  [
    "id" => 156,
    "book" => 138,
    "name" => "Pārājikakaṇḍa-aṭṭhakathā",
    "term" => "vinaya-aṭṭhakathā",
    "v_title" => "vinaya-aṭṭhakathā",
    "m_title" => "pārājikakaṇḍa-aṭṭhakathā",
    "p_title" => "vinaya-aṭṭhakathā",
    "abbr" => "vi. ṭṭha."
  ],
  [
    "id" => 157,
    "book" => 139,
    "name" => "Pācittiya-aṭṭhakathā",
    "term" => "vinaya-aṭṭhakathā",
    "v_title" => "vinaya-aṭṭhakathā",
    "m_title" => "pācittiyādi-aṭṭhakathā",
    "p_title" => "vinaya-aṭṭhakathā",
    "abbr" => "vi. ṭṭha."
  ],
  [
    "id" => 158,
    "book" => 140,
    "name" => "Mahāvagga-aṭṭhakathā",
    "term" => "vinaya-aṭṭhakathā",
    "v_title" => "vinaya-aṭṭhakathā",
    "m_title" => "pācittiyādi-aṭṭhakathā",
    "p_title" => "vinaya-aṭṭhakathā",
    "abbr" => "vi. ṭṭha."
  ],
  [
    "id" => 159,
    "book" => 141,
    "name" => "Cūḷavagga-aṭṭhakathā",
    "term" => "vinaya-aṭṭhakathā",
    "v_title" => "vinaya-aṭṭhakathā",
    "m_title" => "cūḷavaggādi-aṭṭhakathā",
    "p_title" => "vinaya-aṭṭhakathā",
    "abbr" => "vi. ṭṭha."
  ],
  [
    "id" => 160,
    "book" => 142,
    "name" => "Parivāra-aṭṭhakathā",
    "term" => "vinaya-aṭṭhakathā",
    "v_title" => "vinaya-aṭṭhakathā",
    "m_title" => "cūḷavaggādi-aṭṭhakathā",
    "p_title" => "vinaya-aṭṭhakathā",
    "abbr" => "vi. ṭṭha."
  ],
  [
    "id" => 161,
    "book" => 143,
    "name" => "Therāpadānapāḷi(Paṭhamo bhāgo)",
    "term" => "therāpadānapāḷi",
    "v_title" => "therāpadānapāḷi",
    "m_title" => "therāpadānapāḷi",
    "p_title" => "therāpadānapāḷi",
    "abbr" => "apa."
  ],
  [
    "id" => 162,
    "book" => 144,
    "name" => "Therāpadānapāḷi(Dutiyo bhāgo)",
    "term" => "therāpadānapāḷi",
    "v_title" => "therāpadānapāḷi",
    "m_title" => "therāpadānapāḷi",
    "p_title" => "therāpadānapāḷi",
    "abbr" => "apa."
  ],
  [
    "id" => 163,
    "book" => 144,
    "name" => "therīapadānapāḷi",
    "term" => "therīapadānapāḷi",
    "v_title" => "therīapadānapāḷi",
    "m_title" => "therīapadānapāḷi",
    "p_title" => "therīapadānapāḷi",
    "abbr" => "therīapadānapāḷi"
  ],
  [
    "id" => 164,
    "book" => 145,
    "name" => "Buddhavaṃsapāḷi",
    "term" => "buddhavaṃsapāḷi",
    "v_title" => "buddhavaṃsapāḷi",
    "m_title" => "buddhavaṃsapāḷi",
    "p_title" => "buddhavaṃsapāḷi",
    "abbr" => "buddhavaṃ."
  ],
  [
    "id" => 165,
    "book" => 146,
    "name" => "Cariyāpiṭakapāḷi",
    "term" => "cariyāpiṭakapāḷi",
    "v_title" => "cariyāpiṭakapāḷi",
    "m_title" => "cariyāpiṭakapāḷi",
    "p_title" => "cariyāpiṭakapāḷi",
    "abbr" => "cariyā."
  ],
  [
    "id" => 166,
    "book" => 147,
    "name" => "Jātakapāḷi(Dutiyo bhāgo)",
    "term" => "jātakapāḷi",
    "v_title" => "jātakapāḷi",
    "m_title" => "jātakapāḷi",
    "p_title" => "jātakapāḷi",
    "abbr" => "jā."
  ],
  [
    "id" => 167,
    "book" => 148,
    "name" => "Jātakapāḷi(Paṭhamo bhāgo)",
    "term" => "jātakapāḷi",
    "v_title" => "jātakapāḷi",
    "m_title" => "jātakapāḷi",
    "p_title" => "jātakapāḷi",
    "abbr" => "jā."
  ],
  [
    "id" => 168,
    "book" => 149,
    "name" => "Mahāniddesapāḷi",
    "term" => "mahāniddesapāḷi",
    "v_title" => "mahāniddesapāḷi",
    "m_title" => "mahāniddesapāḷi",
    "p_title" => "mahāniddesapāḷi",
    "abbr" => "mahāni."
  ],
  [
    "id" => 169,
    "book" => 150,
    "name" => "Cūḷaniddesapāḷi",
    "term" => "cūḷaniddesapāḷi",
    "v_title" => "cūḷaniddesapāḷi",
    "m_title" => "cūḷaniddesapāḷi",
    "p_title" => "cūḷaniddesapāḷi",
    "abbr" => "cūḷani."
  ],
  [
    "id" => 170,
    "book" => 151,
    "name" => "Paṭisambhidāmaggapāḷi",
    "term" => "paṭisambhidāmaggapāḷi",
    "v_title" => "paṭisambhidāmaggapāḷi",
    "m_title" => "paṭisambhidāmaggapāḷi",
    "p_title" => "paṭisambhidāmaggapāḷi",
    "abbr" => "paṭisaṃ."
  ],
  [
    "id" => 171,
    "book" => 152,
    "name" => "Milindapañhapāḷi",
    "term" => "milindapañhapāḷi",
    "v_title" => "milindapañhapāḷi",
    "m_title" => "milindapañhapāḷi",
    "p_title" => "milindapañhapāḷi",
    "abbr" => "milinda."
  ],
  [
    "id" => 172,
    "book" => 153,
    "name" => "Nettippakaraṇapāḷi",
    "term" => "nettippakaraṇapāḷi",
    "v_title" => "nettippakaraṇapāḷi",
    "m_title" => "nettippakaraṇapāḷi",
    "p_title" => "nettippakaraṇapāḷi",
    "abbr" => "netti."
  ],
  [
    "id" => 173,
    "book" => 154,
    "name" => "Khuddakapāṭhapāḷi",
    "term" => "khuddakapāṭhapāḷi",
    "v_title" => "khuddakapāṭhapāḷi",
    "m_title" => "khuddakapāṭhapāḷi",
    "p_title" => "khuddakapāṭhapāḷi",
    "abbr" => "khuddaka."
  ],
  [
    "id" => 174,
    "book" => 155,
    "name" => "Peṭakopadesapāḷi",
    "term" => "peṭakopadesapāḷi",
    "v_title" => "peṭakopadesapāḷi",
    "m_title" => "peṭakopadesapāḷi",
    "p_title" => "peṭakopadesapāḷi",
    "abbr" => "peṭako."
  ],
  [
    "id" => 175,
    "book" => 156,
    "name" => "Dhammapadapāḷi",
    "term" => "dhammapadapāḷi",
    "v_title" => "dhammapadapāḷi",
    "m_title" => "dhammapadapāḷi",
    "p_title" => "dhammapadapāḷi",
    "abbr" => "dhamma."
  ],
  [
    "id" => 176,
    "book" => 157,
    "name" => "Udānapāḷi",
    "term" => "udānapāḷi",
    "v_title" => "udānapāḷi",
    "m_title" => "udānapāḷi",
    "p_title" => "udānapāḷi",
    "abbr" => "udāna."
  ],
  [
    "id" => 177,
    "book" => 158,
    "name" => "Itivuttakapāḷi",
    "term" => "itivuttakapāḷi",
    "v_title" => "itivuttakapāḷi",
    "m_title" => "itivuttakapāḷi",
    "p_title" => "itivuttakapāḷi",
    "abbr" => "itivutta."
  ],
  [
    "id" => 178,
    "book" => 159,
    "name" => "Suttanipātapāḷi",
    "term" => "suttanipātapāḷi",
    "v_title" => "suttanipātapāḷi",
    "m_title" => "suttanipātapāḷi",
    "p_title" => "suttanipātapāḷi",
    "abbr" => "suttani."
  ],
  [
    "id" => 179,
    "book" => 160,
    "name" => "Vimānavatthupāḷi",
    "term" => "vimānavatthupāḷi",
    "v_title" => "vimānavatthupāḷi",
    "m_title" => "vimānavatthupāḷi",
    "p_title" => "vimānavatthupāḷi",
    "abbr" => "vimāna."
  ],
  [
    "id" => 180,
    "book" => 161,
    "name" => "Petavatthupāḷi",
    "term" => "petavatthupāḷi",
    "v_title" => "petavatthupāḷi",
    "m_title" => "petavatthupāḷi",
    "p_title" => "petavatthupāḷi",
    "abbr" => "peta."
  ],
  [
    "id" => 181,
    "book" => 162,
    "name" => "Theragāthāpāḷi",
    "term" => "theragāthāpāḷi",
    "v_title" => "theragāthāpāḷi",
    "m_title" => "theragāthāpāḷi",
    "p_title" => "theragāthāpāḷi",
    "abbr" => "theragāthā."
  ],
  [
    "id" => 182,
    "book" => 163,
    "name" => "Therīgāthāpāḷi",
    "term" => "therīgāthāpāḷi",
    "v_title" => "therīgāthāpāḷi",
    "m_title" => "therīgāthāpāḷi",
    "p_title" => "therīgāthāpāḷi",
    "abbr" => "therī."
  ],
  [
    "id" => 183,
    "book" => 164,
    "name" => "Mūlapaṇṇāsapāḷi",
    "term" => "majjhimanikaya",
    "v_title" => "majjhimanikaya",
    "m_title" => "majjhimanikaya",
    "p_title" => "majjhimanikaya",
    "abbr" => "ma."
  ],
  [
    "id" => 184,
    "book" => 165,
    "name" => "Majjhimapaṇṇāsapāḷi",
    "term" => "majjhimanikaya",
    "v_title" => "majjhimanikaya",
    "m_title" => "majjhimanikaya",
    "p_title" => "majjhimanikaya",
    "abbr" => "ma."
  ],
  [
    "id" => 185,
    "book" => 166,
    "name" => "Uparipaṇṇāsapāḷi",
    "term" => "majjhimanikaya",
    "v_title" => "majjhimanikaya",
    "m_title" => "majjhimanikaya",
    "p_title" => "majjhimanikaya",
    "abbr" => "ma."
  ],
  [
    "id" => 186,
    "book" => 167,
    "name" => "Sagāthāvaggo",
    "term" => "saṃyuttanikāya",
    "v_title" => "saṃyuttanikāya",
    "m_title" => "saṃyuttanikāya",
    "p_title" => "saṃyuttanikāya",
    "abbr" => "saṃ."
  ],
  [
    "id" => 187,
    "book" => 168,
    "name" => "Nidānavaggo",
    "term" => "saṃyuttanikāya",
    "v_title" => "saṃyuttanikāya",
    "m_title" => "saṃyuttanikāya",
    "p_title" => "saṃyuttanikāya",
    "abbr" => "saṃ."
  ],
  [
    "id" => 188,
    "book" => 169,
    "name" => "Khandhavaggo",
    "term" => "saṃyuttanikāya",
    "v_title" => "saṃyuttanikāya",
    "m_title" => "saṃyuttanikāya",
    "p_title" => "saṃyuttanikāya",
    "abbr" => "saṃ."
  ],
  [
    "id" => 189,
    "book" => 170,
    "name" => "Saḷāyatanavaggo",
    "term" => "saṃyuttanikāya",
    "v_title" => "saṃyuttanikāya",
    "m_title" => "saṃyuttanikāya",
    "p_title" => "saṃyuttanikāya",
    "abbr" => "saṃ."
  ],
  [
    "id" => 190,
    "book" => 171,
    "name" => "Mahāvaggo",
    "term" => "saṃyuttanikāya",
    "v_title" => "saṃyuttanikāya",
    "m_title" => "saṃyuttanikāya",
    "p_title" => "saṃyuttanikāya",
    "abbr" => "saṃ."
  ],
  [
    "id" => 191,
    "book" => 172,
    "name" => "dhammasaṅgaṇī-mūlaṭīkā",
    "term" => "dhammasaṅgaṇī-mūlaṭīkā",
    "v_title" => "dhammasaṅgaṇī-mūlaṭīkā",
    "m_title" => "dhammasaṅgaṇī-mūlaṭīkā",
    "p_title" => "dhammasaṅgaṇī-mūlaṭīkā",
    "abbr" => "mūlaṭī. 1"
  ],
  [
    "id" => 192,
    "book" => 173,
    "name" => "vibhaṅga-mūlaṭīkā",
    "term" => "vibhaṅga-mūlaṭīkā",
    "v_title" => "vibhaṅga-mūlaṭīkā",
    "m_title" => "vibhaṅga-mūlaṭīkā",
    "p_title" => "vibhaṅga-mūlaṭīkā",
    "abbr" => "mūlaṭī. 2"
  ],
  [
    "id" => 193,
    "book" => 173,
    "name" => "Vibhaṅga-anuṭīkā",
    "term" => "vibhaṅga-anuṭīkā",
    "v_title" => "vibhaṅga-anuṭīkā",
    "m_title" => "vibhaṅga-anuṭīkā",
    "p_title" => "vibhaṅga-anuṭīkā",
    "abbr" => "anuṭī. 2"
  ],
  [
    "id" => 194,
    "book" => 174,
    "name" => "pañcapakaraṇa-mūlaṭīkā",
    "term" => "pañcapakaraṇa-mūlaṭīkā",
    "v_title" => "pañcapakaraṇa-mūlaṭīkā",
    "m_title" => "pañcapakaraṇa-mūlaṭīkā",
    "p_title" => "pañcapakaraṇa-mūlaṭīkā",
    "abbr" => "mūlaṭī. 3"
  ],
  [
    "id" => 195,
    "book" => 174,
    "name" => "mūlaṭīkā",
    "term" => "pañcapakaraṇa-mūlaṭīkā",
    "v_title" => "pañcapakaraṇa-mūlaṭīkā",
    "m_title" => "pañcapakaraṇa-mūlaṭīkā",
    "p_title" => "pañcapakaraṇa-mūlaṭīkā",
    "abbr" => "mūlaṭī. 3"
  ],
  [
    "id" => 196,
    "book" => 174,
    "name" => "mūlaṭīkā",
    "term" => "pañcapakaraṇa-mūlaṭīkā",
    "v_title" => "pañcapakaraṇa-mūlaṭīkā",
    "m_title" => "pañcapakaraṇa-mūlaṭīkā",
    "p_title" => "pañcapakaraṇa-mūlaṭīkā",
    "abbr" => "mūlaṭī. 3"
  ],
  [
    "id" => 197,
    "book" => 174,
    "name" => "mūlaṭīkā",
    "term" => "pañcapakaraṇa-mūlaṭīkā",
    "v_title" => "pañcapakaraṇa-mūlaṭīkā",
    "m_title" => "pañcapakaraṇa-mūlaṭīkā",
    "p_title" => "pañcapakaraṇa-mūlaṭīkā",
    "abbr" => "mūlaṭī. 3"
  ],
  [
    "id" => 198,
    "book" => 174,
    "name" => "mūlaṭīkā",
    "term" => "pañcapakaraṇa-mūlaṭīkā",
    "v_title" => "pañcapakaraṇa-mūlaṭīkā",
    "m_title" => "pañcapakaraṇa-mūlaṭīkā",
    "p_title" => "pañcapakaraṇa-mūlaṭīkā",
    "abbr" => "mūlaṭī. 3"
  ],
  [
    "id" => 199,
    "book" => 175,
    "name" => "dhammasaṅgaṇī-anuṭīkā",
    "term" => "dhammasaṅgaṇī-anuṭīkā",
    "v_title" => "dhammasaṅgaṇī-anuṭīkā",
    "m_title" => "dhammasaṅgaṇī-anuṭīkā",
    "p_title" => "dhammasaṅgaṇī-anuṭīkā",
    "abbr" => "anuṭī. 1"
  ],
  [
    "id" => 200,
    "book" => 176,
    "name" => "dhātukathāpakaraṇa-anuṭīkā",
    "term" => "pañcapakaraṇa-anuṭīkā",
    "v_title" => "pañcapakaraṇa-anuṭīkā",
    "m_title" => "pañcapakaraṇa-anuṭīkā",
    "p_title" => "pañcapakaraṇa-anuṭīkā",
    "abbr" => "anuṭī. 3"
  ],
  [
    "id" => 201,
    "book" => 176,
    "name" => "puggalapaññattipakaraṇa-anuṭīkā",
    "term" => "pañcapakaraṇa-anuṭīkā",
    "v_title" => "pañcapakaraṇa-anuṭīkā",
    "m_title" => "pañcapakaraṇa-anuṭīkā",
    "p_title" => "pañcapakaraṇa-anuṭīkā",
    "abbr" => "anuṭī. 3"
  ],
  [
    "id" => 202,
    "book" => 176,
    "name" => "kathāvatthupakaraṇa-anuṭīkā",
    "term" => "pañcapakaraṇa-anuṭīkā",
    "v_title" => "pañcapakaraṇa-anuṭīkā",
    "m_title" => "pañcapakaraṇa-anuṭīkā",
    "p_title" => "pañcapakaraṇa-anuṭīkā",
    "abbr" => "anuṭī. 3"
  ],
  [
    "id" => 203,
    "book" => 176,
    "name" => "anuṭīkā",
    "term" => "pañcapakaraṇa-anuṭīkā",
    "v_title" => "pañcapakaraṇa-anuṭīkā",
    "m_title" => "pañcapakaraṇa-anuṭīkā",
    "p_title" => "pañcapakaraṇa-anuṭīkā",
    "abbr" => "anuṭī. 3"
  ],
  [
    "id" => 204,
    "book" => 176,
    "name" => "anuṭīkā",
    "term" => "pañcapakaraṇa-anuṭīkā",
    "v_title" => "pañcapakaraṇa-anuṭīkā",
    "m_title" => "pañcapakaraṇa-anuṭīkā",
    "p_title" => "pañcapakaraṇa-anuṭīkā",
    "abbr" => "anuṭī. 3"
  ],
  [
    "id" => 205,
    "book" => 177,
    "name" => "Ganthārambhakathā",
    "term" => "abhidhammāvatāro",
    "v_title" => "abhidhammāvatāro",
    "m_title" => "abhidhammāvatāro",
    "p_title" => "abhidhammāvatāro",
    "abbr" => "abhidhammāvatāro"
  ],
  [
    "id" => 206,
    "book" => 177,
    "name" => "nāmarūpaparicchedo",
    "term" => "nāmarūpaparicchedo",
    "v_title" => "nāmarūpaparicchedo",
    "m_title" => "nāmarūpaparicchedo",
    "p_title" => "nāmarūpaparicchedo",
    "abbr" => "nāmarūpaparicchedo"
  ],
  [
    "id" => 207,
    "book" => 177,
    "name" => "paramatthavinicchayo",
    "term" => "paramatthavinicchayo",
    "v_title" => "paramatthavinicchayo",
    "m_title" => "paramatthavinicchayo",
    "p_title" => "paramatthavinicchayo",
    "abbr" => "paramatthavinicchayo"
  ],
  [
    "id" => 208,
    "book" => 177,
    "name" => "saccasaṅkhepo",
    "term" => "saccasaṅkhepo",
    "v_title" => "saccasaṅkhepo",
    "m_title" => "saccasaṅkhepo",
    "p_title" => "saccasaṅkhepo",
    "abbr" => "saccasaṅkhepo"
  ],
  [
    "id" => 209,
    "book" => 178,
    "name" => "Abhidhammatthasaṅgaho",
    "term" => "abhidhammatthasaṅgaho",
    "v_title" => "abhidhammatthasaṅgaho",
    "m_title" => "abhidhammatthasaṅgaho",
    "p_title" => "abhidhammatthasaṅgaho",
    "abbr" => "abhidhammatthasaṅgaho"
  ],
  [
    "id" => 210,
    "book" => 178,
    "name" => "abhidhammatthavibhāvinīṭīkā",
    "term" => "abhidhammatthavibhāvinīṭīkā",
    "v_title" => "abhidhammatthavibhāvinīṭīkā",
    "m_title" => "abhidhammatthavibhāvinīṭīkā",
    "p_title" => "abhidhammatthavibhāvinīṭīkā",
    "abbr" => "abhidhammatthavibhāvinīṭīkā"
  ],
  [
    "id" => 211,
    "book" => 179,
    "name" => "Paṭhamo paricchedo",
    "term" => "abhidhammāvatāra-purāṇaṭīkā",
    "v_title" => "abhidhammāvatāra-purāṇaṭīkā",
    "m_title" => "abhidhammāvatāra-purāṇaṭīkā",
    "p_title" => "abhidhammāvatāra-purāṇaṭīkā",
    "abbr" => "abhidhammāvatāra-purāṇaṭīkā"
  ],
  [
    "id" => 212,
    "book" => 179,
    "name" => "abhidhammāvatāra-abhinavaṭīkā",
    "term" => "abhidhammāvatāra-abhinavaṭīkā",
    "v_title" => "abhidhammāvatāra-abhinavaṭīkā",
    "m_title" => "abhidhammāvatāra-abhinavaṭīkā",
    "p_title" => "abhidhammāvatāra-abhinavaṭīkā",
    "abbr" => "abhidhammāvatāra-abhinavaṭīkā"
  ],
  [
    "id" => 213,
    "book" => 180,
    "name" => "Abhidhammamātikāpāḷi",
    "term" => "abhidhammamātikāpāḷi",
    "v_title" => "abhidhammamātikāpāḷi",
    "m_title" => "abhidhammamātikāpāḷi",
    "p_title" => "abhidhammamātikāpāḷi",
    "abbr" => "abhidhammamātikāpāḷi"
  ],
  [
    "id" => 214,
    "book" => 180,
    "name" => "mohavicchedanī",
    "term" => "mohavicchedanī",
    "v_title" => "mohavicchedanī",
    "m_title" => "mohavicchedanī",
    "p_title" => "mohavicchedanī",
    "abbr" => "mohavicchedanī"
  ],
  [
    "id" => 215,
    "book" => 181,
    "name" => "Ekakanipāta-ṭīkā",
    "term" => "aṅguttaranikāya-ṭīkā",
    "v_title" => "aṅguttaranikāya-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "aṅguttaranikāya-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 216,
    "book" => 182,
    "name" => "Dukanipāta-ṭīkā",
    "term" => "aṅguttaranikāya-ṭīkā",
    "v_title" => "aṅguttaranikāya-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "aṅguttaranikāya-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 217,
    "book" => 182,
    "name" => "tikanipāta-ṭīkā",
    "term" => "tikanipāta-ṭīkā",
    "v_title" => "tikanipāta-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "tikanipāta-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 218,
    "book" => 182,
    "name" => "catukkanipāta-ṭīkā",
    "term" => "catukkanipāta-ṭīkā",
    "v_title" => "catukkanipāta-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "catukkanipāta-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 219,
    "book" => 183,
    "name" => "Pañcakanipāta-ṭīkā",
    "term" => "aṅguttaranikāya-ṭīkā",
    "v_title" => "aṅguttaranikāya-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "aṅguttaranikāya-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 220,
    "book" => 183,
    "name" => "chakkanipāta-ṭīkā",
    "term" => "chakkanipāta-ṭīkā",
    "v_title" => "chakkanipāta-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "chakkanipāta-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 221,
    "book" => 183,
    "name" => "sattakanipāta-ṭīkā",
    "term" => "sattakanipāta-ṭīkā",
    "v_title" => "sattakanipāta-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "sattakanipāta-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 222,
    "book" => 184,
    "name" => "Aṭṭhakanipāta-ṭīkā",
    "term" => "aṅguttaranikāya-ṭīkā",
    "v_title" => "aṅguttaranikāya-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "aṅguttaranikāya-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 223,
    "book" => 184,
    "name" => "navakanipāta-ṭīkā",
    "term" => "navakanipāta-ṭīkā",
    "v_title" => "navakanipāta-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "navakanipāta-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 224,
    "book" => 184,
    "name" => "dasakanipāta-ṭīkā",
    "term" => "dasakanipāta-ṭīkā",
    "v_title" => "dasakanipāta-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "dasakanipāta-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 225,
    "book" => 184,
    "name" => "ekādasakanipāta-ṭīkā",
    "term" => "ekādasakanipāta-ṭīkā",
    "v_title" => "ekādasakanipāta-ṭīkā",
    "m_title" => "aṅguttaranikāya-ṭīkā",
    "p_title" => "ekādasakanipāta-ṭīkā",
    "abbr" => "aṃ. ṭī."
  ],
  [
    "id" => 226,
    "book" => 185,
    "name" => "Sīlakkhandhavaggaṭīkā",
    "term" => "dīghanikāya-ṭīkā",
    "v_title" => "dīghanikāya-ṭīkā",
    "m_title" => "dīghanikāya-ṭīkā",
    "p_title" => "dīghanikāya-ṭīkā",
    "abbr" => "dī. ṭī."
  ],
  [
    "id" => 227,
    "book" => 186,
    "name" => "Mahāvaggaṭīkā",
    "term" => "dīghanikāya-ṭīkā",
    "v_title" => "dīghanikāya-ṭīkā",
    "m_title" => "dīghanikāya-ṭīkā",
    "p_title" => "dīghanikāya-ṭīkā",
    "abbr" => "dī. ṭī."
  ],
  [
    "id" => 228,
    "book" => 187,
    "name" => "Pāthikavaggaṭīkā",
    "term" => "dīghanikāya-ṭīkā",
    "v_title" => "dīghanikāya-ṭīkā",
    "m_title" => "dīghanikāya-ṭīkā",
    "p_title" => "dīghanikāya-ṭīkā",
    "abbr" => "dī. ṭī."
  ],
  [
    "id" => 229,
    "book" => 188,
    "name" => "Sīlakkhandhavaggaabhinavaṭīkā",
    "term" => "dīghanikāya-abhinavaṭīkā",
    "v_title" => "dīghanikāya-abhinavaṭīkā",
    "m_title" => "dīghanikāya-abhinavaṭīkā",
    "p_title" => "dīghanikāya-abhinavaṭīkā",
    "abbr" => "dī. abhi. ṭī."
  ],
  [
    "id" => 230,
    "book" => 189,
    "name" => "Sīlakkhandhavaggaabhinavaṭīkā",
    "term" => "dīghanikāya-abhinavaṭīkā",
    "v_title" => "dīghanikāya-abhinavaṭīkā",
    "m_title" => "dīghanikāya-abhinavaṭīkā",
    "p_title" => "dīghanikāya-abhinavaṭīkā",
    "abbr" => "dī. abhi. ṭī."
  ],
  [
    "id" => 231,
    "book" => 190,
    "name" => "Nettippakaraṇa-ṭīkā",
    "term" => "nettippakaraṇa-ṭīkā",
    "v_title" => "nettippakaraṇa-ṭīkā",
    "m_title" => "nettippakaraṇa-ṭīkā",
    "p_title" => "nettippakaraṇa-ṭīkā",
    "abbr" => "netti. ṭī."
  ],
  [
    "id" => 232,
    "book" => 191,
    "name" => "Nettivibhāvinī",
    "term" => "nettivibhāvinī",
    "v_title" => "nettivibhāvinī",
    "m_title" => "nettivibhāvinī",
    "p_title" => "nettivibhāvinī",
    "abbr" => "netti. vibhā."
  ],
  [
    "id" => 233,
    "book" => 192,
    "name" => "Mūlapaṇṇāsa-ṭīkā",
    "term" => "majjhimanikaya-ṭīkā",
    "v_title" => "majjhimanikaya-ṭīkā",
    "m_title" => "majjhimanikaya-ṭīkā",
    "p_title" => "majjhimanikaya-ṭīkā",
    "abbr" => "ma. ṭī."
  ],
  [
    "id" => 234,
    "book" => 193,
    "name" => "Majjhimapaṇṇāsaṭīkā",
    "term" => "majjhimanikaya-ṭīkā",
    "v_title" => "majjhimanikaya-ṭīkā",
    "m_title" => "majjhimanikaya-ṭīkā",
    "p_title" => "majjhimanikaya-ṭīkā",
    "abbr" => "ma. ṭī."
  ],
  [
    "id" => 235,
    "book" => 194,
    "name" => "Uparipaṇṇāsa-ṭīkā",
    "term" => "majjhimanikaya-ṭīkā",
    "v_title" => "majjhimanikaya-ṭīkā",
    "m_title" => "majjhimanikaya-ṭīkā",
    "p_title" => "majjhimanikaya-ṭīkā",
    "abbr" => "ma. ṭī."
  ],
  [
    "id" => 236,
    "book" => 195,
    "name" => "Sagāthāvaggaṭīkā",
    "term" => "saṃyuttanikāya-ṭīkā",
    "v_title" => "saṃyuttanikāya-ṭīkā",
    "m_title" => "saṃyuttanikāya-ṭīkā",
    "p_title" => "saṃyuttanikāya-ṭīkā",
    "abbr" => "saṃ. ṭī."
  ],
  [
    "id" => 237,
    "book" => 196,
    "name" => "Nidānavaggaṭīkā",
    "term" => "saṃyuttanikāya-ṭīkā",
    "v_title" => "saṃyuttanikāya-ṭīkā",
    "m_title" => "saṃyuttanikāya-ṭīkā",
    "p_title" => "saṃyuttanikāya-ṭīkā",
    "abbr" => "saṃ. ṭī."
  ],
  [
    "id" => 238,
    "book" => 197,
    "name" => "Khandhavaggaṭīkā",
    "term" => "saṃyuttanikāya-ṭīkā",
    "v_title" => "saṃyuttanikāya-ṭīkā",
    "m_title" => "saṃyuttanikāya-ṭīkā",
    "p_title" => "saṃyuttanikāya-ṭīkā",
    "abbr" => "saṃ. ṭī."
  ],
  [
    "id" => 239,
    "book" => 198,
    "name" => "Saḷāyatanavaggaṭīkā",
    "term" => "saṃyuttanikāya-ṭīkā",
    "v_title" => "saṃyuttanikāya-ṭīkā",
    "m_title" => "saṃyuttanikāya-ṭīkā",
    "p_title" => "saṃyuttanikāya-ṭīkā",
    "abbr" => "saṃ. ṭī."
  ],
  [
    "id" => 240,
    "book" => 199,
    "name" => "Mahāvaggaṭīkā",
    "term" => "saṃyuttanikāya-ṭīkā",
    "v_title" => "saṃyuttanikāya-ṭīkā",
    "m_title" => "saṃyuttanikāya-ṭīkā",
    "p_title" => "saṃyuttanikāya-ṭīkā",
    "abbr" => "saṃ. ṭī."
  ],
  [
    "id" => 241,
    "book" => 200,
    "name" => "Vinayavinicchayo",
    "term" => "vinayavinicchayo",
    "v_title" => "vinayavinicchayo",
    "m_title" => "vinayavinicchayo",
    "p_title" => "vinayavinicchayo",
    "abbr" => "vinayavinicchayo"
  ],
  [
    "id" => 242,
    "book" => 200,
    "name" => "uttaravinicchayo",
    "term" => "uttaravinicchayo",
    "v_title" => "uttaravinicchayo",
    "m_title" => "uttaravinicchayo",
    "p_title" => "uttaravinicchayo",
    "abbr" => "uttaravinicchayo"
  ],
  [
    "id" => 243,
    "book" => 201,
    "name" => "Vinayavinicchayaṭīkā(Paṭhamo bhāgo)",
    "term" => "vinayavinicchaya-ṭīkā",
    "v_title" => "vinayavinicchaya-ṭīkā",
    "m_title" => "vinayavinicchaya-ṭīkā",
    "p_title" => "vinayavinicchaya-ṭīkā",
    "abbr" => "vinayavinicchaya-ṭīkā"
  ],
  [
    "id" => 244,
    "book" => 201,
    "name" => "uttaravinicchaya-ṭīkā",
    "term" => "uttaravinicchaya-ṭīkā",
    "v_title" => "uttaravinicchaya-ṭīkā",
    "m_title" => "uttaravinicchaya-ṭīkā",
    "p_title" => "uttaravinicchaya-ṭīkā",
    "abbr" => "uttaravinicchaya-ṭīkā"
  ],
  [
    "id" => 245,
    "book" => 202,
    "name" => "Pācityādiyojanā",
    "term" => "pācityādiyojanā",
    "v_title" => "pācityādiyojanā",
    "m_title" => "pācityādiyojanā",
    "p_title" => "pācityādiyojanā",
    "abbr" => "pācityādiyojanā"
  ],
  [
    "id" => 246,
    "book" => 203,
    "name" => "Khuddasikkhā-mūlasikkhā",
    "term" => "khuddasikkhā",
    "v_title" => "khuddasikkhā",
    "m_title" => "khuddasikkhā",
    "p_title" => "khuddasikkhā",
    "abbr" => "khuddasikkhā"
  ],
  [
    "id" => 247,
    "book" => 203,
    "name" => "khuddasikkhā",
    "term" => "khuddasikkhā",
    "v_title" => "khuddasikkhā",
    "m_title" => "khuddasikkhā",
    "p_title" => "khuddasikkhā",
    "abbr" => "khuddasikkhā"
  ],
  [
    "id" => 248,
    "book" => 203,
    "name" => "khuddasikkhā",
    "term" => "khuddasikkhā",
    "v_title" => "khuddasikkhā",
    "m_title" => "khuddasikkhā",
    "p_title" => "khuddasikkhā",
    "abbr" => "khuddasikkhā"
  ],
  [
    "id" => 249,
    "book" => 203,
    "name" => "mūlasikkhā",
    "term" => "mūlasikkhā",
    "v_title" => "mūlasikkhā",
    "m_title" => "mūlasikkhā",
    "p_title" => "mūlasikkhā",
    "abbr" => "mūlasikkhā"
  ],
  [
    "id" => 250,
    "book" => 203,
    "name" => "mūlasikkhā",
    "term" => "mūlasikkhā",
    "v_title" => "mūlasikkhā",
    "m_title" => "mūlasikkhā",
    "p_title" => "mūlasikkhā",
    "abbr" => "mūlasikkhā"
  ],
  [
    "id" => 251,
    "book" => 204,
    "name" => "sāratthadīpanī-ṭīkā (paṭhamo bhāgo)",
    "term" => "sāratthadīpanī-ṭīkā",
    "v_title" => "sāratthadīpanī-ṭīkā",
    "m_title" => "sāratthadīpanī-ṭīkā",
    "p_title" => "sāratthadīpanī-ṭīkā",
    "abbr" => "sārattha. ṭī."
  ],
  [
    "id" => 252,
    "book" => 205,
    "name" => "sāratthadīpanī-ṭīkā (dutiyo bhāgo)",
    "term" => "sāratthadīpanī-ṭīkā",
    "v_title" => "sāratthadīpanī-ṭīkā",
    "m_title" => "sāratthadīpanī-ṭīkā",
    "p_title" => "sāratthadīpanī-ṭīkā",
    "abbr" => "sārattha. ṭī."
  ],
  [
    "id" => 253,
    "book" => 206,
    "name" => "sāratthadīpanī-ṭīkā (tatiyo bhāgo)",
    "term" => "sāratthadīpanī-ṭīkā",
    "v_title" => "sāratthadīpanī-ṭīkā",
    "m_title" => "sāratthadīpanī-ṭīkā",
    "p_title" => "sāratthadīpanī-ṭīkā",
    "abbr" => "sārattha. ṭī."
  ],
  [
    "id" => 254,
    "book" => 206,
    "name" => "sāratthadīpanī-ṭīkā",
    "term" => "sāratthadīpanī-ṭīkā",
    "v_title" => "sāratthadīpanī-ṭīkā",
    "m_title" => "sāratthadīpanī-ṭīkā",
    "p_title" => "sāratthadīpanī-ṭīkā",
    "abbr" => "sārattha. ṭī."
  ],
  [
    "id" => 255,
    "book" => 206,
    "name" => "sāratthadīpanī-ṭīkā",
    "term" => "sāratthadīpanī-ṭīkā",
    "v_title" => "sāratthadīpanī-ṭīkā",
    "m_title" => "sāratthadīpanī-ṭīkā",
    "p_title" => "sāratthadīpanī-ṭīkā",
    "abbr" => "sārattha. ṭī."
  ],
  [
    "id" => 256,
    "book" => 206,
    "name" => "sāratthadīpanī-ṭīkā",
    "term" => "sāratthadīpanī-ṭīkā",
    "v_title" => "sāratthadīpanī-ṭīkā",
    "m_title" => "sāratthadīpanī-ṭīkā",
    "p_title" => "sāratthadīpanī-ṭīkā",
    "abbr" => "sārattha. ṭī."
  ],
  [
    "id" => 257,
    "book" => 207,
    "name" => "Bhikkhupātimokkhapāḷi",
    "term" => "pātimokkhapāḷi",
    "v_title" => "pātimokkhapāḷi",
    "m_title" => "pātimokkhapāḷi",
    "p_title" => "pātimokkhapāḷi",
    "abbr" => "pātimokkha"
  ],
  [
    "id" => 258,
    "book" => 207,
    "name" => "pātimokkhapāḷi",
    "term" => "pātimokkhapāḷi",
    "v_title" => "pātimokkhapāḷi",
    "m_title" => "pātimokkhapāḷi",
    "p_title" => "pātimokkhapāḷi",
    "abbr" => "pātimokkha"
  ],
  [
    "id" => 259,
    "book" => 207,
    "name" => "kaṅkhāvitaraṇī",
    "term" => "kaṅkhāvitaraṇī",
    "v_title" => "kaṅkhāvitaraṇī",
    "m_title" => "kaṅkhāvitaraṇī",
    "p_title" => "kaṅkhāvitaraṇī",
    "abbr" => "kaṅkhā."
  ],
  [
    "id" => 260,
    "book" => 208,
    "name" => "Vinayasaṅgaha-aṭṭhakathā",
    "term" => "vinayasaṅgaha-aṭṭhakathā",
    "v_title" => "vinayasaṅgaha-aṭṭhakathā",
    "m_title" => "vinayasaṅgaha-aṭṭhakathā",
    "p_title" => "vinayasaṅgaha-aṭṭhakathā",
    "abbr" => "vinayasaṅgaha-aṭṭhakathā"
  ],
  [
    "id" => 261,
    "book" => 209,
    "name" => "Vajirabuddhi-ṭīkā",
    "term" => "vajirabuddhi-ṭīkā",
    "v_title" => "vajirabuddhi-ṭīkā",
    "m_title" => "vajirabuddhi-ṭīkā",
    "p_title" => "vajirabuddhi-ṭīkā",
    "abbr" => "vajīra. ṭī."
  ],
  [
    "id" => 262,
    "book" => 209,
    "name" => "vajirabuddhi-ṭīkā",
    "term" => "vajirabuddhi-ṭīkā",
    "v_title" => "vajirabuddhi-ṭīkā",
    "m_title" => "vajirabuddhi-ṭīkā",
    "p_title" => "vajirabuddhi-ṭīkā",
    "abbr" => "vajīra. ṭī."
  ],
  [
    "id" => 263,
    "book" => 209,
    "name" => "vajirabuddhi-ṭīkā",
    "term" => "vajirabuddhi-ṭīkā",
    "v_title" => "vajirabuddhi-ṭīkā",
    "m_title" => "vajirabuddhi-ṭīkā",
    "p_title" => "vajirabuddhi-ṭīkā",
    "abbr" => "vajīra. ṭī."
  ],
  [
    "id" => 264,
    "book" => 209,
    "name" => "vajirabuddhi-ṭīkā",
    "term" => "vajirabuddhi-ṭīkā",
    "v_title" => "vajirabuddhi-ṭīkā",
    "m_title" => "vajirabuddhi-ṭīkā",
    "p_title" => "vajirabuddhi-ṭīkā",
    "abbr" => "vajīra. ṭī."
  ],
  [
    "id" => 265,
    "book" => 209,
    "name" => "vajirabuddhi-ṭīkā",
    "term" => "vajirabuddhi-ṭīkā",
    "v_title" => "vajirabuddhi-ṭīkā",
    "m_title" => "vajirabuddhi-ṭīkā",
    "p_title" => "vajirabuddhi-ṭīkā",
    "abbr" => "vajīra. ṭī."
  ],
  [
    "id" => 266,
    "book" => 209,
    "name" => "vajirabuddhi-ṭīkā",
    "term" => "vajirabuddhi-ṭīkā",
    "v_title" => "vajirabuddhi-ṭīkā",
    "m_title" => "vajirabuddhi-ṭīkā",
    "p_title" => "vajirabuddhi-ṭīkā",
    "abbr" => "vajīra. ṭī."
  ],
  [
    "id" => 267,
    "book" => 210,
    "name" => "Vimativinodanī-ṭīkā",
    "term" => "vimativinodanī-ṭīkā",
    "v_title" => "vimativinodanī-ṭīkā",
    "m_title" => "vimativinodanī-ṭīkā",
    "p_title" => "vimativinodanī-ṭīkā",
    "abbr" => "vimati. ṭī."
  ],
  [
    "id" => 268,
    "book" => 210,
    "name" => "Vimativinodanī-ṭīkā",
    "term" => "Vimativinodanī-ṭīkā",
    "v_title" => "vimativinodanī-ṭīkā",
    "m_title" => "vimativinodanī-ṭīkā",
    "p_title" => "Vimativinodanī-ṭīkā",
    "abbr" => "vimati. ṭī."
  ],
  [
    "id" => 269,
    "book" => 210,
    "name" => "Vimativinodanī-ṭīkā",
    "term" => "Vimativinodanī-ṭīkā",
    "v_title" => "vimativinodanī-ṭīkā",
    "m_title" => "vimativinodanī-ṭīkā",
    "p_title" => "Vimativinodanī-ṭīkā",
    "abbr" => "vimati. ṭī."
  ],
  [
    "id" => 270,
    "book" => 210,
    "name" => "Vimativinodanī-ṭīkā",
    "term" => "Vimativinodanī-ṭīkā",
    "v_title" => "vimativinodanī-ṭīkā",
    "m_title" => "vimativinodanī-ṭīkā",
    "p_title" => "Vimativinodanī-ṭīkā",
    "abbr" => "vimati. ṭī."
  ],
  [
    "id" => 271,
    "book" => 210,
    "name" => "Vimativinodanī-ṭīkā",
    "term" => "Vimativinodanī-ṭīkā",
    "v_title" => "vimativinodanī-ṭīkā",
    "m_title" => "vimativinodanī-ṭīkā",
    "p_title" => "Vimativinodanī-ṭīkā",
    "abbr" => "vimati. ṭī."
  ],
  [
    "id" => 272,
    "book" => 211,
    "name" => "Vinayālaṅkāra-ṭīkā",
    "term" => "vinayālaṅkāra-ṭīkā",
    "v_title" => "vinayālaṅkāra-ṭīkā",
    "m_title" => "vinayālaṅkāra-ṭīkā",
    "p_title" => "vinayālaṅkāra-ṭīkā",
    "abbr" => "ālaṅkāra. ṭī."
  ],
  [
    "id" => 273,
    "book" => 212,
    "name" => "Kaṅkhāvitaraṇīpurāṇa-ṭīkā",
    "term" => "kaṅkhāvitaraṇīpurāṇa-ṭīkā",
    "v_title" => "kaṅkhāvitaraṇīpurāṇa-ṭīkā",
    "m_title" => "kaṅkhāvitaraṇīpurāṇa-ṭīkā",
    "p_title" => "kaṅkhāvitaraṇīpurāṇa-ṭīkā",
    "abbr" => "kaṅkhā. ṭī."
  ],
  [
    "id" => 274,
    "book" => 212,
    "name" => "Kaṅkhāvitaraṇī-abhinavaṭīkā",
    "term" => "kaṅkhāvitaraṇī-abhinavaṭīkā",
    "v_title" => "kaṅkhāvitaraṇī-abhinavaṭīkā",
    "m_title" => "kaṅkhāvitaraṇī-abhinavaṭīkā",
    "p_title" => "kaṅkhāvitaraṇī-abhinavaṭīkā",
    "abbr" => "kaṅkhā."
  ],
  [
    "id" => 275,
    "book" => 213,
    "name" => "Pārājikapāḷi",
    "term" => "pārājikapāḷi",
    "v_title" => "pārājikapāḷi",
    "m_title" => "pārājikapāḷi",
    "p_title" => "vinayapiṭaka",
    "abbr" => "vi."
  ],
  [
    "id" => 276,
    "book" => 214,
    "name" => "Pācittiyapāḷi",
    "term" => "pācittiyapāḷi",
    "v_title" => "pācittiyapāḷi",
    "m_title" => "pācittiyapāḷi",
    "p_title" => "vinayapiṭaka",
    "abbr" => "vi."
  ],
  [
    "id" => 277,
    "book" => 215,
    "name" => "Mahāvaggapāḷi",
    "term" => "mahāvaggapāḷi",
    "v_title" => "mahāvaggapāḷi",
    "m_title" => "mahāvaggapāḷi",
    "p_title" => "vinayapiṭaka",
    "abbr" => "vi."
  ],
  [
    "id" => 278,
    "book" => 216,
    "name" => "Cūḷavaggapāḷi",
    "term" => "cūḷavaggapāḷi",
    "v_title" => "cūḷavaggapāḷi",
    "m_title" => "cūḷavaggapāḷi",
    "p_title" => "vinayapiṭaka",
    "abbr" => "vi."
  ],
  [
    "id" => 279,
    "book" => 217,
    "name" => "Parivārapāḷi",
    "term" => "parivārapāḷi",
    "v_title" => "parivārapāḷi",
    "m_title" => "parivārapāḷi",
    "p_title" => "vinayapiṭaka",
    "abbr" => "vi."
  ],
  [
    "id" => 280,
    "book" => 139,
    "name" => "samantapāsādikā",
    "term" => "vinaya-aṭṭhakathā",
    "v_title" => "vinaya-aṭṭhakathā",
    "m_title" => "vinaya-aṭṭhakathā",
    "p_title" => "vinaya-aṭṭhakathā",
    "abbr" => "vi. ṭṭha."
  ],
  [
    "id" => 281,
    "book" => 214,
    "name" => "(VN)Bhikkhunīvibhaṅgo",
    "term" => "pācittiyapāḷi",
    "v_title" => "pācittiyapāḷi",
    "m_title" => "pācittiyapāḷi",
    "p_title" => "vinayapiṭaka",
    "abbr" => "vi."
  ]
  ];



    }
}
