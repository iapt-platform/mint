import { useIntl } from "react-intl";
import { Button, Card, Form } from "antd";
import { message } from "antd";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { Col, Row, Space } from "antd";
import { CloseOutlined } from "@ant-design/icons";

import { IComment } from "./DiscussionItem";
import { put } from "../../request";
import { ICommentRequest, ICommentResponse } from "../api/Comment";
import MDEditor from "@uiw/react-md-editor";

interface IWidget {
  data: IComment;
  onCreated?: Function;
  onUpdated?: Function;
  onClose?: Function;
}
const DiscussionEditWidget = ({
  data,
  onCreated,
  onUpdated,
  onClose,
}: IWidget) => {
  const intl = useIntl();

  return (
    <Card
      title={<span>{data.user.nickName}</span>}
      extra={
        <Button
          shape="circle"
          size="small"
          icon={<CloseOutlined />}
          onClick={() => {
            if (typeof onClose !== "undefined") {
              onClose();
            }
          }}
        />
      }
      style={{ width: "auto" }}
    >
      <ProForm<IComment>
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
          const url = `/v2/discussion/${data.id}`;
          const newData: ICommentRequest = {
            title: values.title,
            content: values.content,
          };
          console.info("DiscussionEdit api request", url, newData);
          put<ICommentRequest, ICommentResponse>(url, newData)
            .then((json) => {
              console.debug("DiscussionEdit api response", json);
              if (json.ok) {
                console.log(intl.formatMessage({ id: "flashes.success" }));
                if (typeof onUpdated !== "undefined") {
                  const newData = {
                    id: json.data.id, //id未提供为新建
                    resId: json.data.res_id,
                    resType: json.data.res_type,
                    user: json.data.editor,
                    parent: json.data.parent,
                    title: json.data.title,
                    content: json.data.content,
                    status: json.data.status,
                    childrenCount: json.data.children_count,
                    createdAt: json.data.created_at,
                    updatedAt: json.data.updated_at,
                  };
                  onUpdated(newData);
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
        <ProForm.Group>
          <ProFormText
            name="title"
            hidden={data.parent ? true : false}
            label={intl.formatMessage({ id: "forms.fields.title.label" })}
            tooltip="最长为 24 位"
            rules={[{ required: data.parent ? false : true }]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <Form.Item
            name="content"
            label={intl.formatMessage({ id: "forms.fields.content.label" })}
          >
            <MDEditor style={{ width: "100%" }} />
          </Form.Item>
        </ProForm.Group>
      </ProForm>
    </Card>
  );
};

export default DiscussionEditWidget;
