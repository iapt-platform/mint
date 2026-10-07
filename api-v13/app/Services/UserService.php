<?php

namespace App\Services;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\AiModel;
use App\Models\UserInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

/**
 * 操作者摘要（昵称、头像、角色）的唯一实现。
 *
 * 取代 `App\Http\Api\UserApi` 与 `App\Http\Api\AiAssistantApi`——那两个类已经改成
 * 零逻辑的转发壳，v2 的六十多个调用点一个都不用动。等调用点全部换成注入本类，
 * `app/Http/Api/` 才可以删。**不要再往那两个壳里写逻辑。**
 *
 * 「操作者」有两种：人类存 `user_infos`，AI 助手存 `ai_models`。两者共用一个 uuid
 * 空间，且没有类型判别列，所以解析顺序恒定为「先人、后 AI、都没有就给占位」。
 *
 * 相对原先两个静态类的三点改动：
 *
 * 1. **单点取值是批量取值的特例。** 原先单条走一条路、列表走另一条路，两处各自
 *    拼形状。现在只有 {@see self::load()} 一个出口。
 * 2. **请求内身份映射。** 同一个 uuid 在一次请求里往往要解析很多次（列表里同一个
 *    编辑者、反复出现的当前用户）。本类注册为 singleton，解析过的记在 `$resolved`
 *    里，含「确认查不到」的否定结果，所以重复查询和重复的头像签名都只发生一次。
 * 3. **只取用得到的列。** 原先是 `->first()`（`SELECT *`），每行把 `password`、
 *    `reset_password_token`、AI 的 `key` 一起捞进内存。
 */
class UserService
{
    /**
     * 查不到人时的占位摘要。
     *
     * 键集合比真实摘要少（没有 `sn` / `roles`），这是 v2 既有口径，前端在依赖，
     * 不要「顺手补齐」。
     */
    private const UNKNOWN = [
        'id' => 0,
        'nickName' => 'unknown',
        'userName' => 'unknown',
        'realName' => 'unknown',
        'avatar' => '',
    ];

    /** 拼人类摘要用得到的列；`userid` 是匹配列，不能省。 */
    private const USER_COLUMNS = ['userid', 'id', 'nickname', 'username', 'role', 'avatar'];

    /** 拼 AI 摘要用得到的列；`uid` 是匹配列，不能省。 */
    private const ASSISTANT_COLUMNS = ['uid', 'name', 'real_name', 'avatar', 'model', 'url'];

    /**
     * 请求内的身份映射：uuid => 摘要，或 null 表示「已确认查不到」。
     *
     * 记否定结果是为了让查不到的 uuid 也只查一次。进程常驻（octane / 长跑命令）
     * 时它是陈旧数据的来源，改过用户资料又要重读的场合调 {@see self::flush()}。
     *
     * @var array<string, array<string, mixed>|null>
     */
    private array $resolved = [];

    /**
     * 按 uuid 取一个操作者的摘要；查不到给占位。
     *
     * @param  string|null  $uuid  允许为 null：调用方常直接把可空的 editor_id 传进来
     * @return array<string, mixed>
     */
    public function byUuid(?string $uuid): array
    {
        return $this->byUuids([$uuid])[$uuid] ?? self::UNKNOWN;
    }

    /**
     * 批量取摘要，返回 **uuid => 摘要** 的映射。
     *
     * 最多两次查询，与 uuid 个数无关；`ai_models` 只在人类表还没解析完时才查。
     * 已经在身份映射里的 uuid 不参与查询。
     *
     * 解析不到的 uuid **不出现在结果里**——由调用方决定省略还是补占位，比塞一个
     * unknown 进去更诚实。列表渲染请用这个方法，不要循环调 {@see self::byUuid()}。
     *
     * @param  array<int, string|null>  $uuids
     * @return array<string, array<string, mixed>>
     */
    public function byUuids(array $uuids): array
    {
        $wanted = array_values(array_unique(array_filter(
            $uuids,
            static fn ($uuid): bool => is_string($uuid) && $uuid !== '',
        )));
        if (! $wanted) {
            return [];
        }

        $unseen = array_values(array_diff($wanted, array_keys($this->resolved)));
        if ($unseen) {
            $loaded = $this->load($unseen);
            foreach ($unseen as $uuid) {
                $this->resolved[$uuid] = $loaded[$uuid] ?? null;
            }
        }

        $output = [];
        foreach ($wanted as $uuid) {
            if ($this->resolved[$uuid] !== null) {
                $output[$uuid] = $this->resolved[$uuid];
            }
        }

        return $output;
    }

