import { useState } from "react";
import { LoadingOutlined, WarningOutlined } from "@ant-design/icons";

import WbwFactors from "./WbwFactors";
import { IWbw, TWbwDisplayMode } from "./WbwWord";
import { IPreferenceResponse } from "../../api/Dict";
import { Space } from "antd";

interface IWidget {
  initValue: IWbw;
  display?: TWbwDisplayMode;
  onChange?: (key: string) => Promise<IPreferenceResponse>;
}
const WbwFactorsEditor = ({ initValue, display, onChange }: IWidget) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(false);

  return (
    <Space>
      {loading ? <LoadingOutlined /> : error ? <WarningOutlined /> : <></>}
      <WbwFactors
        key="factors"
        data={initValue}
        display={display}
        onChange={async (e: string) => {
          console.log("factor change", e);
          if (onChange) {
            setLoading(true);
            setError(false);
            const response = await onChange(e);
            setLoading(false);
            if (!response.ok) {
              setError(true);
            }
          }
        }}
      />
    </Space>
  );
};

export default WbwFactorsEditor;
