<?php

use App\Models\Channel;
use App\Models\Sentence;
use App\Models\UserInfo;
use App\Services\AuthService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * 造一个用户 token。
 *
 * AuthService::current() 只解 JWT、不查库，所以测试里不必真的建用户；
 * payload 结构必须与 AuthService::getUserToken() 保持一致。
 */
function userToken(string $userUid, int $userId = 1): string
{
    return JWT::encode([
        'nbf' => time(),
        'exp' => time() + 3600,
        'uid' => $userUid,
        'id' => $userId,
    ], config('mint.app.jwt_secrets_key'), 'HS512');
}

/**
 * 解开一个 token 的 payload。
 */
function decodeToken(string $token): object
{
    return JWT::decode($token, new Key(config('mint.app.jwt_secrets_key'), 'HS512'));
}

/**
 * 把 token 交给 AuthService::current() 判定，返回 user_uid，无效则返回 false。
 *
 * 所有端点的鉴权都走这里，故用它来断言「token 是否还有效」。
 *
 * @return string|false
 */
function currentUid(string $token)
{
    $request = Request::create('/', 'GET');
    $request->headers->set('Authorization', 'Bearer '.$token);

    $user = AuthService::current($request);

    return $user ? $user['user_uid'] : false;
}

/**
 * 带用户 token 的请求头。
 */
function authHeader(string $userUid): array
{
    return ['Authorization' => 'Bearer '.userToken($userUid)];
}

/**
 * 建一个用户及其个人 studio，返回 user uid。
 *
 * StudioApi::getIdByName() 查的是 user_infos.username，所以 studio 名即用户名。
 */
function makeStudio(string $username): string
{
    $userId = (string) Str::uuid();
    (new UserInfo)->forceFill([
        'userid' => $userId,
        'username' => $username,
        'nickname' => $username,
        'password' => 'x',
        'email' => $username.'@example.test',
    ])->save();

    return $userId;
}

/**
 * 建一个属于指定用户的 channel，返回 channel uid。
 *
 * channels.id 不是自增列，必须显式给值。
 */
function makeChannel(string $ownerUid, string $name = 'test channel'): string
{
    $uid = (string) Str::uuid();
    (new Channel)->forceFill([
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => $uid,
        'type' => 'translation',
        'owner_uid' => $ownerUid,
        'editor_id' => 0,
        'name' => $name,
        'lang' => 'zh-Hans',
        'status' => 30,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
    ])->save();

    return $uid;
}

/**
 * 建一个句子，返回模型
 */
function makeSentence(string $channelUid, int $book, int $para, int $wordStart, string $content): Sentence
{
    $sentence = new Sentence;
    $sentence->forceFill([
        // sentences.id 不是自增列，必须显式给值
        'id' => random_int(1, PHP_INT_MAX),
        'uid' => (string) Str::uuid(),
        'book_id' => $book,
        'paragraph' => $para,
        'word_start' => $wordStart,
        'word_end' => $wordStart,
        'channel_uid' => $channelUid,
        'editor_uid' => (string) Str::uuid(),
        'content' => $content,
        'content_type' => 'markdown',
        'strlen' => mb_strlen($content),
        'status' => 30,
        'create_time' => time() * 1000,
        'modify_time' => time() * 1000,
        'language' => 'zh-Hans',
    ])->save();

    return $sentence;
}
