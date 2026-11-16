import { App, Divider, Transfer } from "antd";
import { useState } from "react";
import { useIntl } from "react-intl";
import { saveSystemModels } from "../../api/admin";
import { useAppSelector } from "../../hooks";
import { useChatModelOptions, type IModelOption } from "../../hooks/useAdmin";
import { siteInfo } from "../../reducers/layout";

type TModelUse = "wbw" | "chat" | "summarize";
const USES: TModelUse[] = ["wbw", "chat", "summarize"];

/** 某一种用途的系统模型：左边全部可选模型，右边已选（改动即保存）。 */
const ModelUseTransfer = ({ use, models }: { use: TModelUse; models?: IModelOption[] }) => {
  const intl = useIntl();
  const { message } = App.useApp();
  const site = useAppSelector(siteInfo);
  // 站点设置里只有 wbw / chat 两类（summarize 还没有回读，与 v4 一致）；用户改过就以改后的为准
  const saved = use === "wbw" || use === "chat" ? site?.settings?.models?.[use] : undefined;
  const [edited, setEdited] = useState<string[] | null>(null);
  const targetKeys = edited ?? saved?.map((item) => item.uid) ?? [];
  const [selectedKeys, setSelectedKeys] = useState<string[]>([]);

  return (
    <Transfer
      dataSource={models}
      titles={[intl.formatMessage({ id: "admin.models.all" }), intl.formatMessage({ id: "admin.models.selected" })]}
      targetKeys={targetKeys}
      selectedKeys={selectedKeys}
      onChange={(next) => {
        const keys = next.map(String);
        setEdited(keys);
        saveSystemModels(use, keys)
          .then((json) => (json.ok ? message.success(intl.formatMessage({ id: "flashes.success" })) : message.error(json.message)))
          .catch((e) => message.error(String(e?.message ?? e)));
      }}
      onSelectChange={(source, target) => setSelectedKeys([...source, ...target].map(String))}
      render={(item) => item.title}
    />
  );
};

/** 系统级 AI 模型：逐词解析 / 对话 / 摘要 各用哪些模型。 */
const SystemModels = () => {
  const models = useChatModelOptions();
  return (
    <div>
      {USES.map((use) => (
        <div key={use}>
          <Divider>{use}</Divider>
          <ModelUseTransfer models={models} use={use} />
        </div>
      ))}
    </div>
  );
};

export default SystemModels;
