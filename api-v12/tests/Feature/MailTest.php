<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteMail;
use Illuminate\Support\Str;

/**
 *    # 只运行 MailTest
   php artisan test tests/Feature/MailTest.php

   # 运行特定测试方法
   php artisan test --filter test_invite_mail_can_be_sent
 */
class MailTest extends TestCase
{
    /**
     * 测试邮件发送功能（使用 fake 模拟）
     */
    public function test_invite_mail_can_be_sent(): void
    {
        // 使用 Mail fake 来模拟邮件发送
        Mail::fake();

        // 发送邮件
        Mail::to('visuddhinanda@gmail.com')->send(new InviteMail(Str::uuid()));

        // 断言邮件已发送
        Mail::assertSent(InviteMail::class, function ($mail) {
            return $mail->hasTo('visuddhinanda@gmail.com');
        });

        // 断言只发送了一封邮件
        Mail::assertSent(InviteMail::class, 1);
    }

    /**
     * 测试真实邮件发送
     * 注意：需要正确配置 .env 中的邮件参数
     */
    public function test_invite_mail_actual_sending(): void
    {
        // 真实发送邮件
        Mail::to('visuddhinanda@gmail.com')->send(new InviteMail(Str::uuid()));

        // 断言测试通过（需要手动检查邮箱）
        $this->assertTrue(true);
    }
}
