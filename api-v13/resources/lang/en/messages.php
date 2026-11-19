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
    'discussion_assessment_target' => 'Assessments can only be attached to sentences.',
    'discussion_assessment_method' => 'The assessment method must be 1–16 letters, digits, dots, underscores or hyphens, starting with a letter or digit.',
    'discussion_score_not_allowed' => 'Only assessments can have a score.',
    'discussion_assessment_type' => 'An assessment cannot be converted to or from another type.',
    'discussion_assessment_no_status' => 'Assessments cannot be closed or reopened.',
    'agent_grant_by_model' => 'Only a person can grant an AI model permission to write.',
    'agent_grant_model_not_found' => 'AI model not found.',
    'agent_grant_not_owner' => 'This AI model does not belong to you.',
    'agent_grant_no_power' => 'You cannot edit this channel, so you cannot grant it to an AI model.',
    'agent_grant_human' => 'Only an AI model can use an agent grant.',
    'agent_grant_required' => 'An AI model needs a grant from a person (X-Agent-Grant) to write.',
    'agent_grant_invalid' => 'The agent grant is invalid, expired or revoked.',
    'agent_grant_scope' => 'The agent grant does not cover this content.',

    'agent_device_required' => 'Guests must send a device_id.',
    'agent_client_ip_required' => 'Guests must come with a client IP.',
    'agent_thread_forbidden' => 'This conversation belongs to another account. Start a new conversation.',
    'agent_quota_insufficient_user' => 'Not enough credits for this question today. Reading the texts earns more credits.',
    'agent_quota_insufficient_guest' => 'You have used today\'s free questions. Sign in to get more each day.',
    'agent_quota_ip_cap' => 'Too many questions from this network today. Please sign in to continue.',
    'credit_user_not_found' => 'No user with this ID, username or email.',
    'credit_balance_negative' => 'The balance would drop below zero (current balance: :balance).',
    'reading_time_day_out_of_range' => 'Reading time can only be reported for today or the past 2 days.',
    'stats_range_too_long' => 'The date range can be at most :days days.',
    'agent_feedback_tags' => 'These tags do not match the rating.',

    'group_forbidden' => 'Your role in this team does not allow this action.',
    'group_user_not_found' => 'No such user.',
    'group_member_exists' => 'This user is already a member of the team.',
    'group_owner_immutable' => 'The team owner cannot be changed or removed.',
    'group_not_member' => 'You are not a member of this team.',
    'group_has_resources' => 'The team still owns resources. Transfer or delete them before deleting the team.',
    'group_name_exists' => 'You already have a team with this name.',
    'group_share_not_found' => 'This resource is not shared with this team.',
    'agent_not_allowed' => 'AI models cannot perform this action. Please use a human account.',
    'channel_forbidden' => 'You are not allowed to do this.',
    'channel_studio_forbidden' => 'You cannot view or manage the channels of this studio (team).',
    'channel_name_exists' => 'A channel with this name already exists in this studio (team).',
    'channel_has_content' => 'The channel already has translations, terms or word-by-word data and cannot be deleted.',
    'channel_link_self' => 'A channel cannot point to itself.',
    'channel_link_target_missing' => 'This channel does not exist, or you are not allowed to see it.',
    'channel_link_wbw_type' => 'The bound word-by-word channel must be of type wbw.',
    'channel_link_fallback_type' => 'A fallback channel must be of the same type as this channel.',
    'channel_link_term_source_channel' => 'Only translation channels can have term sources.',
    'channel_link_term_source_type' => 'Term sources must be translation channels.',
    'term_forbidden' => 'You are not allowed to view or change this term.',
    'term_exists' => 'A term with the same word and sense (word@tag) already exists here.',
    'term_word_immutable' => 'The word and sense cannot be changed once created. Create a new term and redirect the old one to it.',
    'term_if_match_required' => 'Changing a term requires If-Match (the etag from the term details).',
    'term_modified' => 'Someone else changed this term while you were editing. Reload it and try again.',
    'term_language_follows_channel' => 'The language of a term in a channel follows the channel and cannot be changed on its own.',
    'term_redirect_self' => 'A term cannot redirect to itself.',
    'term_import_word_required' => 'This row has no word. Import only creates or updates terms; delete terms from the list.',
    'term_import_meaning_required' => 'This row has no meaning.',
    'term_import_language_required' => 'A studio-wide term (no channel_id) needs a language.',
    'term_import_channel_not_in_studio' => 'Channel :channel does not exist or does not belong to this studio.',
    'term_import_not_found' => 'No term with id :id in the scope of this import.',
    'term_import_modified' => 'Someone changed this term after it was exported. Export again and redo the change.',
    'term_import_bad_value' => 'Invalid value for :field: :value.',
    'term_import_unreadable' => 'This file cannot be read. Upload an xlsx spreadsheet.',
    'term_import_header_required' => 'The first row must hold column names, including at least word and meaning.',
    'term_import_too_many' => 'At most :max rows can be imported at a time.',
];
