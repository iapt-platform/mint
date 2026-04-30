<?php
// api-v12/app/Http/Controllers/DownloadController.php
namespace App\Http\Controllers;

use App\Services\PacketService;

class DownloadController extends Controller
{
    //
    public function index()
    {
        $packets = app(PacketService::class)->index();


        return view('library.download', compact('packets'));
    }
}
