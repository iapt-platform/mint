import { useEffect, useState } from "react";
import { IAiModel, IAiModelResponse } from "../api/ai";
import { get } from "../../request";
import { Alert, Tag, Typography } from "antd";
const { Text } = Typography;

interface IAiCtl {
  model?: string;
}
const AiCtl = ({ model }: IAiCtl) => {
  const [curr, setCurr] = useState<IAiModel>();
  useEffect(() => {
    const url = `/v2/ai-model/${model}`;
    console.info("api request", url);
    get<IAiModelResponse>(url).then((json) => {
      console.info("api response", json);
      if (json.ok) {
        setCurr(json.data);
      }
    });
  }, [model]);
  return (
    <Alert
      message={
        <div>
          <Text strong style={{ display: "block" }}>
            {curr?.name}
          </Text>
          <Tag>{curr?.model}</Tag>
          <Text>{curr?.url}</Text>
        </div>
      }
      type="info"
    />
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IAiCtl;
  console.log(prop);
  return (
    <>
      <AiCtl {...prop} />
    </>
  );
};

export default Widget;
