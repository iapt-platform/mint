import { useRef } from "react";
import {
  ProForm,
  ProFormDependency,
  type ProFormInstance,
  ProFormSelect,
} from "@ant-design/pro-components";
import { PlusOutlined } from "@ant-design/icons";

import { Button, Divider, Popover } from "antd";
import WatchList from "./WatchList";

import { useIntl } from "react-intl";
import type { IUser } from "../../api/Auth";
import UserSelect from "../users/UserSelect";
import AiAssistantSelect from "../ai-model/AiAssistantSelect";

export interface IDataType {
  user_type?: "user" | "ai-assistant";
  user_id?: string;
}

interface IWidget {
  data?: IUser[];
  onFinish?: ((formData: IDataType) => Promise<boolean | void>) | undefined;
  onDelete?: ((user: IUser) => Promise<boolean | void>) | undefined;
}

export const WatchAddButton = ({ data, onFinish, onDelete }: IWidget) => {
  return (
    <Popover
      trigger={"click"}
      content={<WatchAdd data={data} onFinish={onFinish} onDelete={onDelete} />}
    >
      <Button type="text" icon={<PlusOutlined />} />
    </Popover>
  );
};
const WatchAdd = ({ data, onFinish, onDelete }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance | undefined>(undefined);
  return (
    <div>
      <ProForm<IDataType> formRef={formRef} onFinish={onFinish}>
        <ProForm.Group>
          <ProFormSelect
            options={[
              { label: "用户", value: "user" },
              {
                label: intl.formatMessage({ id: "labels.ai-assistant" }),
                value: "ai-assistant",
              },
            ]}
            width="xs"
            name="userType"
            label={"用户类型"}
          />
          <ProFormDependency name={["userType"]}>
            {({ userType }) => {
              if (userType === "user") {
                return <UserSelect name="user_id" multiple={false} />;
              } else {
                return <AiAssistantSelect name="user_id" multiple={false} />;
              }
            }}
          </ProFormDependency>
        </ProForm.Group>
      </ProForm>
      <Divider />
      <WatchList data={data} onDelete={onDelete} />
    </div>
  );
};

export default WatchAdd;
