<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    |
    | 操作结果的提示文案：某个动作失败或成功时告诉用户发生了什么。
    | 站点固定文案与服务状态（停机维护、404 等）仍在 site.php。
    |
    */

    'mail_send_failed' => '邮件发送失败，请稍后再试。',
    'email_registered' => '该邮箱已注册。',
    'sign_up_code_invalid' => '验证码不正确或已过期。',
    'invite_invalid' => '邀请无效或已被使用。',

    'discussion_closed' => '话题已关闭，不能再回复。',
    'discussion_reply_to_reply' => '不能回复一条回复，请回复它所在的话题。',
    'discussion_target_not_found' => '讨论挂靠的对象不存在。',
    'discussion_topic_only' => '回复不能设置这个字段。',
    'discussion_forbidden' => '你没有权限执行这个操作。',
    'discussion_severity_not_allowed' => '严重程度与讨论类型不匹配。',
    'discussion_annotation_target' => '批注只能挂在句子上。',
    'discussion_annotation_no_status' => '批注没有关闭 / 打开状态。',
    'discussion_type_group' => '评论线程与批注之间不能互相转换。',
    'discussion_commentary_format' => '义注的内容必须是句子模板，如 {{book-para-start-end}}。',
    'discussion_anchor_thread' => '这种类型的讨论不能锚定到文本位置。',
    'discussion_glossary_target' => '外挂术语只能挂在句子上。',
    'discussion_assessment_target' => '评估只能挂在句子上。',
    'discussion_assessment_method' => '评估方法须为 1–16 个字母、数字、点、下划线或连字符，以字母或数字开头。',
    'discussion_score_not_allowed' => '只有评估可以带分数。',
    'discussion_assessment_type' => '评估不能与其它类型互相转换。',
    'discussion_assessment_no_status' => '评估没有关闭 / 打开状态。',
    'agent_grant_by_model' => '只有人类能给 AI 模型授权写入。',
    'agent_grant_model_not_found' => 'AI 模型不存在。',
    'agent_grant_not_owner' => '这个 AI 模型不属于你。',
    'agent_grant_no_power' => '你不能编辑这个 channel，也就不能把它授权给 AI 模型。',
    'agent_grant_human' => '写入授权只能由 AI 模型使用。',
    'agent_grant_required' => 'AI 模型写入需要人类的授权（X-Agent-Grant）。',
    'agent_grant_invalid' => '写入授权无效、已过期或已撤销。',
    'agent_grant_scope' => '写入授权不覆盖这条内容。',

    'agent_device_required' => '未登录时必须带 device_id。',
    'agent_client_ip_required' => '未登录时必须带客户端 IP。',
    'agent_thread_forbidden' => '这个会话属于别的账号，请新开一个会话。',
    'agent_quota_insufficient_user' => '今天的积分不够这次提问了。读经可以赚取积分。',
    'agent_quota_insufficient_guest' => '今天的免费提问次数用完了。登录后每天有更多次数。',
    'agent_quota_ip_cap' => '今天同一网络下的提问太多了，请登录后继续。',
    'reading_time_day_out_of_range' => '读经时长只能上报今天或 2 天内的。',
    'stats_range_too_long' => '时间范围最多 :days 天。',
    'agent_feedback_tags' => '标签和评价不配套。',

    'group_forbidden' => '你在这个团队里的角色不能执行这个操作。',
    'group_user_not_found' => '没有这个用户。',
    'group_member_exists' => '这个用户已经是团队成员了。',
    'group_owner_immutable' => '团队拥有者不能被修改或移除。',
    'group_not_member' => '你不是这个团队的成员。',
    'group_has_resources' => '团队名下还有资源，请先转让或删除它们再删除团队。',
    'group_name_exists' => '你名下已经有同名的团队了。',
    'group_share_not_found' => '这项资源没有共享给这个团队。',
    'agent_not_allowed' => 'AI 模型不能执行这个操作，请用人类账号。',
    'channel_forbidden' => '你没有权限执行这个操作。',
    'channel_studio_forbidden' => '你不能查看或管理这个工作室（团队）的版本。',
    'channel_name_exists' => '这个工作室（团队）里已经有同名的版本了。',
    'channel_has_content' => '版本里已经有译文、术语或逐词解析，不能删除。',
    'channel_link_self' => '不能指向这个版本自己。',
    'channel_link_target_missing' => '找不到这个版本，或者你没有权限查看它。',
    'channel_link_wbw_type' => '捆绑的逐词版本必须是逐词（wbw）类型。',
    'channel_link_fallback_type' => '降级版本必须与本版本类型相同。',
    'channel_link_term_source_channel' => '只有译文（translation）版本才能设置术语路径。',
    'channel_link_term_source_type' => '术语路径只能选译文（translation）版本。',
    'term_forbidden' => '你没有权限查看或修改这个术语。',
    'term_exists' => '这里已经有同一个词与义项（word@tag）的术语了。',
    'term_word_immutable' => '词与义项建立后不能修改。要改请新建一条术语，再把旧的重定向过去。',
    'term_if_match_required' => '修改术语要带上 If-Match（取自术语详情的 etag）。',
    'term_modified' => '这条术语在你编辑期间被别人改过了，请重新加载后再改。',
    'term_language_follows_channel' => '版本里的术语语言跟随版本，不能单独修改。',
    'term_redirect_self' => '不能重定向到自己。',
    'term_import_word_required' => '这一行没有 word。导入只能新建或修改；要删除术语请在列表里删。',
    'term_import_meaning_required' => '这一行没有 meaning（释义不能为空）。',
    'term_import_language_required' => 'studio 通用术语（没有 channel_id）要填 language。',
    'term_import_channel_not_in_studio' => 'channel :channel 不存在，或不属于这个 studio。',
    'term_import_not_found' => '找不到 id 为 :id 的术语，或它不在这次导入的范围里。',
    'term_import_modified' => '这条术语在导出之后被别人改过了，请重新导出再改。',
    'term_import_bad_value' => ':field 的取值「:value」不对。',
    'term_import_unreadable' => '读不了这个文件，请上传 xlsx 格式的表格。',
    'term_import_header_required' => '表格第一行要有列名，至少包含 word 与 meaning。',
    'term_import_too_many' => '一次最多导入 :max 行。',
];
