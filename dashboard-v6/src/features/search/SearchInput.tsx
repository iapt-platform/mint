import { AutoComplete, Flex, Input, Typography, theme } from "antd";
import { useEffect, useRef, useState } from "react";
import { useIntl } from "react-intl";
import {
  fetchWordSuggestions,
  type WordSuggestion,
} from "../../api/pali-word-form";
import { looksLikePageNumber } from "./state";

const { Text } = Typography;
const PALI_FONT = '"Noto Serif", Georgia, serif';
/** 停止输入多久后再请求补全 */
const DEBOUNCE_MS = 200;

/** 补全的是最后一个词（空格或分号之后），前面的原样保留（同 v4） */
const splitLast = (value: string): [head: string, last: string] => {
  const i = Math.max(value.lastIndexOf(" "), value.lastIndexOf(";"));
  return [value.slice(0, i + 1), value.slice(i + 1)];
};

interface IWidget {
  value: string;
  onChange: (value: string) => void;
  onSearch: (value: string) => void;
  autoFocus?: boolean;
}

/**
 * 搜索框 + 巴利词补全（/v3/pali-word-suggestions）。
 *
 * 候选显示出现次数与黑体次数（黑体出现过的词加粗）。不默认选中第一项：直接回车搜的是输入的原文，
 * 用方向键选中候选再回车（或点击）才替换最后一个词并立即搜索。页码输入不补全。
 */
const SearchInput = ({ value, onChange, onSearch, autoFocus }: IWidget) => {
  const intl = useIntl();
  const { token } = theme.useToken();
  const [options, setOptions] = useState<WordSuggestion[]>([]);
  const [head, last] = splitLast(value);
  const prefix = last.trim();
  const pending = useRef<AbortController | null>(null);
  // 搜索之后收起候选（否则下拉挡住标签页）；再次输入时才展开
  const [open, setOpen] = useState(false);

  useEffect(() => {
    pending.current?.abort();
    if (!prefix || looksLikePageNumber(prefix)) return;
    const controller = new AbortController();
    pending.current = controller;
    const timer = setTimeout(() => {
      fetchWordSuggestions(prefix, controller.signal)
        .then((items) => {
          if (!controller.signal.aborted) setOptions(items);
        })
        .catch(() => {
          // 补全失败不打扰：没有候选就是了
        });
    }, DEBOUNCE_MS);
    return () => {
      clearTimeout(timer);
      controller.abort();
    };
  }, [prefix]);

  const shown = prefix && !looksLikePageNumber(prefix) ? options : [];

  return (
    <AutoComplete
      value={value}
      onChange={(v: string) => {
        onChange(v);
        setOpen(true);
      }}
      open={open && shown.length > 0}
      onOpenChange={(o) => {
        if (!o) setOpen(false);
      }}
      defaultActiveFirstOption={false}
      // 候选由服务端按前缀（含去变音形）给出，不再按输入原文过滤，否则 dhamma 会把 dhammā 筛掉
      filterOption={false}
      popupMatchSelectWidth={360}
      style={{ flex: 1, minWidth: 0 }}
      options={shown.map((w) => ({
        value: head + w.word,
        label: (
          <Flex justify="space-between" align="baseline" gap={12}>
            <span style={{ fontFamily: PALI_FONT }}>
              {w.bold > 0 ? <Text strong>{w.word}</Text> : w.word}
            </span>
            <Text
              type="secondary"
              style={{
                fontSize: token.fontSizeSM,
                fontVariantNumeric: "tabular-nums",
              }}
            >
              {intl.formatMessage(
                { id: "search.suggest.count" },
                {
                  count: w.count.toLocaleString(),
                  bold: w.bold.toLocaleString(),
                },
              )}
            </Text>
          </Flex>
        ),
      }))}
      onSelect={(v: string) => {
        setOpen(false);
        onChange(v);
        onSearch(v);
      }}
    >
      <Input.Search
        size="large"
        allowClear
        autoFocus={autoFocus}
        placeholder={intl.formatMessage({ id: "search.placeholder" })}
        onSearch={(v) => {
          setOpen(false);
          onSearch(v);
        }}
        styles={{ input: { fontFamily: PALI_FONT } }}
      />
    </AutoComplete>
  );
};

export default SearchInput;
