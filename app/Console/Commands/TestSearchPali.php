<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tools\PaliSearch;

class TestSearchPali extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:search.pali';

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
        //$result = PaliSearch::search(['citta'],[93,94],'case',0,10);
        //var_dump($result);
        //$result = PaliSearch::book_list(['citta'],[93,94],'case',0,10);
        //var_dump($result);
        //$this->info(PaliSearch::upload_dict('ddd'));
        $full = '19 . ‘ ‘ ‘ Yathā vā paneke bhonto samaṇabrāhmaṇā saddhādeyyāni bhojanāni bhuñjitvā te evarūpaṃ dūteyyapahiṇagamanānuyogaṃ anuyuttā viharanti , seyyathidaṃ – raññaṃ , rājamahāmattānaṃ , khattiyānaṃ , brāhmaṇānaṃ , gahapatikānaṃ , kumārānaṃ ‘ ‘ idha gaccha , amutrāgaccha , idaṃ hara , amutra idaṃ āharā ’ ’ ti iti vā iti evarūpā dūteyyapahiṇagamanānuyogā paṭivirato samaṇo gotamo ’ ti – iti vā hi , bhikkhave , puthujjano tathāgatassa vaṇṇaṃ vadamāno vadeyya . ';
        $test = 'bhonto samaṇabrāhmaṇā';
        $update = PaliSearch::update(93,50,
                                        '','','',
                                        $test,
                                        99);
        $this->info($update);
        return 0;
    }
}
