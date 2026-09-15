<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Models\AccessToken;
use App\Services\AuthService;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccessTokenController extends Controller
{
    /**
     * 签出的 access token 有效期（秒）。7 天足够一次批量写入作业，
     * 又能把凭据泄漏的窗口限制在可接受范围内。
     */
    private const TOKEN_TTL = 60 * 60 * 24 * 7;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }
        $payload = $request->input('payload');
        $result = [];
        foreach ($payload as $key => $value) {
            // 鉴权
            switch ($value['res_type']) {
                case 'channel':
                    if (! isset($value['power']) || ! isset($value['res_id'])) {
                        continue 2;
                    }
                    if ($value['power'] === 'edit') {
                        if (! ChannelApi::userCanEdit($user['user_uid'], $value['res_id'])) {
                            continue 2;
                        }
                    } else {
                        if (! ChannelApi::userCanRead($user['user_uid'], $value['res_id'])) {
                            continue 2;
                        }
                    }
                    break;
                default:
                    continue 2;
                    break;
            }
            // 获取token
            $token = AccessToken::firstOrNew(
                [
                    'res_type' => $value['res_type'],
                    'res_id' => $value['res_id'],
                ],
                [
                    'token' => (string) Str::uuid(),
                ]
            );
            if (! $token->exists) {
                $token->save();
            }

            // 有效期：payload 里不注入 exp 的话，签出的 token 永久有效，泄漏后无法失效
            $value['nbf'] = time();
            $value['exp'] = time() + self::TOKEN_TTL;

            try {
                $jwt = JWT::encode($value, $token->token.$token->token, 'HS512');
            } catch (\Exception $e) {
                Log::error('jwt', ['error' => $e]);

                continue;
            }
            $result[] = [
                'payload' => $value,
                'token' => $jwt,
            ];
        }

        return $this->ok(['rows' => $result, 'count' => count($result)]);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(AccessToken $accessToken)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, AccessToken $accessToken)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(AccessToken $accessToken)
    {
        //
    }
}