    /**
     * 从「人类行」与「AI 行」两个候选里定出操作者摘要。
     *
     * 这是解析顺序（先人、后 AI、都没有给占位）的唯一实现。传进来的应当是**已经
     * 预加载好的模型**，本方法不查库——它只负责拼形状。列表渲染时每行调用它是安全的。
     *
     * Resource 里直接用 {@see ResolvesActor::actor()}，
     * 不必自己解析本服务。
     *
     * @param  mixed  $human  一行 `user_infos`，没有传 null
     * @param  mixed  $assistant  一行 `ai_models`，没有传 null
     * @return array<string, mixed>
     */
    public function actor($human, $assistant = null): array
    {
        return $human ? $this->profile($human) : $this->assistantProfile($assistant);
    }

    /**
     * 操作者两条关系的预加载声明，直接喂给 `->with()`。
     *
     * 把列白名单收在这里，而不是让每个控制器各抄一份长字符串——那串东西一旦漏掉
     * 匹配列（`userid` / `uid`），关系会静默对不上、整列变 null，不报错。
     *
     *     Reaction::query()->with(UserService::eagerLoadActor())
     *     Sentence::query()->with(UserService::eagerLoadActor('editor', 'editorAssistant'))
     *
     * @param  string  $human  人类那条关系的名字
     * @param  string  $assistant  AI 那条关系的名字
     * @return array<int, string>
     */
    public static function eagerLoadActor(string $human = 'user', string $assistant = 'aiModel'): array
    {
        return [
            $human.':'.implode(',', self::USER_COLUMNS),
            $assistant.':'.implode(',', self::ASSISTANT_COLUMNS),
        ];
    }

    /**
     * 批量取摘要，返回**位置数组**：保持传入顺序，解析不到的整条丢弃。
     *
     * 这是 `UserApi::getListByUuid()` 的既有口径，只为兼容旧调用点而存在。
     * 新代码用 {@see self::byUuids()}——位置数组一旦丢项就和输入对不上号了。
     *
     * @param  array<int, string|null>  $uuids
     * @return array<int, array<string, mixed>>
     */
    public function listByUuids(array $uuids): array
    {
        $map = $this->byUuids($uuids);

        $output = [];
        foreach ($uuids as $uuid) {
            if (is_string($uuid) && isset($map[$uuid])) {
                $output[] = $map[$uuid];
            }
        }

        return $output;
    }

    /**
     * 按 uuid 取 AI 助手的摘要，跳过人类表；查不到给占位。
     *
     * @return array<string, mixed>
     */
    public function assistantByUuid(?string $uuid): array
    {
        $assistant = $this->query(AiModel::class, 'uid', [$uuid], self::ASSISTANT_COLUMNS)->first();

        return $assistant ? $this->assistantProfile($assistant) : self::UNKNOWN;
    }

    /**
     * 按自增主键取人类用户的摘要。
     *
     * @return array<string, mixed>
     */
    public function byId(int|string|null $id): array
    {
        $user = UserInfo::query()->where('id', $id)->first(self::USER_COLUMNS);

        return $user ? $this->profile($user) : self::UNKNOWN;
    }

    /**
     * 按用户名取人类用户的摘要。
     *
     * @return array<string, mixed>
     */
    public function byName(?string $name): array
    {
        $user = UserInfo::query()->where('username', $name)->first(self::USER_COLUMNS);

        return $user ? $this->profile($user) : self::UNKNOWN;
    }

    /** 用户名 → uuid（`user_infos.userid`）。 */
    public function uuidByName(?string $name): ?string
    {
        return UserInfo::query()->where('username', $name)->value('userid');
    }

    /** uuid → 自增主键（`user_infos.id`）。 */
    public function intIdByUuid(?string $uuid): ?int
    {
        return UserInfo::query()->where('userid', $uuid)->value('id');
    }

    /** 用户名 → 自增主键（`user_infos.id`）。 */
    public function intIdByName(?string $name): ?int
    {
        return UserInfo::query()->where('username', $name)->value('id');
    }

