<?php

namespace App\Services\V3;

/**
 * 术语导入时某一行不能写入的原因：记进响应的 errors，不中断其它行（见 {@see TermExchangeService}）。
 */
final class TermImportRowError extends \RuntimeException {}
