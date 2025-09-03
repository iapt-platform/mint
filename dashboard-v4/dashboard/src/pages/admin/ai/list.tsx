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

interface RecordType {
  key: string;
  title: string;
  description?: string;
}

const Widget = () => {
  const [models, setModels] = useState<RecordType[]>();

  const [targetKeys, setTargetKeys] = useState<string[]>([]);
  const [selectedKeys, setSelectedKeys] = useState<string[]>([]);

  const site = useAppSelector(siteInfo);

  useEffect(() => {
    const url = `/v2/ai-model?view=chat`;
    get<IAiModelListResponse>(url)
      .then((json) => {
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
      })
      .finally(() => {
        const wbw = site?.settings?.models?.wbw?.map((item) => item.uid) ?? [];
        setTargetKeys(wbw);
      });
  }, [site?.settings?.models?.wbw]);

  const onChange = (
    nextTargetKeys: string[],
    direction: TransferDirection,
    moveKeys: string[]
  ) => {
    setTargetKeys(nextTargetKeys);
    const url = `/v2/system-model`;
    post<IAiModelSystem, IAiModelResponse>(url, {
      view: "wbw",
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
    <div>
      <Divider>WBW</Divider>
      <Transfer
        dataSource={models}
        titles={["All", "Selected"]}
        targetKeys={targetKeys}
        selectedKeys={selectedKeys}
        onChange={onChange}
        onSelectChange={onSelectChange}
        render={(item) => item.title}
      />
      <Divider>Chat</Divider>
    </div>
  );
};

export default Widget;
