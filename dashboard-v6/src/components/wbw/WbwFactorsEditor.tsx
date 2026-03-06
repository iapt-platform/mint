import { useState } from "react";
import { Space } from "antd";
import { LoadingOutlined, WarningOutlined } from "@ant-design/icons";

import WbwFactors from "./WbwFactors";
import type { IPreferenceResponse } from "../../api/dict";
import type { IWbw, TWbwDisplayMode } from "../../types/wbw";

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