    /**
     * 把一行 `user_infos` 拼成公开摘要。
     *
     * 参数刻意不加类型：传进来的既有 UserInfo 模型，也有包着模型的 Resource。
     *
     * @return array<string, mixed>
     */
    public function profile($user): array
    {
        if (! $user) {
            return self::UNKNOWN;
        }

        $profile = [
            'id' => $user->userid,
            'nickName' => $user->nickname,
            'userName' => $user->username,
            'realName' => $user->username,
            'sn' => $user->id,
        ];

        // roles 与 avatar 是可选键：没有就整个键不出现，不是给个空值。
        // 这是 v2 的既有口径，前端用 `in` 判断，别改成恒定存在。
        if (! empty($user->role)) {
            $profile['roles'] = json_decode($user->role);
        }
        if ($avatar = $this->avatarUrl($user->avatar)) {
            $profile['avatar'] = $avatar;
        }

        return $profile;
    }

    /**
     * 把一行 `ai_models` 拼成公开摘要。
     *
     * 参数刻意不加类型：`AiModelResource` 传进来的是 Resource 而不是模型。
     *
     * @return array<string, mixed>
     */
    public function assistantProfile($assistant): array
    {
        if (! $assistant) {
            return self::UNKNOWN;
        }

        return [
            'id' => $assistant->uid,
            'nickName' => $assistant->name,
            'userName' => $assistant->real_name,
            'realName' => $assistant->real_name,
            'roles' => ['ai'],
            'sn' => 0,
            'avatar' => $this->avatarUrl($assistant->avatar)
                ?? $this->assistantLogo($assistant->model, $assistant->url),
        ];
    }

    /**
     * 清空请求内的身份映射。
     *
     * 只有「同一进程里改过用户资料又要重新读出来」的场合才需要，例如长跑的
     * artisan 命令。普通 HTTP 请求不必调用。
     */
    public function flush(): void
    {
        $this->resolved = [];
    }

    /**
     * 一批 uuid 的实际装载：先查人类表，剩下的才查 AI 表。
     *
     * 两次简单查询而不是一次 union/join：两张表各自的 uuid 列上都有唯一索引，
     * 分开查各走各的索引，第二次还常常因为没有剩余 uuid 而根本不发生。
     *
     * @param  array<int, string>  $uuids
     * @return array<string, array<string, mixed>>
     */
    private function load(array $uuids): array
    {
        $found = [];
        foreach ($this->query(UserInfo::class, 'userid', $uuids, self::USER_COLUMNS)->get() as $user) {
            $found[$user->userid] = $this->profile($user);
        }

        $rest = array_values(array_diff($uuids, array_keys($found)));
        if (! $rest) {
            return $found;
        }

        foreach ($this->query(AiModel::class, 'uid', $rest, self::ASSISTANT_COLUMNS)->get() as $assistant) {
            $found[$assistant->uid] = $this->assistantProfile($assistant);
        }

        return $found;
    }

    /**
     * 按 uuid 列表查某张表，只取指定列。
     *
     * @param  class-string<Model>  $model
     * @param  array<int, string|null>  $uuids
     * @param  array<int, string>  $columns
     * @return Builder<covariant \Illuminate\Database\Eloquent\Model>
     */
    private function query(string $model, string $column, array $uuids, array $columns)
    {
        return $model::query()->whereIn($column, $uuids)->select($columns);
    }

    /**
     * 头像缩略图地址；没有头像返回 null。
     *
     * 生产环境给 6 天有效期的签名 URL，本地与测试环境给普通 URL——签名要连对象
     * 存储，测试里会直接炸。
     *
     * 原先人类路径认 `local` + `testing`、AI 路径只认 `local`，是两份副本漂移的
     * 结果，这里统一成前者。差异只在 testing 环境显现，生产行为不变。
     */
    private function avatarUrl(?string $avatar): ?string
    {
        if (! $avatar) {
            return null;
        }
        $img = str_replace('.jpg', '_s.jpg', $avatar);

        return App::environment(['local', 'testing'])
            ? Storage::url($img)
            : Storage::temporaryUrl($img, now()->addDays(6));
    }

    /**
     * AI 助手没有自定义头像时的内置 logo：按 model / url 里的关键字挑，都不匹配
     * 就用通用图标。
     */
    private function assistantLogo(?string $model, ?string $url): string
    {
        $logo = 'ai-assistant.png';
        // model 与 url 分别匹配，不要拼成一个串：关键字可能跨越拼接处产生假匹配。
        // 两者都可为 null（新建模型时未必填写），转字符串避免 str_contains 报类型错。
        foreach (config('mint.ai.logo', []) as $keyword => $file) {
            if (str_contains((string) $model, (string) $keyword)
                || str_contains((string) $url, (string) $keyword)) {
                $logo = $file;
                break;
            }
        }

        return config('app.url').'/assets/images/avatar/'.$logo;
    }
}
