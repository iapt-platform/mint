<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * 测试环境变量：与 phpunit.xml 的 <env> 保持一致。
     *
     * 必须在这里（Laravel bootstrap 之前）把这些值同时写入 putenv / $_ENV / $_SERVER。
     * 原因：Laravel 的 Env::get() 通过 Dotenv 的多 adapter 仓库读取，优先级是
     * $_SERVER > $_ENV > getenv；而 PHPUnit 的 <env> 只写 putenv 与 $_ENV、不写 $_SERVER，
     * 导致 .env 里的 DB_DATABASE（wikipali）在 $_SERVER 里胜出，测试会连到开发库，
     * RefreshDatabase 的 migrate:fresh 会直接清空开发库数据。
     *
     * @var array<string, string>
     */
    private const TEST_ENV = [
        'APP_ENV' => 'testing',
        'APP_MAINTENANCE_DRIVER' => 'file',
        'BCRYPT_ROUNDS' => '4',
        'BROADCAST_CONNECTION' => 'null',
        'CACHE_STORE' => 'array',
        'DB_CONNECTION' => 'pgsql',
        'DB_DATABASE' => 'mint_test',
        'DB_URL' => '',
        'MAIL_MAILER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'SESSION_DRIVER' => 'array',
        'PULSE_ENABLED' => 'false',
        'TELESCOPE_ENABLED' => 'false',
        'NIGHTWATCH_ENABLED' => 'false',
    ];

    public function createApplication()
    {
        foreach (self::TEST_ENV as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        return parent::createApplication();
    }
}
