import { useIntl } from "react-intl";
import { Button, Card, Col, Form, Row, Space } from "antd";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { CloseOutlined } from "@ant-design/icons";

import MDEditor from "@uiw/react-md-editor";
import { updateDiscussion, type IComment } from "../../api/discussion";

interface IWidget {
  data: IComment;
  onUpdated?: (value: IComment) => void;
  onClose?: () => void;
}
const DiscussionEditWidget = ({ data, onUpdated, onClose }: IWidget) => {
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
          render: (_props, doms) => {
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
          if (!data.id) {
            return;
          }
          // 局部更新：回复没有标题，只发正文；话题发标题与正文，状态不动
          const updated = await updateDiscussion(
            data.id,
            data.parent
              ? { content: values.content ?? "" }
              : { title: values.title ?? "", content: values.content ?? "" }
          );
          onUpdated?.(updated);
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
