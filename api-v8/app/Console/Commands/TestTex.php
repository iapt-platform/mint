<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Tools\Export;

class TestTex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tex';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $tex = array();
        $content = <<<'EOF'
% 导言区
\documentclass[a4paper, 12pt, fontset=ubuntu]{article} % book, report, letter
\usepackage{ctex} % Use chinese package

\title{\heiti 一级标题}
\author{\kaishu 半闲}
\date{\today}

% 正文区

\begin{document}
    \maketitle % 头部信息在正文显示
    \newpage
    \tableofcontents % 显示索引列

    \include{section-1.tex}
    \include{section-2.tex}

\end{document}

EOF;
$tex[] = ['name'=>'main.tex','content'=>$content];
$content = <<<'EOF'
\section{三十位经}

住在王舍城的竹林园。
那时，三十位波婆城的比丘全是住林野者、全是常乞食者、全是穿粪扫衣者、全是但三衣者、全是尚有结缚者，他们去见世尊。
\subsubsection{子章节1.1 标题}
子章节1-1 正文
\subsection{子章节1.2 标题}
子章节1-2 正文
EOF;
$tex[] = ['name'=>'section-1.tex','content'=>$content];

$content = <<<'EOF'
\section{章节2 标题}
章节2 正文
\subsection{子章节2.1 标题}
子章节2-1 正文
\subsection{子章节2.2 标题}
子章节2-2 正文
EOF;

$tex[] = ['name'=>'section-2.tex','content'=>$content];

        $data = Export::ToPdf($tex);
        if($data['ok']){
            $filename = "export/test.pdf";
            $this->info($data['content-type']);
            Storage::disk('local')->put($filename, $data['data']);
        }else{
            $this->error($data['code'].'-'.$data['message']);
        }
        return 0;
    }
}
