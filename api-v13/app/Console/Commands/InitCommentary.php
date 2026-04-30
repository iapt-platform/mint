<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tag;
use App\Models\TagMap;
use App\Models\PaliText;
use App\Models\PaliSentence;
use App\Models\Commentary;
use App\Models\RelatedParagraph;

class InitCommentary extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan init:commentary
     * @var string
     */
    protected $signature = 'init:commentary {--book=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'init commentary sentences';

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
        //查询注释书标签
        $tags = Tag::whereIn('name', ['aṭṭhakathā', 'ṭīkā'])->select('id')->get();
        //查询段落编号
        $paliId = TagMap::whereIn('tag_id', $tags)
            ->where('table_name', 'pali_texts')
            ->cursor();
        foreach ($paliId as $key => $paraId) {
            $book = PaliText::where('uid', $paraId->anchor_id)
                ->where('level', 1)->first();
            if (!$book) {
                continue;
            }
            $paragraphs = PaliText::where('book', $book->book)
                ->whereBetween('paragraph', [$book->paragraph, $book->paragraph + $book->chapter_len - 1])
                ->get();
            foreach ($paragraphs as $key => $para) {
                $this->info($para->book . '-' . $para->paragraph);
                $sentences = PaliSentence::where('book', $para->book)
                    ->where('paragraph', $para->paragraph)
                    ->get();
                $del = Commentary::where('book1', $para->book)
                    ->where('paragraph1', $para->paragraph)
                    ->where('owner_id', config("mint.admin.root_uuid"))
                    ->delete();
                $csPara = RelatedParagraph::where('book', $para->book)
                    ->where('para', $para->paragraph)
                    ->first();
                if ($csPara) {
                    foreach ($sentences as $key => $sentence) {
                        $new = new Commentary();
                        $new->book1 = $sentence->book;
                        $new->paragraph1 = $sentence->paragraph;
                        $new->start1 = $sentence->word_begin;
                        $new->end1 = $sentence->word_end;
                        $new->editor_id = config("mint.admin.root_uuid");
                        $new->owner_id = config("mint.admin.root_uuid");
                        $new->p_number = $csPara->book_name . '-' . $csPara->para;
                        $new->save();
                    }
                } else {
                    $this->error('no relation paragraph');
                }
            }
        }
        $this->info('all done');
        return 0;
    }
}
