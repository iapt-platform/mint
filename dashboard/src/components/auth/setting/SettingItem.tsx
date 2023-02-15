import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { Switch, Typography, Radio, RadioChangeEvent, Select } from "antd";

import {
  onChange as onSettingChanged,
  settingInfo,
  ISettingItem,
} from "../../../reducers/setting";
import { useAppSelector } from "../../../hooks";
import store from "../../../store";
import { ISetting } from "./default";

const { Title, Text } = Typography;

interface IWidgetSettingItem {
  data?: ISetting;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidgetSettingItem) => {
  const intl = useIntl();
  const settings: ISettingItem[] | undefined = useAppSelector(settingInfo);
  const [value, setValue] = useState(data?.defaultValue);
  const title = (
    <Title level={5}>
      {data?.label ? intl.formatMessage({ id: data.label }) : ""}
    </Title>
  );
  console.log(data);
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
    const description: string = intl.formatMessage({ id: data.description });
    switch (typeof data.defaultValue) {
      case "number":
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
                      store.dispatch(
                        onSettingChanged({
                          key: data.key,
                          value: e.target.value,
                        })
                      );
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
                    onChange={(value: string) => {
                      console.log(`selected ${value}`);
                      store.dispatch(
                        onSettingChanged({
                          key: data.key,
                          value: value,
                        })
                      );
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
                if (typeof onChange !== "undefined") {
                  onChange(checked);
                }
                console.log("setting changed", data.key, checked);
                store.dispatch(
                  onSettingChanged({ key: data.key, value: checked })
                );
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
        <div style={{ display: "flex", justifyContent: "space-between" }}>
          <Text>{intl.formatMessage({ id: data.label })}</Text>
          {content}
        </div>
        <Text type="secondary">{description}</Text>
      </div>
    );
  }
};

export default Widget;
