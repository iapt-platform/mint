import { useState } from "react";
import { useIntl } from "react-intl";
import { Button, Card } from "antd";
import { Input, message } from "antd";
import { SaveOutlined } from "@ant-design/icons";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { Col, Row, Space } from "antd";

import { IComment } from "./CommentItem";
import { post } from "../../request";
import { ICommentRequest, ICommentResponse } from "../api/Comment";

interface IWidget {
  data: IComment;
  onCreated?: Function;
}
const Widget = ({ data, onCreated }: IWidget) => {
  const intl = useIntl();

  const formItemLayout = {
    labelCol: { span: 4 },
    wrapperCol: { span: 20 },
  };
  return (
    <div>
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
          post<ICommentRequest, ICommentResponse>(`/v2/discussion`, {
            res_id: data.resId,
            res_type: data.resType,
            title: values.title,
            content: values.content,
          })
            .then((json) => {
              console.log(json);
              if (json.ok) {
                message.success(intl.formatMessage({ id: "flashes.success" }));
                if (typeof onCreated !== "undefined") {
                  onCreated(json.data);
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
        request={async () => {
          return data;
        }}
      >
        {data.parent ? (
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
