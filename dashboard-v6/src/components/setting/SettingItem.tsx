import { useIntl } from "react-intl";
import { useMemo, type JSX } from "react";
import {
  type RadioChangeEvent,
  Switch,
  Typography,
  Radio,
  Select,
  Transfer,
} from "antd";
import type { TransferKey } from "antd/lib/transfer/interface";

import {
  onChange as onSettingChanged,
  settingInfo,
  type ISettingItem,
} from "../../reducers/setting";
import { useAppSelector } from "../../hooks";
import store from "../../store";
import type { ISetting } from "./default";

const { Text } = Typography;

interface IWidgetSettingItem {
  data?: ISetting;
  autoSave?: boolean;
  bordered?: boolean;
  onChange?: (key: string, newTargetKeys: string[] | boolean | string) => void;
}

const SettingItemWidget = ({
  data,
  bordered = true,
  onChange,
  autoSave = true,
}: IWidgetSettingItem) => {
  const intl = useIntl();
  const settings: ISettingItem[] | undefined = useAppSelector(settingInfo);

  // 用 useMemo 替代 useEffect + useState，派生当前值
  const value = useMemo(() => {
    const currSetting = settings?.find((element) => element.key === data?.key);
    if (typeof currSetting !== "undefined") {
      return currSetting.value;
    }
    return data?.defaultValue;
  }, [data?.key, data?.defaultValue, settings]);

  const targetKeys = useMemo(() => {
    if (Array.isArray(value)) {
      return value as string[];
    }
    return [];
  }, [value]);

  let content: JSX.Element = <></>;

  if (typeof data === "undefined") {
    return content;
  }

  const description: string | undefined = data.description
    ? intl.formatMessage({ id: data.description })
    : undefined;

  switch (typeof data.defaultValue) {
    case "number":
      break;

    case "object":
      if (data.widget === "transfer" && typeof data.options !== "undefined") {
        content = (
          <Transfer
            dataSource={data.options.map((item) => ({
              key: item.value,
              title: intl.formatMessage({ id: item.label }),
            }))}
            titles={["备选", intl.formatMessage({ id: "labels.selected" })]}
            targetKeys={targetKeys}
            onChange={(newTargetKeys: TransferKey[]) => {
              const keys = newTargetKeys.map(String);
              store.dispatch(onSettingChanged({ key: data.key, value: keys }));
              onChange?.(data.key, keys);
            }}
            render={(item) => item.title ?? ""}
            oneWay
          />
        );
      }
      break;

    case "string":
      if (
        data.widget === "radio-button" &&
        typeof data.options !== "undefined"
      ) {
        content = (
          <Radio.Group
            value={value}
            buttonStyle="solid"
            onChange={(e: RadioChangeEvent) => {
              if (autoSave) {
                store.dispatch(
                  onSettingChanged({ key: data.key, value: e.target.value })
                );
              }
              onChange?.(data.key, e.target.value);
            }}
          >
            {data.options.map((item, id) => (
              <Radio.Button key={id} value={item.value}>
                {intl.formatMessage({ id: item.label })}
              </Radio.Button>
            ))}
          </Radio.Group>
        );
      } else if (typeof data.options !== "undefined") {
        content = (
          <Select
            value={value as string}
            style={{ width: 120 }}
            variant={bordered ? "outlined" : "borderless"}
            onChange={(val: string) => {
              if (autoSave) {
                store.dispatch(onSettingChanged({ key: data.key, value: val }));
              }
              onChange?.(data.key, val);
            }}
            options={data.options.map((item) => ({
              value: item.value,
              label: intl.formatMessage({ id: item.label }),
            }))}
          />
        );
      }
      break;

    case "boolean":
      content = (
        <Switch
          checked={value as boolean}
          onChange={(checked) => {
            if (autoSave) {
              store.dispatch(
                onSettingChanged({ key: data.key, value: checked })
              );
            }
            onChange?.(data.key, checked);
          }}
        />
      );
      break;

    default:
      break;
  }

  return (
    <div style={{ marginBottom: 10 }}>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          flexWrap: "wrap",
        }}
      >
        <div>
          <div>
            <Text>{intl.formatMessage({ id: data.label })}</Text>
          </div>
          <Text type="secondary">{description}</Text>
        </div>
        <div style={{ marginLeft: "auto" }}>{content}</div>
      </div>
    </div>
  );
};

export default SettingItemWidget;
