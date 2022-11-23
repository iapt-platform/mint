import { useIntl } from "react-intl";

export interface ISettingItemOption {
  label: string;
  key: string;
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
          key: "column",
          label: intl.formatMessage({
            id: "setting.layout.direction.col.label",
          }),
        },
        {
          key: "row",
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
          key: "sentence",
          label: intl.formatMessage({
            id: "setting.layout.paragraph.sentence.label",
          }),
        },
        {
          key: "paragraph",
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
      defaultValue: "rome",
      options: [
        {
          key: "rome",
          label: intl.formatMessage({
            id: "setting.pali.script.rome.label",
          }),
        },
        {
          key: "my",
          label: intl.formatMessage({
            id: "setting.pali.script.my.label",
          }),
        },
        {
          key: "si",
          label: intl.formatMessage({
            id: "setting.pali.script.si.label",
          }),
        },
      ],
    },
    {
      /**
       * 第一巴利脚本
       */
      key: "setting.pali.script2",
      label: intl.formatMessage({ id: "setting.pali.script2.label" }),
      description: intl.formatMessage({
        id: "setting.pali.script2.description",
      }),
      defaultValue: "none",
      options: [
        {
          key: "none",
          label: intl.formatMessage({
            id: "setting.pali.script.none.label",
          }),
        },
        {
          key: "rome",
          label: intl.formatMessage({
            id: "setting.pali.script.rome.label",
          }),
        },
        {
          key: "my",
          label: intl.formatMessage({
            id: "setting.pali.script.my.label",
          }),
        },
        {
          key: "si",
          label: intl.formatMessage({
            id: "setting.pali.script.si.label",
          }),
        },
      ],
    },
  ];

  return defaultSetting;
};
