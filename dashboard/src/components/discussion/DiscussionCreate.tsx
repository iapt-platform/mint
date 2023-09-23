import { useIntl } from "react-intl";
import { Form, message } from "antd";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import ReactQuill from "react-quill";
import "react-quill/dist/quill.snow.css";

import { IComment } from "./DiscussionItem";
import { post } from "../../request";
import {
  ICommentApiData,
  ICommentRequest,
  ICommentResponse,
} from "../api/Comment";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { useEffect, useRef, useState } from "react";
import MDEditor from "@uiw/react-md-editor";

export type TContentType = "text" | "markdown" | "html" | "json";

export const toIComment = (value: ICommentApiData): IComment => {
  return {
    id: value.id,
    resId: value.res_id,
    resType: value.res_type,
    user: value.editor,
    title: value.title,
    parent: value.parent,
    tplId: value.tpl_id,
    content: value.content,
    createdAt: value.created_at,
    updatedAt: value.updated_at,
  };
};
interface IWidget {
  resId?: string;
  resType?: string;
  parent?: string;
  topic?: IComment;
  contentType?: TContentType;
  onCreated?: Function;
  onTopicCreated?: Function;
}
const DiscussionCreateWidget = ({
  resId,
  resType,
  contentType = "html",
  parent,
  topic,
  onCreated,
  onTopicCreated,
}: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();
  const _currUser = useAppSelector(_currentUser);
  const [currParent, setCurrParent] = useState(parent);

  useEffect(() => setCurrParent(parent), [parent]);

  if (typeof _currUser === "undefined") {
    return <></>;
  } else {
    return (
      <div>
        <div>{_currUser?.nickName}:</div>
        <div>
          <ProForm<IComment>
            formRef={formRef}
            onFinish={async (values) => {
              //新建
              console.log("create", resId, resType, currParent, topic);
              console.log("value", values);
              let newParent: string | undefined;
              if (typeof currParent === "undefined") {
                if (typeof topic !== "undefined" && topic.tplId) {
                  const topicData: ICommentRequest = {
                    res_id: resId,
                    res_type: resType,
                    title: topic.title,
                    tpl_id: topic.tplId,
                    content: topic.content,
                    content_type: "markdown",
                  };
                  console.log("create topic", topicData);
                  const newTopic = await post<
                    ICommentRequest,
                    ICommentResponse
                  >(`/v2/discussion`, topicData);
                  if (newTopic.ok) {
                    setCurrParent(newTopic.data.id);
                    newParent = newTopic.data.id;
                    if (typeof onTopicCreated !== "undefined") {
                      onTopicCreated(toIComment(newTopic.data));
                    }
                  } else {
                    console.error("no parent id");
                    return;
                  }
                }
              }
              console.log("parent", currParent);
              post<ICommentRequest, ICommentResponse>(`/v2/discussion`, {
                res_id: resId,
                res_type: resType,
                parent: newParent ? newParent : currParent,
                title: values.title,
                content: values.content,
                content_type: contentType,
              })
                .then((json) => {
                  console.log("new discussion", json);
                  if (json.ok) {
                    formRef.current?.resetFields();
                    if (typeof onCreated !== "undefined") {
                      onCreated(toIComment(json.data));
                    }
                  } else {
                    message.error(json.message);
                  }
                })
                .catch((e) => {
                  message.error(e.message);
                });
            }}
            params={{}}
          >
            <ProForm.Group>
              <ProFormText
                name="title"
                width={"lg"}
                hidden={
                  typeof currParent !== "undefined" ||
                  typeof topic?.tplId !== "undefined"
                }
                label={intl.formatMessage({ id: "forms.fields.title.label" })}
                tooltip="最长为 24 位"
                placeholder={intl.formatMessage({
                  id: "forms.message.question.required",
                })}
                rules={[
                  { required: currParent || topic?.tplId ? false : true },
                ]}
              />
            </ProForm.Group>
            <ProForm.Group>
              {contentType === "text" ? (
                <ProFormTextArea
                  name="content"
                  label={intl.formatMessage({
                    id: "forms.fields.content.label",
                  })}
                  placeholder={intl.formatMessage({
                    id: "forms.fields.content.placeholder",
                  })}
                />
              ) : contentType === "html" ? (
                <Form.Item
                  name="content"
                  label={intl.formatMessage({
                    id: "forms.fields.content.label",
                  })}
                  tooltip="可以直接粘贴屏幕截图"
                >
                  <ReactQuill
                    theme="snow"
                    style={{ height: 180 }}
                    modules={{
                      toolbar: [
                        ["bold", "italic", "underline", "strike"],
                        ["blockquote", "code-block"],
                        [{ header: 1 }, { header: 2 }],
                        [{ list: "ordered" }, { list: "bullet" }],
                        [{ indent: "-1" }, { indent: "+1" }],
                        [{ size: ["small", false, "large", "huge"] }],
                        [{ header: [1, 2, 3, 4, 5, 6, false] }],
                        ["link", "image", "video"],
                        [{ color: [] }, { background: [] }],
                        [{ font: [] }],
                        [{ align: [] }],
                      ],
                    }}
                  />
                </Form.Item>
              ) : contentType === "markdown" ? (
                <Form.Item
                  name="content"
                  rules={[
                    {
                      required:
                        typeof currParent !== "undefined" ||
                        typeof topic?.tplId !== "undefined",
                    },
                  ]}
                  label={
                    typeof currParent === "undefined" &&
                    typeof topic?.tplId === "undefined"
                      ? intl.formatMessage({
                          id: "forms.message.question.description.option",
                        })
                      : intl.formatMessage({
                          id: "forms.fields.replay.label",
                        })
                  }
                >
                  <MDEditor
                    placeholder={
                      "问题的详细描述" +
                      (typeof currParent !== "undefined" &&
                      typeof topic?.tplId !== "undefined"
                        ? ""
                        : "（选填）")
                    }
                  />
                </Form.Item>
              ) : (
                <></>
              )}
            </ProForm.Group>
          </ProForm>
        </div>
      </div>
    );
  }
};

export default DiscussionCreateWidget;
