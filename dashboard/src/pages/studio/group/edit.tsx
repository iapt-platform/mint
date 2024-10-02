import { useState } from "react";
import { useIntl } from "react-intl";
import { useParams } from "react-router-dom";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { message, Card } from "antd";

import { IGroupRequest, IGroupResponse } from "../../../components/api/Group";
import { get, put } from "../../../request";
import GoBack from "../../../components/studio/GoBack";

interface IFormData {
  id: string;
  name: string;
  description: string;
}
const Widget = () => {
  const intl = useIntl();
  const { studioname, groupId } = useParams(); //url 参数
  const [title, setTitle] = useState("Loading");

  return (
    <Card
      title={<GoBack to={`/studio/${studioname}/group/list`} title={title} />}
    >
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          console.log(values);
          const res = await put<IGroupRequest, IGroupResponse>(
            `/v2/group/${groupId}`,
            values
          );
          if (res.ok) {
            message.success(intl.formatMessage({ id: "flashes.success" }));
          }
        }}
        formKey="group_edit"
        request={async () => {
          const res = await get<IGroupResponse>(`/v2/group/${groupId}`);
          setTitle(res.data.name);
          document.title = `${res.data.name}`;
          return {
            id: res.data.uid,
            name: res.data.name,
            description: res.data.description,
          };
        }}
      >
        <ProForm.Group>
          <ProFormText
            width="md"
            name="name"
            required
            label={intl.formatMessage({ id: "forms.fields.name.label" })}
            rules={[
              {
                required: true,
              },
            ]}
          />
        </ProForm.Group>

        <ProForm.Group>
          <ProFormTextArea
            width="md"
            name="description"
            label={intl.formatMessage({
              id: "forms.fields.description.label",
            })}
          />
        </ProForm.Group>
      </ProForm>
    </Card>
  );
};

export default Widget;
