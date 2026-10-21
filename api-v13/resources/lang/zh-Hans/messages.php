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
    'discussion_glossary_target' => '术语讨论只能挂在句子上。',
    'agent_grant_by_model' => '只有人类能给 AI 模型授权写入。',
    'agent_grant_model_not_found' => 'AI 模型不存在。',
    'agent_grant_not_owner' => '这个 AI 模型不属于你。',
    'agent_grant_no_power' => '你不能编辑这个 channel，也就不能把它授权给 AI 模型。',
    'agent_grant_human' => '写入授权只能由 AI 模型使用。',
    'agent_grant_required' => 'AI 模型写入需要人类的授权（X-Agent-Grant）。',
    'agent_grant_invalid' => '写入授权无效、已过期或已撤销。',
    'agent_grant_scope' => '写入授权不覆盖这条内容。',

];
