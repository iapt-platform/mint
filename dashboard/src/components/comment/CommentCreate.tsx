import { useIntl } from "react-intl";
import { message } from "antd";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { Col, Row, Space } from "antd";

import { IComment } from "./CommentItem";
import { post } from "../../request";
import { ICommentRequest, ICommentResponse } from "../api/Comment";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

interface IWidget {
  resId: string;
  resType: string;
  parent?: string;
  onCreated?: Function;
}
const Widget = ({ resId, resType, parent, onCreated }: IWidget) => {
  const intl = useIntl();
  const _currUser = useAppSelector(_currentUser);
  const formItemLayout = {
    labelCol: { span: 4 },
    wrapperCol: { span: 20 },
  };
  return (
    <div>
      <div>{_currUser?.nickName}:</div>
      <ProForm<IComment>
        {...formItemLayout}
        layout="horizontal"
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

          post<ICommentRequest, ICommentResponse>(`/v2/discussion`, {
            res_id: resId,
            res_type: resType,
            parent: parent,
            title: values.title,
            content: values.content,
          })
            .then((json) => {
              console.log("new discussion", json);
              if (json.ok) {
                if (typeof onCreated !== "undefined") {
                  onCreated({
                    id: json.data.id,
                    resId: json.data.res_id,
                    resType: json.data.res_type,
                    user: {
                      id: json.data.editor?.id ? json.data.editor.id : "null",
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

        <ProFormTextArea
          name="content"
          label={intl.formatMessage({ id: "forms.fields.content.label" })}
          placeholder={intl.formatMessage({
            id: "forms.fields.content.placeholder",
          })}
        />
      </ProForm>
    </div>
  );
};

export default Widget;
