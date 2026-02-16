import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import {
  Switch,
  Typography,
  Radio,
  RadioChangeEvent,
  Select,
  Transfer,
} from "antd";

import {
  onChange as onSettingChanged,
  settingInfo,
  ISettingItem,
} from "../../../reducers/setting";
import { useAppSelector } from "../../../hooks";
import store from "../../../store";
import { ISetting } from "./default";
import { TransferDirection } from "antd/lib/transfer";

const { Text } = Typography;

interface IWidgetSettingItem {
  data?: ISetting;
  autoSave?: boolean;
  bordered?: boolean;
  onChange?: Function;
}
const SettingItemWidget = ({
  data,
  bordered = true,
  onChange,
  autoSave = true,
}: IWidgetSettingItem) => {
  const intl = useIntl();
  const settings: ISettingItem[] | undefined = useAppSelector(settingInfo);
  const [value, setValue] = useState(data?.defaultValue);

  const [targetKeys, setTargetKeys] = useState<string[]>([]);

  useEffect(() => {
    if (typeof data?.defaultValue === "object") {
      setTargetKeys(data.defaultValue);
    }
  }, [data?.defaultValue]);

  useEffect(() => {
    setValue(data?.defaultValue);
  }, [data?.defaultValue]);

  useEffect(() => {
    const currSetting = settings?.find((element) => element.key === data?.key);
    if (typeof currSetting !== "undefined") {
      setValue(currSetting.value);
    }
  }, [data?.key, settings]);
  let content: JSX.Element = <></>;

  if (typeof data === "undefined") {
    return content;
  } else {
    const description: string | undefined = data.description
      ? intl.formatMessage({ id: data.description })
      : undefined;
    switch (typeof data.defaultValue) {
      case "number":
        break;
      case "object":
        switch (data.widget) {
          case "transfer":
            if (typeof data.options !== "undefined") {
              content = (
                <Transfer
                  dataSource={data.options.map((item) => {
                    return {
                      key: item.value,
                      title: intl.formatMessage({ id: item.label }),
                    };
                  })}
                  titles={[
                    "备选",
                    intl.formatMessage({ id: "labels.selected" }),
                  ]}
                  targetKeys={targetKeys}
                  onChange={(
                    newTargetKeys: string[],
                    direction: TransferDirection,
                    moveKeys: string[]
                  ) => {
                    setTargetKeys(newTargetKeys);
                    store.dispatch(
                      onSettingChanged({
                        key: data.key,
                        value: newTargetKeys,
                      })
                    );
                    if (typeof onChange !== "undefined") {
                      onChange(data.key, newTargetKeys);
                    }
                  }}
                  render={(item) => item.title}
                  oneWay
                />
              );
            }
            break;
        }
        break;
      case "string":
        switch (data.widget) {
          case "radio-button":
            if (typeof data.options !== "undefined") {
              content = (
                <>
                  <Radio.Group
                    value={value}
                    buttonStyle="solid"
                    onChange={(e: RadioChangeEvent) => {
                      setValue(e.target.value);
                      if (autoSave) {
                        store.dispatch(
                          onSettingChanged({
                            key: data.key,
                            value: e.target.value,
                          })
                        );
                      }
                      if (typeof onChange !== "undefined") {
                        onChange(data.key, e.target.value);
                      }
                    }}
                  >
                    {data.options.map((item, id) => {
                      return (
                        <Radio.Button key={id} value={item.value}>
                          {intl.formatMessage({ id: item.label })}
                        </Radio.Button>
                      );
                    })}
                  </Radio.Group>
                </>
              );
            }
            break;

          default:
            if (typeof data.options !== "undefined") {
              content = (
                <div>
                  <Select
                    defaultValue={data.defaultValue}
                    style={{ width: 120 }}
                    bordered={bordered}
                    onChange={(value: string) => {
                      console.log(`selected ${value}`);
                      if (autoSave) {
                        store.dispatch(
                          onSettingChanged({
                            key: data.key,
                            value: value,
                          })
                        );
                      }
                      if (typeof onChange !== "undefined") {
                        onChange(data.key, value);
                      }
                    }}
                    options={data.options.map((item) => {
                      return {
                        value: item.value,
                        label: intl.formatMessage({ id: item.label }),
                      };
                    })}
                  />
                </div>
              );
            } else {
            }
            break;
        }
        break;
      case "boolean":
        content = (
          <div>
            <Switch
              defaultChecked={value as boolean}
              onChange={(checked) => {
                console.log("setting changed", data.key, checked);
                if (autoSave) {
                  store.dispatch(
                    onSettingChanged({ key: data.key, value: checked })
                  );
                }
                if (typeof onChange !== "undefined") {
                  onChange(data.key, checked);
                }
              }}
            />
          </div>
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
  }
};

export default SettingItemWidget;
