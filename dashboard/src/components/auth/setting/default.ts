import { useIntl } from "react-intl";
import { ISettingItem } from "../../../reducers/setting";

export interface ISettingItemOption {
  label: string;
  value: string;
}
export interface ISetting {
  key: string;
  label: string;
  description: string;
  defaultValue: string | number | boolean;
  value?: string | number | boolean;
  widget?: "input" | "select" | "radio" | "radio-button";
  options?: ISettingItemOption[];
  max?: number;
  min?: number;
}

export const GetUserSetting = (
  key: string,
  curr?: ISettingItem[]
): string | number | boolean | undefined => {
  const currSetting = curr?.find((element) => element.key === key);
  if (typeof currSetting !== "undefined") {
    return currSetting.value;
  } else {
    const defaultSetting = SettingFind(key);
    if (typeof defaultSetting !== "undefined") {
      return defaultSetting.defaultValue;
    } else {
      return undefined;
    }
  }
};

export const SettingFind = (key: string): ISetting | undefined => {
  return Settings().find((element) => element.key === key);
};
export const Settings = (): ISetting[] => {
  const intl = useIntl();

  const defaultSetting: ISetting[] = [
    {
      /**
       * 是否显示巴利原文
       */
      key: "setting.display.original",
      label: intl.formatMessage({ id: "setting.display.original.label" }),
      description: intl.formatMessage({
        id: "setting.display.original.description",
      }),
      defaultValue: true,
    },
    {
      /**
       * 排版方向
       */
      key: "setting.layout.direction",
      label: intl.formatMessage({ id: "setting.layout.direction.label" }),
      description: intl.formatMessage({
        id: "setting.layout.direction.description",
      }),
      defaultValue: "column",
      options: [
        {
          value: "column",
          label: intl.formatMessage({
            id: "setting.layout.direction.col.label",
          }),
        },
        {
          value: "row",
          label: intl.formatMessage({
            id: "setting.layout.direction.row.label",
          }),
        },
      ],
      widget: "radio-button",
    },
    {
      /**
       * 段落或者逐句对读
       */
      key: "setting.layout.paragraph",
      label: intl.formatMessage({ id: "setting.layout.paragraph.label" }),
      description: intl.formatMessage({
        id: "setting.layout.paragraph.description",
      }),
      defaultValue: "sentence",
      options: [
        {
          value: "sentence",
          label: intl.formatMessage({
            id: "setting.layout.paragraph.sentence.label",
          }),
        },
        {
          value: "paragraph",
          label: intl.formatMessage({
            id: "setting.layout.paragraph.paragraph.label",
          }),
        },
      ],
      widget: "radio-button",
    },
    {
      /**
       * 第一巴利脚本
       */
      key: "setting.pali.script1",
      label: intl.formatMessage({ id: "setting.pali.script1.label" }),
      description: intl.formatMessage({
        id: "setting.pali.script1.description",
      }),
      defaultValue: "roman",
      options: [
        {
          value: "roman",
          label: intl.formatMessage({
            id: "setting.pali.script.rome.label",
          }),
        },
        {
          value: "roman_to_my",
          label: intl.formatMessage({
            id: "setting.pali.script.my.label",
          }),
        },
        {
          value: "roman_to_si",
          label: intl.formatMessage({
            id: "setting.pali.script.si.label",
          }),
        },
        {
          value: "roman_to_thai",
          label: intl.formatMessage({
            id: "setting.pali.script.thai.label",
          }),
        },
        {
          value: "roman_to_taitham",
          label: intl.formatMessage({
            id: "setting.pali.script.tai.label",
          }),
        },
      ],
    },
    {
      /**
       * 第二巴利脚本
       */
      key: "setting.pali.script2",
      label: intl.formatMessage({ id: "setting.pali.script2.label" }),
      description: intl.formatMessage({
        id: "setting.pali.script2.description",
      }),
      defaultValue: "none",
      options: [
        {
          value: "none",
          label: intl.formatMessage({
            id: "setting.pali.script.none.label",
          }),
        },
        {
          value: "roman",
          label: intl.formatMessage({
            id: "setting.pali.script.rome.label",
          }),
        },
        {
          value: "roman_to_my",
          label: intl.formatMessage({
            id: "setting.pali.script.my.label",
          }),
        },
        {
          value: "roman_to_si",
          label: intl.formatMessage({
            id: "setting.pali.script.si.label",
          }),
        },
      ],
    },
  ];

  return defaultSetting;
};
