import { Divider, Transfer } from "antd";
import { useEffect, useState } from "react";
import { TransferDirection } from "antd/es/transfer";

import { get, post } from "../../../request";
import {
  IAiModelListResponse,
  IAiModelResponse,
  IAiModelSystem,
} from "../../../components/api/ai";
import { useAppSelector } from "../../../hooks";
import { siteInfo } from "../../../reducers/layout";

interface IWidget {
  type: "wbw" | "chat";
  models?: RecordType[];
}
const ModelSelect = ({ type, models }: IWidget) => {
  const [targetKeys, setTargetKeys] = useState<string[]>([]);
  const [selectedKeys, setSelectedKeys] = useState<string[]>([]);

  const site = useAppSelector(siteInfo);

  useEffect(() => {
    let target: string[] = [];
    switch (type) {
      case "wbw":
        target = site?.settings?.models?.wbw?.map((item) => item.uid) ?? [];
        break;
      case "chat":
        target = site?.settings?.models?.chat?.map((item) => item.uid) ?? [];
        break;
    }
    setTargetKeys(target);
  }, [site?.settings?.models, type]);

  const onChange = (
    nextTargetKeys: string[],
    direction: TransferDirection,
    moveKeys: string[]
  ) => {
    setTargetKeys(nextTargetKeys);
    const url = `/v2/system-model`;
    post<IAiModelSystem, IAiModelResponse>(url, {
      view: type,
      models: nextTargetKeys,
    })
      .then((json) => {
        if (json.ok) {
          console.info("system model save ok");
        } else {
          console.error("system model save");
        }
      })
      .catch((e) => console.error(e));
  };

  const onSelectChange = (
    sourceSelectedKeys: string[],
    targetSelectedKeys: string[]
  ) => {
    console.log("sourceSelectedKeys:", sourceSelectedKeys);
    console.log("targetSelectedKeys:", targetSelectedKeys);
    setSelectedKeys([...sourceSelectedKeys, ...targetSelectedKeys]);
  };

  return (
    <Transfer
      dataSource={models}
      titles={["All", "Selected"]}
      targetKeys={targetKeys}
      selectedKeys={selectedKeys}
      onChange={onChange}
      onSelectChange={onSelectChange}
      render={(item) => item.title}
    />
  );
};

interface RecordType {
  key: string;
  title: string;
  description?: string;
}

const Widget = () => {
  const [models, setModels] = useState<RecordType[]>();

  useEffect(() => {
    const url = `/v2/ai-model?view=chat`;
    console.log("api request", url);
    get<IAiModelListResponse>(url).then((json) => {
      console.log("api response", json);
      if (json.ok) {
        setModels(
          json.data.rows.map((item) => {
            return {
              key: item.uid,
              title: item.name,
              description: item.model,
            };
          })
        );
      }
    });
  }, []);

  return (
    <div>
      <Divider>WBW</Divider>
      <ModelSelect models={models} type="wbw" />
      <Divider>Chat</Divider>
      <ModelSelect models={models} type="chat" />
    </div>
  );
};

export default Widget;
