<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\MdRender;

class TestMdRender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:md.render';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $markdown = "";
        //$markdown .= "[[isipatana]] `bla` [[dhammacakka]]\n\n";
        //$markdown .= "前面的\n";
        //$markdown .= "{{note|\n";
        //$markdown .= "多**行注**释\n\n";
        //$markdown .= "多行注释\n";
        //$markdown .= "}}\n\n";
        $markdown .= "- title \n";
        $markdown .= "  \n";
        $markdown .= "  content-1\n";
        $markdown .= "- title-2 \n";
        $markdown .= "  \n";
        $markdown .= "  content-2\n";
        $markdown .= "  \n";
        $markdown .= "\n";
        $markdown .= "\n";
        $markdown .= "aaa bbb\n";
        /*
        $markdown .= "```\n";
        $markdown .= "content **content**\n";
        $markdown .= "content **content**\n";
        $markdown .= "```\n\n";
        */
        /*
        $markdown .= "{{168-916-10-37}}";
        $markdown .= "{{exercise|1|((168-916-10-37))}}";

        $markdown2 = "# heading [[isipatana]] \n\n";
        $markdown2 .= "{{exercise\n|id=1\n|content={{168-916-10-37}}}}";
        $markdown2 .= "{{exercise\n|id=2\n|content=# ddd}}";

        $markdown2 .= "{{note|trigger=kacayana|text={{99-556-8-12}}}}";
        $markdown2 = "aaa=bbb\n";
        $markdown2 .= "ccc=ddd\n";
*/
        //echo MdRender::render($markdown,'00ae2c48-c204-4082-ae79-79ba2740d506');
        //$wiki = MdRender::markdown2wiki($markdown2);
        //$xml = MdRender::wiki2xml($wiki);
        //$html = MdRender::xmlQueryId($xml, "1");
        //$sent = MdRender::take_sentence($html);
        //print_r($sent);

        echo MdRender::render2($markdown,'00ae2c48-c204-4082-ae79-79ba2740d506',null,'read','nissaya');
        return 0;
    }
}
