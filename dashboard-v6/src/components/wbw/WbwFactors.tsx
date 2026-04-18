import { useMemo } from "react";
import { useIntl } from "react-intl";
import { type MenuProps, Tooltip } from "antd";
import { Dropdown, Space, Typography } from "antd";
import { LoadingOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { inlineDict as _inlineDict } from "../../reducers/inline-dict";
import { errorClass } from "./utils";
import type { IWbw, TWbwDisplayMode } from "../../types/wbw";

const { Text } = Typography;

interface IWidget {
  data: IWbw;
  answer?: IWbw;
  display?: TWbwDisplayMode;
  onChange?: (key: string) => void;
}

const defaultMenu: MenuProps["items"] = [
  {
    key: "loading",
    label: (
      <Space>
        <LoadingOutlined />
        {"Loading"}
      </Space>
    ),
  },
];

const WbwFactorsWidget = ({ data, answer, display, onChange }: IWidget) => {
  const intl = useIntl();
  const inlineDict = useAppSelector(_inlineDict);

  const items = useMemo<MenuProps["items"]>(() => {
    const realValue = data.real.value;
    if (!realValue || !inlineDict.wordIndex.includes(realValue)) {
      return defaultMenu;
    }

    const result = inlineDict.wordList.filter(
      (word) => word.word === realValue
    );

    const uniqueFactors = [
      ...new Map(
        result
          .filter(
            (item): item is typeof item & { factors: string } =>
              typeof item.factors === "string"
          )
          .map((item) => [item.factors, 1] as const)
      ).keys(),
    ];

    return [...uniqueFactors, realValue].map((item) => ({
      key: item,
      label: item,
    }));
  }, [data.real.value, inlineDict]);

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    onChange?.(e.key);
  };

  const realValue = data.real?.value;
  if (typeof realValue !== "string" || realValue.trim().length === 0) {
    return <></>;
  }

  let factors = <></>;
  if (display === "block") {
    if (
      typeof data.factors?.value === "string" &&
      data.factors.value.trim().length > 0
    ) {
      const maxLen = realValue.length + 6 + Math.floor(realValue.length / 3);
      const shortString = data.factors.value.slice(0, maxLen);
      factors =
        shortString === data.factors.value ? (
          <span>{shortString}</span>
        ) : (
          <Tooltip title={data.factors.value}>{`${shortString}…`}</Tooltip>
        );
    } else {
      factors = (
        <Text type="secondary">
          {intl.formatMessage({ id: "forms.fields.factors.label" })}
        </Text>
      );
    }
  }

  const checkClass = answer
    ? errorClass("factors", data.factors?.value, answer?.factors?.value)
    : "";

  return (
    <div className={"wbw_word_item" + checkClass}>
      <Text type="secondary">
        <Dropdown menu={{ items, onClick }} placement="bottomLeft">
          {factors}
        </Dropdown>
      </Text>
    </div>
  );
};

export default WbwFactorsWidget;
