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

const WbwParent = ({ data, answer, display, onChange }: IWidget) => {
  const intl = useIntl();
  const inlineDict = useAppSelector(_inlineDict);

  const items = useMemo<MenuProps["items"]>(() => {
    const word = data.real.value;
    if (!word || !inlineDict.wordIndex.includes(word)) {
      return defaultMenu;
    }

    const result = inlineDict.wordList.filter((w) => w.word === word);
    const uniqueParents = [
      ...new Map(
        result
          .filter(
            (w): w is typeof w & { parent: string } =>
              typeof w.parent === "string"
          )
          .map((w) => [w.parent, 1] as const)
      ).keys(),
    ];

    return [...uniqueParents, word].map((item) => ({
      key: item,
      label: item,
    })) satisfies MenuProps["items"];
  }, [data.real.value, inlineDict]);

  const onClick: MenuProps["onClick"] = (e) => {
    console.log("click ", e);
    onChange?.(e.key);
  };

  const word = data.real?.value;
  if (typeof word !== "string" || word.trim().length === 0) {
    return <></>;
  }

  let parent = <></>;
  if (display === "block") {
    if (
      typeof data.parent?.value === "string" &&
      data.parent.value.trim().length > 0
    ) {
      const maxLen = word.length + 6 + Math.floor(word.length / 3);
      const shortString = data.parent.value.slice(0, maxLen);
      parent =
        shortString === data.parent.value ? (
          <span>{shortString}</span>
        ) : (
          <Tooltip title={data.parent.value}>{`${shortString}…`}</Tooltip>
        );
    } else {
      parent = (
        <Text type="secondary">
          {intl.formatMessage({ id: "forms.fields.parent.label" })}
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
          {parent}
        </Dropdown>
      </Text>
    </div>
  );
};

export default WbwParent;
