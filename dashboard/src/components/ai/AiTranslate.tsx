import { Button, Typography } from "antd";
import { useState } from "react";
import { LoadingOutlined } from "@ant-design/icons";

import Marked from "../general/Marked";
import { post } from "../../request";
import { IAiTranslateRequest, IAiTranslateResponse } from "../api/ai";

const { Text } = Typography;

interface IAiTranslateWidget {
  origin?: string;
}

const AiTranslate = ({ origin }: IAiTranslateWidget) => {
  const [loading, setLoading] = useState(false);
  const [translation, setTranslation] = useState<string>();
  const [error, setError] = useState<string>();
  const onTranslate = (origin?: string) => {
    if (typeof origin === "undefined") {
      return;
    }
    const url = "/v2/ai-translate";
    const data = { origin: origin };
    console.info("api request", url, data);
    setLoading(true);
    post<IAiTranslateRequest, IAiTranslateResponse>(url, data)
      .then((json) => {
        console.debug("api response", json);
        if (json.ok) {
          setTranslation(json.data.choices[0].message.content);
        } else {
          setError(json.message);
        }
      })
      .finally(() => setLoading(false));
  };

  if (translation) {
    return <Marked text={translation} />;
  } else if (loading) {
    return <LoadingOutlined />;
  } else if (error) {
    return (
      <div>
        <Text type="danger">{error}</Text>
        <Button type="link" onClick={() => onTranslate(origin)}>
          再试一次
        </Button>
      </div>
    );
  } else {
    return (
      <Button type="link" onClick={() => onTranslate(origin)}>
        AI 翻译
      </Button>
    );
  }
};

export default AiTranslate;
