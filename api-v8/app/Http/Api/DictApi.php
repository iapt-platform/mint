<?php
namespace App\Http\Api;
use App\Models\DictInfo;

class DictApi{
    public static function langOrder(string $lang){
        switch ($lang) {
            case 'zh':
                $output = ["zh","jp","en","my"];
                break;
            case 'en':
                $output = ["en","my"];
                break;
            case 'my':
                $output = ["my","en"];
                break;
            default:
                $output = [$lang,"en","my"];
                break;
        }
        $output[] = "others";
        return $output;
    }

    public static function dictOrder($lang){
        $output = [];
        switch ($lang) {
            case 'zh':
                $output = [
                "0d79e8e8-1430-4c99-a0f1-b74f2b4b26d8",	/*《巴汉词典》增订*/
                "f364d3dc-b611-471b-9a4f-531286b8c2c3",	/*《巴汉词典》Mahāñāṇo Bhikkhu编著*/
                "0e4dc5c8-a228-4693-92ba-7d42918d8a91",	/*汉译パーリ语辞典-黃秉榮*/
                "6aa9ec8b-bba4-4bcd-abd2-9eae015bad2b",	/*汉译パーリ语辞典-李瑩*/
                "eb99f8b4-c3e5-43af-9102-6a93fcb97db6",	/*パーリ语辞典--勘误表*/
                ];
                break;
            case 'jp':
                $output = [
                "91d3ec93-3811-4973-8d84-ced99179a0aa",	/*パーリ语辞典*/
                "6d6c6812-75e7-457d-874f-5b049ad4b6de",	/*パーリ语辞典-增补*/
                ];
                break;
            case 'en':
                $output = [
                "c6e70507-4a14-4687-8b70-2d0c7eb0cf21",	/*	Concise P-E Dict*/
                "6afb8c05-5cbe-422e-b691-0d4507450cb7",	/*	PTS P-E dictionary*/
                ];
                break;
            case 'my':
                $output =[
                "e740ef40-26d7-416e-96c2-925d6650ac6b",	/*	Tipiṭaka Pāḷi-Myanmar*/
                "beb45062-7c20-4047-bcd4-1f636ba443d1",	/*	U Hau Sein’s Pāḷi-Myanmar Dictionary*/
                "1e299ccb-4fc4-487d-8d72-08f63d84c809",	/*	Pali Roots Dictionary*/
                "6f9caea1-17fa-41f1-92e5-bd8e6e70e1d7",	/*	U Hau Sein’s Pāḷi-Myanmar*/
                ];
                break;
            case 'vi':
                $output = [
                "23f67523-fa03-48d9-9dda-ede80d578dd2",	/*	Pali Viet Dictionary*/
                "4ac8a0d5-9c6f-4b9f-983d-84288d47f993",	/*	Pali Viet Abhi-Terms*/
                "7c7ee287-35ba-4cf3-b87b-30f1fa6e57c9",	/*	Pali Viet Vinaya Terms*/
                ];
                break;
            default:
                $output = [];
                break;
        };
        $output[] = "others";
        return $output;
    }
    public static function getSysDict($name){
        $dict_info=  DictInfo::where('name',$name)
                    ->where('owner_id',config("mint.admin.root_uuid"))
                    ->first();
        if(!$dict_info){
            return false;
        }else{
            return $dict_info->id;
        }
    }
}
