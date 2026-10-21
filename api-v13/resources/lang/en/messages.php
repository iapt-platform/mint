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

    'mail_send_failed' => 'The email could not be sent. Please try again later.',
    'email_registered' => 'This email is already registered.',
    'sign_up_code_invalid' => 'The verification code is incorrect or has expired.',
    'invite_invalid' => 'This invitation is invalid or has already been used.',

    'discussion_closed' => 'This topic is closed and no longer accepts replies.',
    'discussion_reply_to_reply' => 'You cannot reply to a reply. Reply to the topic instead.',
    'discussion_target_not_found' => 'The item this discussion is attached to does not exist.',
    'discussion_topic_only' => 'Replies cannot have this field.',
    'discussion_forbidden' => 'You do not have permission to do this.',
    'discussion_severity_not_allowed' => 'This severity does not match the discussion type.',
    'discussion_annotation_target' => 'Annotations can only be attached to sentences.',
    'discussion_annotation_no_status' => 'Annotations cannot be closed or reopened.',
    'discussion_type_group' => 'A comment thread and an annotation cannot be converted into each other.',
    'discussion_commentary_format' => 'Commentary content must be sentence templates such as {{book-para-start-end}}.',
    'discussion_anchor_thread' => 'This discussion type cannot be anchored to a text position.',
    'discussion_glossary_target' => 'Attached glossary terms can only be added to sentences.',
    'agent_grant_by_model' => 'Only a person can grant an AI model permission to write.',
    'agent_grant_model_not_found' => 'AI model not found.',
    'agent_grant_not_owner' => 'This AI model does not belong to you.',
    'agent_grant_no_power' => 'You cannot edit this channel, so you cannot grant it to an AI model.',
    'agent_grant_human' => 'Only an AI model can use an agent grant.',
    'agent_grant_required' => 'An AI model needs a grant from a person (X-Agent-Grant) to write.',
    'agent_grant_invalid' => 'The agent grant is invalid, expired or revoked.',
    'agent_grant_scope' => 'The agent grant does not cover this content.',

];
