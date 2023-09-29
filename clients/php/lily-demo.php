<?php

require dirname(__FILE__) . '/vendor/autoload.php';

function tex2pdf($host, $request)
{
    $client = new Palm\Lily\V1\TexClient($host, [
        'credentials' => Grpc\ChannelCredentials::createInsecure(),
    ]);

    list($response, $status) = $client->ToPdf($request)->wait();
    if ($status->code !== Grpc\STATUS_OK) {
        echo "ERROR: " . $status->code . ", " . $status->details . PHP_EOL;
        exit(1);
    }
    echo $response->getContentType() . '(' . strlen($response->getPayload()) . ' bytes)' . PHP_EOL;
}

$request = new Palm\Lily\V1\TexToRequest();

$request->getFiles()['main.tex'] = <<<'EOF'
% 导言区
\documentclass[a4paper, 12pt, fontset=ubuntu]{article} % book, report, letter
\usepackage{ctex} % Use chinese package

\title{\heiti 一级标题}
\author{\kaishu 半闲}
\date{\today}

% 正文区

\begin{document}
    \maketitle % 头部信息在正文显示
    \tableofcontents % 显示索引列

    \include{section-1.tex}
    \include{section-2.tex}

\end{document}

EOF;

$request->getFiles()['section-1.tex'] = <<<'EOF'
\section{章节1 标题}
章节1 正文
\subsection{子章节1.1 标题}
子章节1-1 正文
\subsection{子章节1.2 标题}
子章节1-2 正文
EOF;

$request->getFiles()['section-2.tex'] = <<<'EOF'
\section{章节2 标题}
章节2 正文
\subsection{子章节2.1 标题}
子章节2-1 正文
\subsection{子章节2.2 标题}
子章节2-2 正文
EOF;

tex2pdf('localhost:9999', $request);
