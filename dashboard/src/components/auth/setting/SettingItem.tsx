import { Switch, Typography, Radio, RadioChangeEvent, Select } from "antd";
import {
  onChange as onSettingChanged,
  settingInfo,
  ISettingItem,
} from "../../../reducers/setting";
import { useAppSelector } from "../../../hooks";
import store from "../../../store";
import { ISetting } from "./default";
import { useEffect, useState } from "react";

const { Title, Text } = Typography;

interface IWidgetSettingItem {
  data?: ISetting;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidgetSettingItem) => {
  const settings: ISettingItem[] | undefined = useAppSelector(settingInfo);
  const [value, setValue] = useState(data?.defaultValue);
  const title = <Title level={5}>{data?.label}</Title>;
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
    switch (typeof data.defaultValue) {
      case "number":
        break;
      case "string":
        switch (data.widget) {
          case "radio-button":
            if (typeof data.options !== "undefined") {
              return (
                <>
                  {title}
                  <div>
                    <Text>{data.description}</Text>
                  </div>
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
                          {item.label}
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
                    options={data.options}
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
            <Text>{data.description}</Text>
          </div>
        );
        break;
      default:
        break;
    }

    return (
      <div>
        <Title level={5}>{data.label}</Title>
        {content}
      </div>
    );
  }
};

export default Widget;
