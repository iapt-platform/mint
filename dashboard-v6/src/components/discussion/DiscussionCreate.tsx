import { useIntl } from "react-intl";
import { Form } from "antd";
import {
  ProForm,
  type ProFormInstance,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { useEffect, useRef, useState } from "react";
import MDEditor from "@uiw/react-md-editor";
import {
  asDiscussionTarget,
  createReply,
  createTopic,
  SEVERITY_OPTIONS,
  type IComment,
  type TDiscussionType,
  type TResType,
  type TSeverity,
} from "../../api/discussion";
import { refreshDiscussionBadge } from "./utils";

interface IFormValues {
  title?: string;
  content?: string;
  type?: TDiscussionType;
  severity?: TSeverity;
}

interface IWidget {
  resId?: string;
  resType?: TResType;
  /** 传了就是回复这个话题 */
  parent?: string;
  /** 新话题的默认类型 */
  type?: TDiscussionType;
  /** 当前用户能发起的话题类型（来自 /v3/discussions/summary），多于一种时给出选择 */
  creatableTypes?: TDiscussionType[];
  /** 尚未创建的模板话题：先建话题，再回复 */
  topic?: IComment;
  onCreated?: (value: IComment) => void;
  onTopicCreated?: (value: IComment) => void;
}
const DiscussionCreateWidget = ({
  resId,
  resType,
  parent,
  topic,
  type,
  creatableTypes = [],
  onCreated,
  onTopicCreated,
}: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance<IFormValues> | undefined>(undefined);
  const _currUser = useAppSelector(_currentUser);
  const [currParent, setCurrParent] = useState(parent);
  const [currType, setCurrType] = useState<TDiscussionType>(
    type ?? creatableTypes[0] ?? "discussion"
  );

  useEffect(() => {
    setCurrParent(parent);
  }, [parent]);

  const target = asDiscussionTarget(resType);
  const isReply =
    typeof currParent !== "undefined" || typeof topic?.tplId === "string";

  if (typeof _currUser === "undefined") {
    return <></>;
  }
  return (
    <div>
      <div>{_currUser?.nickName}:</div>
      <div>
        <ProForm<IFormValues>
          formRef={formRef}
          autoFocusFirstInput={false}
          initialValues={{ type: currType }}
          onFinish={async (values) => {
            if (isReply) {
              let topicId = currParent;
              if (typeof topicId === "undefined") {
                // 在模板话题下跟帖：先建立模板话题，再回复
                if (!topic?.tplId || !resId || !target) {
                  return;
                }
                const newTopic = await createTopic({
                  target_type: target,
                  target_id: resId,
                  type: topic.type,
                  title: topic.title ?? "",
                  content: topic.content ?? undefined,
                  tpl_id: topic.tplId,
                });
                topicId = newTopic.id;
                setCurrParent(topicId);
                onTopicCreated?.(newTopic);
              }
              if (!topicId) {
                return;
              }
              const reply = await createReply(topicId, values.content ?? "");
              formRef.current?.resetFields();
              refreshDiscussionBadge(target, resId);
              onCreated?.(reply);
              return;
            }

            if (!resId || !target) {
              return;
            }
            const newType = values.type ?? currType;
            const created = await createTopic({
              target_type: target,
              target_id: resId,
              type: newType,
              title: values.title ?? "",
              content: values.content,
              severity: SEVERITY_OPTIONS[newType] ? values.severity : undefined,
            });
            formRef.current?.resetFields();
            refreshDiscussionBadge(target, resId);
            onCreated?.(created);
          }}
          params={{}}
        >
          {!isReply && creatableTypes.length > 1 && typeof type === "undefined" ? (
            <ProForm.Group>
              <ProFormSelect
                name="type"
                width="sm"
                allowClear={false}
                label={intl.formatMessage({ id: "forms.fields.type.label" })}
                options={creatableTypes.map((value) => ({
                  value,
                  label: value,
                }))}
                fieldProps={{
                  onChange: (value: TDiscussionType) => setCurrType(value),
                }}
              />
              {SEVERITY_OPTIONS[currType] ? (
                <ProFormSelect
                  name="severity"
                  width="sm"
                  label="severity"
                  options={SEVERITY_OPTIONS[currType]?.map((value) => ({
                    value,
                    label: value,
                  }))}
                />
              ) : undefined}
            </ProForm.Group>
          ) : undefined}
          <ProForm.Group>
            <ProFormText
              name="title"
              width={"lg"}
              hidden={isReply}
              label={intl.formatMessage({ id: "forms.fields.title.label" })}
              tooltip="最长为 24 位"
              placeholder={intl.formatMessage({
                id: "forms.message.question.required",
              })}
              rules={[{ required: !isReply }]}
            />
          </ProForm.Group>
          <ProForm.Group>
            <Form.Item
              name="content"
              rules={[{ required: isReply }]}
              label={
                isReply
                  ? intl.formatMessage({ id: "forms.fields.replay.label" })
                  : intl.formatMessage({
                      id: "forms.message.question.description.option",
                    })
              }
            >
              <MDEditor
                textareaProps={{
                  placeholder: "问题的详细描述" + (isReply ? "" : "（选填）"),
                  maxLength: 10000,
                }}
              />
            </Form.Item>
          </ProForm.Group>
        </ProForm>
      </div>
    </div>
  );
};

export default DiscussionCreateWidget;
