<?php

namespace App\Http\Controllers;

use App\Http\Api\UserApi;
use App\Http\Resources\CourseMemberResource;
use App\Models\Course;
use App\Models\CourseMember;
use App\Models\UserInfo;
use App\Services\AuthService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CourseMemberController extends Controller
{
    /**
     * 列出课程成员
     *
     * 按 view 指定的口径返回课程成员列表，支持角色/状态过滤、按昵称搜索、分页与排序。
     * 必须登录，且当前用户必须已经是该 course 的成员（在 course_member 中有 role），否则返回 403。
     * 返回 rows（成员列表）、role（当前用户在该 course 中的当前角色）、count（过滤后的总数）。
     *
     * @queryParam view string required 查询口径。course=课程内全部当前成员；timeline=某用户的成员变更时间线。Enum: course,timeline
     * @queryParam id string required course uid。view=timeline 时也用于权限校验
     * @queryParam course string 权限校验与 timeline 口径使用的 course uid，缺省时回退到 id
     * @queryParam userId string view=timeline 时必填，要查看时间线的用户 uid
     * @queryParam timeline string view=timeline 时的范围：current=只看 course 参数指定的课程，其余值=该用户全部课程。Default: current
     * @queryParam role string 按角色过滤，all 表示不过滤。Example: student
     * @queryParam status string 按状态过滤，逗号分隔可多选。Example: joined,applied
     * @queryParam search string 按成员昵称模糊搜索
     * @queryParam order string 排序字段。Default: created_at
     * @queryParam dir string 排序方向。Enum: asc,desc Default: asc
     * @queryParam offset integer 跳过的记录数。Default: 0
     * @queryParam limit integer 返回条数上限。Default: 1000
     */
    public function index(Request $request)
    {
        //
        $user = AuthService::current($request);
        // FIXME: [403] 和 403 被当成了 __() 的替换参数与 locale，没有传给 error()，实际返回的仍是默认状态码。
        // 应改为 $this->error(__('auth.failed'), [403], 403)；本文件多处同样写法。
        if (! $user) {
            return $this->error(__('auth.failed', [403], 403));
        }
        // 判断当前用户是否有指定的 course 的权限
        $role = CourseMember::where('course_id', $request->input('id', $request->input('course')))
            ->where('user_id', $user['user_uid'])
            ->value('role');
        if (empty($role)) {
            return $this->error(__('auth.failed', [403], 403));
        }

        $result = false;
        $indexCol = [
            'id',
            'user_id',
            'course_id',
            'channel_id',
            'role',
            'editor_uid',
            'updated_at',
            'created_at',
        ];
        switch ($request->input('view')) {
            case 'course':
                // 获取 course 内所有 成员
                $table = CourseMember::where('course_id', $request->input('id'))
                    ->where('is_current', true);
                break;
            case 'timeline':
                /**
                 * 编辑时间线
                 */
                $table = CourseMember::where('user_id', $request->input('userId'));
                if ($request->input('timeline', 'current') === 'current') {
                    $table = $table->where('course_id', $request->input('course'));
                }

                break;
            default:
                return $this->error('无法识别的参数view', 400, 400);
                break;
        }
        if (! empty($request->input('role')) && $request->input('role') !== 'all') {
            $table = $table->where('role', $request->input('role'));
        }
        if (! empty($request->input('status'))) {
            $table = $table->whereIn('status', explode(',', $request->input('status')));
        }
        if (! empty($request->input('search'))) {
            $usersId = UserInfo::where('nickname', 'like', '%'.$request->input('search').'%')
                ->select('userid')
                ->get();
            // FIXME: whereIn 传入的是 UserInfo 模型集合而非 id 数组，取到的是主键而不是 userid 列，search 过滤很可能不生效。
            // 建议改为 ->pluck('userid')->all() 后再传入。
            $table = $table->whereIn('user_id', $usersId);
        }

        $count = $table->count();

        $table = $table->orderBy(
            $request->input('order', 'created_at'),
            $request->input('dir', 'asc')
        );

        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000));

        $result = $table->get();

        // 获取当前用户角色
        // FIXME: 这里固定读 input('id')，view=timeline 且只传了 course 时取不到 role，返回的 role 会是空。
        // 建议与上面的权限校验一致，改用 input('id', input('course'))。
        $role = CourseMember::where('course_id', $request->input('id'))
            ->where('user_id', $user['user_uid'])
            ->where('is_current', true)
            ->value('role');

        return $this->ok(['rows' => CourseMemberResource::collection($result), 'role' => $role, 'count' => $count]);
    }

    /**
     * 新增课程成员记录（加入/申请/邀请）
     *
     * 必须登录。status=invited 表示由当前用户邀请他人，成员归属 user_id 指定的用户；
     * 其余情况（自己加入或申请）一律忽略 user_id，成员归属当前登录用户。
     * 非 invited 时若该 user 已在该 course 中存在任何记录，返回 member exists（HTTP 200）。
     * 写入前会把同一 course + user 的旧记录 is_current 置为 false，新记录成为当前记录。
     * 状态必须与课程加入方式匹配：course.join=open 只接受 joined/invited，manual 只接受 applied/invited，
     * 不匹配返回 invalid course（HTTP 200）。course 不存在返回 invalid course。
     *
     * @bodyParam user_id string required 成员用户 uid，仅在 status=invited 时生效
     * @bodyParam course_id string required course uid
     * @bodyParam role string required 成员角色。Example: student
     * @bodyParam status string required 成员状态。Enum: joined,applied,invited
     */
    public function store(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed', [403], 403));
        }
        $validated = $request->validate([
            'user_id' => 'required',
            'course_id' => 'required',
            'role' => 'required',
            'status' => 'required',
        ]);
        // 查找重复的
        if ($validated['status'] !== 'invited') {
            if (CourseMember::where('course_id', $validated['course_id'])
                ->where('user_id', $validated['user_id'])
                ->exists()
            ) {
                return $this->error('member exists', [200], 200);
            }
        }

        if ($validated['status'] === 'invited') {
            $userId = $validated['user_id'];
        } else {
            $userId = $user['user_uid'];
        }

        CourseMember::where('course_id', $validated['course_id'])
            ->where('user_id', $userId)
            ->update(['is_current' => false]);

        $newMember = new CourseMember;
        $newMember->course_id = $validated['course_id'];
        $newMember->role = $validated['role'];
        $newMember->editor_uid = $user['user_uid'];
        $newMember->status = $validated['status'];
        $newMember->user_id = $userId;

        /**
         * 查找course 信息，根据加入方式设置状态
         * open : accepted
         * manual: progressing
         */
        $course = Course::find($validated['course_id']);
        if (! $course) {
            return $this->error('invalid course');
        }
        switch ($course->join) {
            case 'open': // 开放学习课程
                if (
                    $validated['status'] !== 'joined' &&
                    $validated['status'] !== 'invited'
                ) {
                    return $this->error('invalid course', [200], 200);
                }
                break;
            case 'manual': // 人工审核课程
                if (
                    $validated['status'] !== 'applied' &&
                    $validated['status'] !== 'invited'
                ) {
                    return $this->error('invalid course', [200], 200);
                }
                break;
        }
        $newMember->save();

        return $this->ok(new CourseMemberResource($newMember));
    }

    /**
     * 查询某用户在指定课程中的当前成员记录
     *
     * 必须登录。默认查当前登录用户，传 user_uid 可查他人（无额外权限校验）。
     * 只返回 is_current=true 的那条记录；查不到返回 no result（HTTP 200）。
     *
     * @urlParam courseId string required course uid
     *
     * @queryParam user_uid string 要查询的用户 uid，缺省为当前登录用户
     */
    public function show(Request $request, string $courseId)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        $userId = $user['user_uid'];
        if (! empty($request->input('user_uid'))) {
            $userId = $request->input('user_uid');
        }
        $member = CourseMember::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->where('is_current', true)
            ->first();
        if ($member) {
            return $this->ok(new CourseMemberResource($member));
        } else {
            return $this->error('no result', 200, 200);
        }
    }

    /**
     * 更新课程成员（以新增记录的方式保留历史）
     *
     * 必须登录。不会就地修改原记录：先复制一条新记录，把原记录 is_current 置为 false，
     * 再把请求中出现的字段写到新记录上，原记录作为历史保留。
     * 修改 channel_id 只允许成员本人操作，否则返回 auth.failed。
     *
     * @urlParam course_member string required 要更新的 course_member 记录 id
     *
     * @bodyParam channel_id string 该成员在课程中使用的 channel uid，仅本人可改
     * @bodyParam status string 新的成员状态。Example: joined
     */
    public function update(Request $request, CourseMember $courseMember)
    {
        /**
         * 保留原有记录
         * 增加一条新纪录
         * 原有记录变为历史记录
         */
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }

        $newMember = new CourseMember;
        $newMember->user_id = $courseMember->user_id;
        $newMember->course_id = $courseMember->course_id;
        $newMember->role = $courseMember->role;
        $newMember->status = $courseMember->status;
        $newMember->channel_id = $courseMember->channel_id;
        $newMember->editor_uid = $user['user_uid'];

        $courseMember->is_current = false;
        $courseMember->save();

        if ($request->has('channel_id')) {
            if ($newMember->user_id !== $user['user_uid']) {
                return $this->error(__('auth.failed'));
            }
            $newMember->channel_id = $request->input('channel_id');
        }
        if ($request->has('status')) {
            $newMember->status = $request->input('status');
        }
        $newMember->save();

        return $this->ok(new CourseMemberResource($newMember));
    }

    /**
     * 设置当前用户在某课程中使用的 channel
     *
     * 必须登录。就地修改当前用户在该 course 的 is_current 记录（不产生历史记录）。
     * 未传 channel_id、或当前用户不是该课程的当前成员，都返回 auth.failed。
     *
     * @bodyParam course_id string required course uid
     * @bodyParam channel_id string required 作业所用的 channel uid
     */
    public function set_channel(Request $request)
    {
        //
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }

        if ($request->has('channel_id')) {
            $courseMember = CourseMember::where('course_id', $request->input('course_id'))
                ->where('user_id', $user['user_uid'])
                ->where('is_current', true)
                ->first();
            if ($courseMember) {
                $courseMember->channel_id = $request->input('channel_id');
                $courseMember->save();

                return $this->ok(new CourseMemberResource($courseMember));
            } else {
                return $this->error(__('auth.failed'));
            }
        } else {
            return $this->error(__('auth.failed'));
        }
    }

    /**
     * 删除课程成员记录
     *
     * 必须登录。课程所有者（course.studio_id 等于当前用户）可以直接删除；
     * 否则按当前用户在该课程中的角色判断权限，普通 student 无删除权限，返回 auth.failed。
     *
     * @urlParam course_member string required 要删除的 course_member 记录 id
     */
    public function destroy(Request $request, CourseMember $courseMember)
    {
        // 查看删除者有没有删除权限
        // 查询删除者的权限
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }

        $isOwner = Course::where('id', $courseMember->course_id)->where('studio_id', $user['user_uid'])->exists();
        if (! $isOwner) {
            $courseUser = CourseMember::where('course_id', $courseMember->course_id)
                ->where('user_id', $user['user_uid'])
                ->select('role')->first();
            // open 课程 可以删除自己

            // FIXME: 条件写反了。$courseUser 为 null 时才进入分支并立刻对 null 取属性，会 fatal error；
            // 而真正有记录的 student 反而绕过了权限检查，等于对所有非 owner 的已有成员放行。应改为 if ($courseUser) {。
            if (! $courseUser) {
                // 被删除的不是自己
                if ($courseUser->role === 'student') {
                    // 普通成员没有删除权限
                    return $this->error(__('auth.failed'));
                }
            }
        }

        $delete = $courseMember->delete();

        return $this->ok($delete);
    }

    /**
     * 获取当前用户在指定课程中的角色与 channel
     *
     * 必须登录。只读取 is_current=true 的记录，返回 role 与 channel_id 两个字段。
     * 当前用户不是该课程成员时返回 not member。
     *
     * @queryParam course_id string required course uid
     */
    public function curr(Request $request)
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'));
        }
        $courseUser = CourseMember::where('course_id', $request->input('course_id'))
            ->where('user_id', $user['user_uid'])
            ->where('is_current', true)
            ->select(['role', 'channel_id'])->first();
        if ($courseUser) {
            return $this->ok($courseUser);
        } else {
            return $this->error('not member');
        }
    }

    /**
     * 导出课程成员名单为 Excel
     *
     * 导出该课程全部 is_current 成员，表头为 nickname、username、role、status、created_at，
     * 昵称与用户名通过 UserApi 按 user_id 逐条查询。
     * 直接以 xlsx 附件（course_member.xlsx）写入输出流，不返回 JSON，且不做登录与权限校验。
     *
     * @queryParam course_id string required course uid
     */
    public function export(Request $request)
    {
        // FIXME: 本方法没有任何登录与权限校验，任何人都能导出任意课程的成员名单（含昵称与用户名），属于信息泄露。
        // 应先 AuthService::current() 并校验当前用户是该 course 的管理者或 studio 拥有者。

        $courseUser = CourseMember::where('course_id', $request->input('course_id'))
            ->where('is_current', true)
            ->get();

        $spreadsheet = new Spreadsheet;
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'nickname');
        $activeWorksheet->setCellValue('B1', 'username');
        $activeWorksheet->setCellValue('C1', 'role');
        $activeWorksheet->setCellValue('D1', 'status');
        $activeWorksheet->setCellValue('E1', 'created_at');

        $currLine = 2;
        foreach ($courseUser as $key => $row) {
            $user = UserApi::getByUuid($row->user_id);
            $activeWorksheet->setCellValue("A{$currLine}", $user['nickName']);
            $activeWorksheet->setCellValue("B{$currLine}", $user['userName']);
            $activeWorksheet->setCellValue("C{$currLine}", $row->role);
            $activeWorksheet->setCellValue("D{$currLine}", $row->status);
            $activeWorksheet->setCellValue("E{$currLine}", $row->created_at);
            $currLine++;
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="course_member.xlsx"');
        $writer->save('php://output');
    }
}
