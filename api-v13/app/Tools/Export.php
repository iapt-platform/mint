<?php

namespace App\Tools;

use Grpc\ChannelCredentials;
use Illuminate\Support\Facades\Log;
use Palm\Lily\V1\TexClient;
use Palm\Lily\V1\TexToRequest;

class Export
{
    public static function ToPdf($tex)
    {
        return Export::tex2pdf_lily($tex);
    }

    private static function tex2pdf_lily($tex)
    {
        $request = new TexToRequest;
        foreach ($tex as $key => $value) {
            $request->getFiles()[$value['name']] = $value['content'];
            // Log::info($value['name']);
            // Log::info($value['content']);
        }
        $host = config('mint.server.rpc.lily.host').':'.config('mint.server.rpc.lily.port');
        $client = new TexClient($host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);

        [$response, $status] = $client->ToPdf($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            echo 'ERROR: '.$status->code.', '.$status->details.PHP_EOL;

            return ['ok' => false,
                'code' => $status->code,
                'message' => $status->details,
            ];
        }

        return ['ok' => true,
            'content-type' => $response->getContentType(),
            'data' => $response->getPayload(),
        ];
    }
}
