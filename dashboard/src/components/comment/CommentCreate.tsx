import { useIntl } from "react-intl";
import { Form, message } from "antd";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { Col, Row, Space } from "antd";
import ReactQuill from "react-quill";
import "react-quill/dist/quill.snow.css";

import { IComment } from "./CommentItem";
import { post } from "../../request";
import { ICommentRequest, ICommentResponse } from "../api/Comment";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { useRef } from "react";
import MDEditor from "@uiw/react-md-editor";

export type TContentType = "text" | "markdown" | "html";

interface IWidget {
  resId?: string;
  resType?: string;
  parent?: string;
  onCreated?: Function;
  contentType?: TContentType;
}
const Widget = ({
  resId,
  resType,
  contentType = "html",
  parent,
  onCreated,
}: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();
  const _currUser = useAppSelector(_currentUser);
  const formItemLayout = {
    labelCol: { span: 4 },
    wrapperCol: { span: 20 },
  };
  if (typeof _currUser === "undefined") {
    return <></>;
  } else {
    return (
      <div>
        <div>{_currUser?.nickName}:</div>
        <div>
          <ProForm<IComment>
            {...formItemLayout}
            layout="horizontal"
            formRef={formRef}
            submitter={{
              render: (props, doms) => {
                return (
                  <Row>
                    <Col span={14} offset={4}>
                      <Space>{doms}</Space>
                    </Col>
                  </Row>
                );
              },
            }}
            onFinish={async (values) => {
              //新建
              console.log("create", resId, resType, parent);
              console.log("value", values);
              post<ICommentRequest, ICommentResponse>(`/v2/discussion`, {
                res_id: resId,
                res_type: resType,
                parent: parent,
                title: values.title,
                content: values.content,
                content_type: contentType,
              })
                .then((json) => {
                  console.log("new discussion", json);
                  if (json.ok) {
                    formRef.current?.resetFields();
                    if (typeof onCreated !== "undefined") {
                      onCreated({
                        id: json.data.id,
                        resId: json.data.res_id,
                        resType: json.data.res_type,
                        user: {
                          id: json.data.editor?.id
                            ? json.data.editor.id
                            : "null",
                          nickName: json.data.editor?.nickName
                            ? json.data.editor.nickName
                            : "null",
                          realName: json.data.editor?.userName
                            ? json.data.editor.userName
                            : "null",
                          avatar: json.data.editor?.avatar
                            ? json.data.editor.avatar
                            : "null",
                        },
                        title: json.data.title,
                        parent: json.data.parent,
                        content: json.data.content,
                        createdAt: json.data.created_at,
                        updatedAt: json.data.updated_at,
                      });
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
            {parent ? (
              <></>
            ) : (
              <ProFormText
                name="title"
                label={intl.formatMessage({ id: "forms.fields.title.label" })}
                tooltip="最长为 24 位"
                placeholder={intl.formatMessage({
                  id: "forms.message.title.required",
                })}
                rules={[{ required: true, message: "这是必填项" }]}
              />
            )}
            {contentType === "text" ? (
              <ProFormTextArea
                name="content"
                label={intl.formatMessage({ id: "forms.fields.content.label" })}
                placeholder={intl.formatMessage({
                  id: "forms.fields.content.placeholder",
                })}
              />
            ) : contentType === "html" ? (
              <Form.Item
                name="content"
                label={intl.formatMessage({ id: "forms.fields.content.label" })}
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
                label={intl.formatMessage({ id: "forms.fields.content.label" })}
              >
                <MDEditor />
              </Form.Item>
            ) : (
              <></>
            )}
          </ProForm>
        </div>
      </div>
    );
  }
};

export default Widget;
