import { useIntl } from "react-intl";
import { Button, Card } from "antd";
import { message } from "antd";
import { ProForm, ProFormTextArea } from "@ant-design/pro-components";
import { Col, Row, Space } from "antd";

import { IComment } from "./CommentItem";
import { put } from "../../request";
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
      <Card
        title={<span>{data.user.nickName}</span>}
        extra={
          <Button shape="circle" size="small">
            xxx
          </Button>
        }
        style={{ width: "auto" }}
      >
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
            put<ICommentRequest, ICommentResponse>(
              `/v2/discussion/${data.id}`,
              {
                title: values.title,
                content: values.content,
              }
            )
              .then((json) => {
                console.log(json);
                if (json.ok) {
                  console.log(intl.formatMessage({ id: "flashes.success" }));
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
          <ProFormTextArea
            name="content"
            label={intl.formatMessage({ id: "forms.fields.content.label" })}
            placeholder={intl.formatMessage({
              id: "forms.fields.content.placeholder",
            })}
          />
        </ProForm>
      </Card>
    </div>
  );
};

export default Widget;
