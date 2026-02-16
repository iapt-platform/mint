import { Dropdown, Progress, Space } from "antd";
import { useState } from "react";
import { LoadingOutlined } from "@ant-design/icons";
import { setValue } from "./DictPreference";

interface IWidget {
  value?: number;
  onChange?: (value: number) => void;
}
const DictConfidence = ({ value, onChange }: IWidget) => {
  const [loading, setLoading] = useState(false);

  const confidence = [0, 40, 60, 80, 100];
  return (
    <Space>
      {loading ? <LoadingOutlined /> : <></>}
      <div style={{ width: 100 }}>
        <Dropdown
          menu={{
            items: confidence.map((item) => {
              return { key: item, label: item };
            }),
            onClick: async (info) => {
              setLoading(true);
              onChange && onChange(parseInt(info.key));
              setLoading(false);
            },
          }}
        >
          <Progress
            size="small"
            percent={Math.round(value ?? 0)}
            status={value !== undefined && value < 50 ? "exception" : undefined}
          />
        </Dropdown>
      </div>
    </Space>
  );
};

export default DictConfidence;
